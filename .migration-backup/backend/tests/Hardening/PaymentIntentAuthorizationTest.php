<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\MemberShip;
use App\Models\ParcelOrder;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Shop;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use App\Services\PaymentService\BaseService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;

final class PaymentIntentAuthorizationTest extends IsolatedTestCase
{
    private function service(): BaseService
    {
        return new class extends BaseService {
            public function __construct()
            {
                $this->language = 'en';
                $this->currency = 1;
            }

            protected function getModelClass(): string
            {
                return \App\Models\Payout::class;
            }
        };
    }

    private function authenticate(int $id, array $attributes = []): void
    {
        $user = new User();
        $user->forceFill(array_merge(['id' => $id], array_diff_key($attributes, ['shop' => true])));
        $user->setRelation('countryAdmin', (object) ['country_id' => null]);
        $shop = $attributes['shop'] ?? null;
        if (is_object($shop) && !$shop instanceof Shop) {
            $shop = (new Shop())->forceFill((array) $shop);
        }
        $user->setRelation('shop', $shop);
        $user->setRelation('moderatorShop', null);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
    }

    public function test_a_customer_cannot_initiate_checkout_for_another_users_booking_or_cart(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });
        Schema::create('carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
        });
        $this->database->table('bookings')->insert(['id' => 8, 'user_id' => 901]);
        $this->database->table('carts')->insert(['id' => 9, 'owner_id' => 901]);
        $this->authenticate(44);

        try {
            $this->service()->getPayload(['booking_id' => 8], []);
            self::fail('Cross-customer booking payment intent was accepted');
        } catch (\Exception $exception) {
            self::assertSame('Payment target is unavailable to this account', $exception->getMessage());
        }

        try {
            $this->service()->getPayload(['cart_id' => 9], []);
            self::fail('Cross-customer cart payment intent was accepted');
        } catch (\Exception $exception) {
            self::assertSame('Payment target is unavailable to this account', $exception->getMessage());
        }
    }

    public function test_ambiguous_targets_and_foreign_tenant_plan_purchases_fail_closed(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('active');
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
        });
        $this->database->table('subscriptions')->insert(['id' => 2, 'active' => true]);
        $this->database->table('shops')->insert(['id' => 7, 'user_id' => 99]);
        $this->authenticate(12, ['shop' => (object) ['id' => 7]]);

        try {
            $this->service()->getPayload(['booking_id' => 1, 'cart_id' => 1], []);
            self::fail('Ambiguous payable identifiers were accepted');
        } catch (\Exception $exception) {
            self::assertSame('Exactly one payment target is required', $exception->getMessage());
        }

        try {
            $this->service()->getPayload(['subscription_id' => 2], []);
            self::fail('Plan payment for a shop owned by another tenant was accepted');
        } catch (\Exception $exception) {
            self::assertSame('Payment target is unavailable to this account', $exception->getMessage());
        }
    }

    public function test_country_disabled_gateway_is_rejected_before_intent_creation(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });
        Schema::create('countries', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('currency_id');
            $table->boolean('active')->default(true);
        });
        Schema::create('country_payments', function (Blueprint $table): void {
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('payment_id');
            $table->boolean('active')->default(false);
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
            $table->boolean('active')->default(true);
        });
        Schema::create('payment_payloads', function (Blueprint $table): void {
            $table->increments('id'); $table->integer('payment_id'); $table->text('payload')->nullable();
        });
        Schema::create('shop_locations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('type');
        });
        Schema::create('shops', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->boolean('collect_via_platform')->default(false);
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('shop_id');
        });
        $this->database->table('currencies')->insert(['id' => 3, 'title' => 'GBP']);
        $this->database->table('countries')->insert(['id' => 4, 'currency_id' => 3, 'active' => true]);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'stripe', 'active' => true]);
        $this->database->table('shops')->insert(['id' => 6, 'user_id' => 44, 'collect_via_platform' => false]);
        $this->database->table('shop_locations')->insert([
            'shop_id' => 6,
            'country_id' => 4,
            'type' => \App\Models\ShopLocation::SERVICE,
        ]);
        $this->database->table('bookings')->insert(['id' => 8, 'user_id' => 44, 'shop_id' => 6]);
        $this->authenticate(44);

        try {
            $this->service()->getPayload(['booking_id' => 8], [], 5);
            self::fail('Gateway disabled for the payable country was accepted');
        } catch (\Exception $exception) {
            self::assertSame('Payment method is unavailable in this checkout country', $exception->getMessage());
        }
    }

    public function test_booking_membership_and_parcel_minor_amounts_do_not_ceil_or_round_major_prices(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
        });
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('rate', 10, 4)->default(1);
            $table->string('status')->nullable();
        });
        Schema::create('member_ships', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('price', 10, 2);
            $table->boolean('active')->default(true);
        });
        Schema::create('parcel_orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('currency_id');
            $table->decimal('total_price', 10, 2);
            $table->decimal('rate', 10, 4)->default(1);
            $table->json('address_to')->nullable();
            $table->json('address_from')->nullable();
        });
        $this->database->table('currencies')->insert(['id' => 3, 'title' => 'GBP']);
        $this->database->table('bookings')->insert([
            'id' => 1, 'user_id' => 4, 'currency_id' => 3, 'total_price' => 10.25, 'rate' => 1,
        ]);
        $this->database->table('member_ships')->insert(['id' => 2, 'price' => 10.25, 'active' => true]);
        $this->database->table('parcel_orders')->insert(['id' => 3, 'currency_id' => 3, 'total_price' => 10.25, 'rate' => 1]);
        $this->authenticate(4);
        request()->server->set('REQUEST_URI', '/api/v1/dashboard/user/payment');

        self::assertSame(1025, $this->service()->beforeBooking(['booking_id' => 1], [])['total_price']);
        self::assertSame(1025, $this->service()->beforeMemberShip(['member_ship_id' => 2], ['currency' => 'GBP'])['total_price']);
        self::assertSame(1025, $this->service()->beforeParcel(['parcel_id' => 3], [])['total_price']);
    }

    public function test_paid_settlement_proof_must_match_amount_currency_merchant_and_payable_identity(): void
    {
        $process = new PaymentProcess();
        $process->id = 'provider-reference';
        $process->data = [
            'total_price' => 1025,
            'currency' => 'GBP',
            'payment_id' => 5,
            'model_type' => Booking::class,
            'model_id' => 8,
        ];
        $proof = [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => 'provider-reference',
            'amount_minor' => 1025,
            'currency' => 'GBP',
            'payment_id' => 5,
            'model_type' => Booking::class,
            'model_id' => 8,
        ];

        $method = new \ReflectionMethod(BaseService::class, 'matchesVerifiedIntent');
        $method->setAccessible(true);
        $service = $this->service();

        self::assertTrue($method->invoke($service, $process, Transaction::STATUS_PAID, $proof));
        self::assertTrue($method->invoke($service, $process, Transaction::STATUS_CANCELED, $proof));
        self::assertFalse($method->invoke($service, $process, 'unknown-status', $proof));
        foreach ([
            ['amount_minor' => 1024],
            ['amount_minor' => 1025.5],
            ['currency' => 'USD'],
            ['authenticated' => 1],
            ['merchant_verified' => false],
            ['reference' => 'other-reference'],
            ['payment_id' => 6],
            ['model_type' => Cart::class],
            ['model_id' => 9],
        ] as $mismatch) {
            self::assertFalse(
                $method->invoke($service, $process, Transaction::STATUS_PAID, array_merge($proof, $mismatch))
            );
        }
    }

    public function test_frozen_collection_mode_separates_global_paypal_and_shop_credential_gateways(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->boolean('collect_via_platform')->default(false);
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
            $table->boolean('active')->default(true);
        });
        $this->database->table('bookings')->insert([
            ['id' => 1, 'collect_via_platform' => false],
            ['id' => 2, 'collect_via_platform' => true],
        ]);
        $this->database->table('payments')->insert([
            ['id' => 5, 'tag' => Payment::TAG_STRIPE, 'active' => true],
            ['id' => 6, 'tag' => Payment::TAG_PAY_PAL, 'active' => true],
            ['id' => 7, 'tag' => Payment::TAG_MTN, 'active' => true],
        ]);
        $this->authenticate(9);
        $method = new \ReflectionMethod(BaseService::class, 'freezeGatewayRouting');
        $method->setAccessible(true);
        $service = $this->service();

        try {
            $method->invoke($service, ['model_type' => Booking::class, 'model_id' => 1], 'booking_id', 1, 5);
            self::fail('A global Stripe account must not collect as a shop-direct gateway');
        } catch (\ReflectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            self::assertSame('This payment method cannot collect directly for this shop', $exception->getMessage());
        }

        try {
            $method->invoke($service, ['model_type' => Booking::class, 'model_id' => 1], 'booking_id', 1, 6);
            self::fail('A global PayPal account must not collect as a shop-direct gateway');
        } catch (\ReflectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            self::assertSame('This payment method cannot collect directly for this shop', $exception->getMessage());
        }

        $payPalPlatform = $method->invoke($service, [], 'booking_id', 2, 6);
        self::assertTrue($payPalPlatform['collect_via_platform']);
        self::assertSame('platform', $payPalPlatform['checkout_collection_mode']);

        $platform = $method->invoke($service, [], 'booking_id', 2, 5);
        self::assertTrue($platform['collect_via_platform']);
        self::assertSame('platform', $platform['checkout_collection_mode']);

        self::assertFalse($method->invoke($service, [], 'booking_id', 1, 7)['collect_via_platform']);
        self::assertTrue($method->invoke($service, [], 'booking_id', 2, 7)['collect_via_platform']);
    }
}