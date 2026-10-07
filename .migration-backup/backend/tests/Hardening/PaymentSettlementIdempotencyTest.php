<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Order;
use App\Models\GiftCart;
use App\Models\MemberShip;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserGiftCart;
use App\Models\UserMemberShip;
use App\Models\Wallet;
use App\Services\PaymentService\BaseService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;

final class PaymentSettlementIdempotencyTest extends IsolatedTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        Schema::create('orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->unsignedInteger('cart_id')->nullable();
        });
        Schema::create('transactions', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('payable_type');
            $table->unsignedInteger('payable_id');
            $table->unsignedInteger('payment_sys_id')->nullable();
            $table->string('payment_trx_id')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('perform_time')->nullable();
            $table->text('status_description')->nullable();
            $table->dateTime('refund_time')->nullable();
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('payment_process', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->text('data');
        });
        $this->database->table('orders')->insert(['id' => 8, 'shop_id' => 3, 'currency_id' => 2]);
        $user = (new User())->forceFill(['id' => 9]);
        $user->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(9);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
    }

    private function intent(string $id, string $type = Order::class, int $modelId = 8, array $extras = []): PaymentProcess
    {
        $data = array_merge([
            'payment_id' => 5,
            'total_price' => 1000,
            'currency' => 'GBP',
            'model_type' => $type,
            'model_id' => $modelId,
            'status' => Transaction::STATUS_PROGRESS,
        ], $extras);
        $process = new PaymentProcess();
        $process->id = $id;
        $process->user_id = null;
        $process->model_type = $type;
        $process->model_id = $modelId;
        $process->data = $data;
        $process->save();

        return $process;
    }

    private function proof(string $reference, string $type = Order::class, int $modelId = 8): array
    {
        return [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => $reference,
            'amount_minor' => 1000,
            'currency' => 'GBP',
            'payment_id' => 5,
            'model_type' => $type,
            'model_id' => $modelId,
        ];
    }

    public function test_duplicate_and_out_of_order_callbacks_are_idempotent_and_a_second_paid_order_intent_is_rejected(): void
    {
        $this->intent('first-attempt');
        $this->intent('second-attempt');
        $service = $this->service();

        $first = $service->afterHook('first-attempt', Transaction::STATUS_PAID, null, $this->proof('first-attempt'));
        self::assertTrue($first['status']);
        self::assertTrue($service->afterHook('first-attempt', Transaction::STATUS_PAID, null, $this->proof('first-attempt'))['status']);
        self::assertTrue($service->afterHook('first-attempt', Transaction::STATUS_CANCELED, null, $this->proof('first-attempt'))['status']);

        $second = $service->afterHook('second-attempt', Transaction::STATUS_PAID, null, $this->proof('second-attempt'));
        self::assertFalse($second['status']);
        self::assertSame(Transaction::STATUS_PROGRESS, data_get(PaymentProcess::find('second-attempt')->data, 'status'));
        self::assertSame(Transaction::STATUS_PAID, data_get(PaymentProcess::find('first-attempt')->data, 'status'));
    }

    public function test_wallet_topups_remain_independent_and_booking_tips_are_distinct_settlements(): void
    {
        $wallet = $this->intent('wallet-intent-a', Wallet::class, 90);
        $this->intent('wallet-intent-b', Wallet::class, 90);
        $service = $this->service();
        $method = new \ReflectionMethod(BaseService::class, 'hasPaidAnotherIntentForPayable');
        $method->setAccessible(true);

        $wallet->data = array_merge($wallet->data, ['status' => Transaction::STATUS_PAID]);
        $wallet->save();
        $walletPending = PaymentProcess::find('wallet-intent-b');
        self::assertFalse($method->invoke($service, $walletPending));

        $tipsA = $this->intent('booking-tip-a', \App\Models\Booking::class, 91, ['tips' => 500]);
        $this->intent('booking-tip-b', \App\Models\Booking::class, 91, ['tips' => 500]);
        $tipsA->data = array_merge($tipsA->data, ['status' => Transaction::STATUS_PAID]);
        $tipsA->save();
        self::assertFalse($method->invoke($service, PaymentProcess::find('booking-tip-b')));
    }

    public function test_same_user_can_buy_catalog_products_again_and_reference_replays_have_no_duplicate_effects(): void
    {
        Schema::create('gift_carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('price', 12, 2);
            $table->string('time');
            $table->boolean('active')->default(true);
        });
        Schema::create('member_ships', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('price', 12, 2);
            $table->string('time');
            $table->string('color')->nullable();
            $table->unsignedInteger('sessions')->nullable();
            $table->unsignedInteger('sessions_count')->nullable();
            $table->boolean('active')->default(true);
        });
        Schema::create('user_gift_carts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('gift_cart_id');
            $table->unsignedInteger('user_id');
            $table->decimal('price', 12, 2);
            $table->dateTime('expired_at');
            $table->timestamps();
        });
        Schema::create('user_member_ships', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('member_ship_id');
            $table->unsignedInteger('user_id');
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2);
            $table->dateTime('expired_at');
            $table->unsignedInteger('sessions')->nullable();
            $table->unsignedInteger('sessions_count')->nullable();
            $table->unsignedInteger('remainder')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('lang')->nullable();
        });
        $this->database->table('users')->insert(['id' => 9, 'lang' => 'en']);
        $this->database->table('gift_carts')->insert([
            'id' => 30, 'price' => 10, 'time' => '1 day', 'active' => true,
        ]);
        $this->database->table('member_ships')->insert([
            'id' => 31, 'price' => 10, 'time' => '1 day', 'color' => '#123456',
            'sessions' => 4, 'sessions_count' => 4, 'active' => true,
        ]);

        $createCatalogIntent = function (string $id, string $type, int $modelId): string {
            $this->database->table('payment_process')->insert([
                'id' => $id,
                'user_id' => 9,
                'model_type' => $type,
                'model_id' => $modelId,
                'data' => json_encode([
                    'payment_id' => 5,
                    'total_price' => 1000,
                    'currency' => 'GBP',
                    'model_type' => $type,
                    'model_id' => $modelId,
                    'status' => Transaction::STATUS_PROGRESS,
                ], JSON_THROW_ON_ERROR),
            ]);
            return $id;
        };
        $service = $this->service();
        $giftFirst = $createCatalogIntent('gift-purchase-first', GiftCart::class, 30);
        $giftProof = $this->proof($giftFirst, GiftCart::class, 30);
        $firstGiftSettlement = $service->afterHook($giftFirst, Transaction::STATUS_PAID, null, $giftProof);
        self::assertTrue($firstGiftSettlement['status'], json_encode($firstGiftSettlement));
        self::assertSame(1, UserGiftCart::query()->where('user_id', 9)->count());

        self::assertTrue($service->afterHook($giftFirst, Transaction::STATUS_PAID, null, $giftProof)['status']);
        self::assertSame(1, UserGiftCart::query()->where('user_id', 9)->count());
        self::assertSame(1, Transaction::query()->where('payable_type', UserGiftCart::class)->count());

        $giftRenewal = $createCatalogIntent('gift-purchase-renewal', GiftCart::class, 30);
        self::assertTrue($service->afterHook(
            $giftRenewal,
            Transaction::STATUS_PAID,
            null,
            $this->proof($giftRenewal, GiftCart::class, 30),
        )['status']);
        self::assertSame(2, UserGiftCart::query()->where('user_id', 9)->count());
        self::assertSame(2, Transaction::query()->where('payable_type', UserGiftCart::class)->count());

        $membershipFirst = $createCatalogIntent('membership-purchase-first', MemberShip::class, 31);
        $membershipProof = $this->proof($membershipFirst, MemberShip::class, 31);
        self::assertTrue($service->afterHook(
            $membershipFirst,
            Transaction::STATUS_PAID,
            null,
            $membershipProof,
        )['status']);
        self::assertSame(1, UserMemberShip::query()->where('user_id', 9)->count());

        self::assertTrue($service->afterHook(
            $membershipFirst,
            Transaction::STATUS_PAID,
            null,
            $membershipProof,
        )['status']);
        self::assertSame(1, UserMemberShip::query()->where('user_id', 9)->count());
        self::assertSame(1, Transaction::query()->where('payable_type', UserMemberShip::class)->count());

        $membershipRenewal = $createCatalogIntent('membership-purchase-renewal', MemberShip::class, 31);
        self::assertTrue($service->afterHook(
            $membershipRenewal,
            Transaction::STATUS_PAID,
            null,
            $this->proof($membershipRenewal, MemberShip::class, 31),
        )['status']);
        self::assertSame(2, UserMemberShip::query()->where('user_id', 9)->count());
        self::assertSame(2, Transaction::query()->where('payable_type', UserMemberShip::class)->count());
    }

    public function test_paid_partial_wallet_contributions_do_not_block_provider_remainder_but_full_wallet_settlement_does(): void
    {
        $this->database->table('orders')->insert(['id' => 9, 'shop_id' => 3, 'currency_id' => 2]);
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
        });
        $this->database->table('payments')->insert([
            ['id' => 5, 'tag' => 'stripe'],
            ['id' => 7, 'tag' => 'wallet'],
        ]);
        $this->intent('split-checkout', Order::class, 8, [
            'payment_id' => 5,
            'total_price' => 1499,
            'from_wallet_price' => 5.00,
        ]);
        $this->database->table('transactions')->insert([
            'payable_type' => Order::class,
            'payable_id' => 8,
            'payment_sys_id' => 7,
            'price' => 5.00,
            'status' => Transaction::STATUS_PAID,
        ]);

        $method = new \ReflectionMethod(BaseService::class, 'hasPaidAnotherIntentForPayable');
        $method->setAccessible(true);
        $service = $this->service();

        self::assertFalse($method->invoke($service, PaymentProcess::find('split-checkout')));

        $this->database->table('transactions')->insert([
            'payable_type' => Order::class,
            'payable_id' => 8,
            'payment_sys_id' => 5,
            'price' => 14.99,
            'status' => Transaction::STATUS_PAID,
        ]);
        self::assertTrue($method->invoke($service, PaymentProcess::find('split-checkout')));

        $this->intent('full-wallet-checkout', Order::class, 9, [
            'payment_id' => 7,
            'total_price' => 0,
            'from_wallet_price' => 24.99,
        ]);
        $this->database->table('transactions')->insert([
            'payable_type' => Order::class,
            'payable_id' => 9,
            'payment_sys_id' => 7,
            'price' => 24.99,
            'status' => Transaction::STATUS_PAID,
        ]);
        self::assertTrue($method->invoke($service, PaymentProcess::find('full-wallet-checkout')));
    }

    public function test_each_distinct_verified_wallet_reference_credits_once_but_replays_do_not_credit_again(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('lang')->nullable();
        });
        Schema::create('wallets', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('currency_id');
            $table->decimal('price', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('wallet_histories', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('uuid');
            $table->string('wallet_uuid');
            $table->string('type');
            $table->decimal('price', 12, 2);
            $table->text('note')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->string('status');
            $table->unsignedInteger('transaction_id')->nullable();
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('title');
            $table->string('symbol')->nullable();
            $table->decimal('rate', 12, 4)->default(1);
            $table->boolean('active')->default(true);
            $table->boolean('default')->default(true);
        });
        Schema::create('languages', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->boolean('default')->default(true);
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
        });
        $this->database->table('users')->insert(['id' => 9, 'lang' => 'en']);
        $this->database->table('wallets')->insert([
            'id' => 90, 'uuid' => 'wallet-uuid-90', 'user_id' => 9, 'currency_id' => 1, 'price' => 0,
        ]);
        $this->database->table('currencies')->insert([
            'id' => 1, 'title' => 'GBP', 'symbol' => '£', 'rate' => 1, 'active' => true, 'default' => true,
        ]);
        $this->database->table('languages')->insert(['id' => 1, 'locale' => 'en', 'default' => true]);
        $this->database->table('payments')->insert(['id' => 5, 'tag' => 'wallet']);
        $this->intent('wallet-credit-a', Wallet::class, 90);
        $this->intent('wallet-credit-b', Wallet::class, 90);
        $this->database->table('payment_process')->where('id', 'wallet-credit-a')->update(['user_id' => 9]);
        $this->database->table('payment_process')->where('id', 'wallet-credit-b')->update(['user_id' => 9]);
        self::assertSame(9, (int) PaymentProcess::find('wallet-credit-a')->user_id);
        self::assertNotNull(PaymentProcess::with('user')->find('wallet-credit-a')->user);
        $service = $this->service();

        $firstResult = $service->afterHook(
            'wallet-credit-a',
            Transaction::STATUS_PAID,
            null,
            $this->proof('wallet-credit-a', Wallet::class, 90)
        );
        self::assertTrue($firstResult['status']);
        self::assertSame('wallet success', $firstResult['message']);
        self::assertTrue($service->afterHook(
            'wallet-credit-a',
            Transaction::STATUS_PAID,
            null,
            $this->proof('wallet-credit-a', Wallet::class, 90)
        )['status']);
        self::assertTrue($service->afterHook(
            'wallet-credit-b',
            Transaction::STATUS_PAID,
            null,
            $this->proof('wallet-credit-b', Wallet::class, 90)
        )['status']);

        self::assertSame(20.0, (float) $this->database->table('wallets')->where('id', 90)->value('price'), json_encode([
            'histories' => $this->database->table('wallet_histories')->get()->toArray(),
            'transactions' => $this->database->table('transactions')->get()->toArray(),
        ]));
        self::assertSame(2, $this->database->table('wallet_histories')->count());
        self::assertSame(2, $this->database->table('transactions')->where('payable_type', \App\Models\WalletHistory::class)->count());
    }
}