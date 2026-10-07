<?php

declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Seller\PayoutsController;
use App\Http\Controllers\API\v1\Dashboard\Seller\ShopPaymentController;
use App\Http\Controllers\API\v1\Dashboard\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\API\v1\Dashboard\Admin\PaymentPayloadController;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Payment\UpdateRequest as PaymentUpdateRequest;
use App\Http\Requests\PaymentPayload\StoreRequest as PaymentPayloadStoreRequest;
use App\Http\Requests\PaymentPayload\UpdateRequest as PaymentPayloadUpdateRequest;
use App\Http\Requests\Payout\UpdateRequest as PayoutUpdateRequest;
use App\Http\Requests\ShopPayment\StoreRequest as ShopPaymentStoreRequest;
use App\Http\Requests\ShopPayment\UpdateRequest as ShopPaymentUpdateRequest;
use App\Http\Resources\ShopPaymentResource;
use App\Models\Country;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Shop;
use App\Models\ShopPayment;
use App\Models\User;
use App\Policies\CountryPaymentPolicy;
use App\Services\PayoutService\PayoutService;
use App\Services\PaymentPayloadService\PaymentPayloadService;
use App\Services\PaymentService\PaymentService;
use App\Services\ShopServices\ShopPaymentService;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PaymentPhaseZeroContainmentTest extends IsolatedTestCase
{
    public function test_shop_payment_resource_returns_presence_metadata_without_credential_values(): void
    {
        $payment = new ShopPayment();
        $payment->setRawAttributes([
            'id' => 12,
            'shop_id' => 3,
            'payment_id' => 8,
            'status' => 1,
            'client_id' => 'dummy-public-client',
            'secret_id' => 'dummy-secret',
            'merchant_email' => 'dummy@example.invalid',
            'payment_key' => 'dummy-key',
            'merchant_key' => 'opaque-encrypted-dummy',
            'subscription_key' => 'opaque-encrypted-dummy',
            'api_user' => 'opaque-encrypted-dummy',
            'api_key' => 'opaque-encrypted-dummy',
            'private_key' => 'dummy-private-key',
            'access_token' => 'dummy-access-token',
            'created_at' => null,
            'updated_at' => null,
        ], true);

        $serialized = (new ShopPaymentResource($payment))->resolve(Request::create('/api/v1/rest/shop-payments/12'));

        self::assertSame(12, $serialized['id']);
        self::assertSame(8, $serialized['payment_id']);
        self::assertTrue($serialized['configured']);
        self::assertTrue($serialized['merchant_key_configured']);

        foreach ([
            'client_id',
            'secret_id',
            'merchant_email',
            'payment_key',
            'merchant_key',
            'subscription_key',
            'api_user',
            'api_key',
            'private_key',
            'access_token',
        ] as $sensitiveKey) {
            self::assertArrayNotHasKey($sensitiveKey, $serialized);
        }
        $json = json_encode($serialized, JSON_THROW_ON_ERROR);
        foreach ([
            'dummy-public-client',
            'dummy-secret',
            'dummy@example.invalid',
            'dummy-key',
            'opaque-encrypted-dummy',
            'dummy-private-key',
            'dummy-access-token',
        ] as $credentialSentinel) {
            self::assertStringNotContainsString($credentialSentinel, $json);
        }
    }

    public function test_seller_shop_payment_show_and_update_reject_a_foreign_shop_row(): void
    {
        $controller = $this->sellerShopPaymentController(shopId: 41);
        $foreignPayment = new ShopPayment();
        $foreignPayment->setRawAttributes(['id' => 7, 'shop_id' => 42], true);

        try {
            $controller->show($foreignPayment);
            self::fail('A foreign shop payment must not be shown.');
        } catch (NotFoundHttpException) {
            self::assertTrue(true);
        }

        try {
            $controller->update($foreignPayment, new ShopPaymentUpdateRequest());
            self::fail('A foreign shop payment must not be updated.');
        } catch (NotFoundHttpException) {
            self::assertTrue(true);
        }
    }

    public function test_shop_payment_update_request_authorizes_current_shop_and_permissioned_staff_before_rules(): void
    {
        $shop = new Shop();
        $shop->setRawAttributes(['id' => 41], true);

        $owner = new User();
        $owner->setRelation('shop', $shop);
        $sameShopRow = new ShopPayment();
        $sameShopRow->setRawAttributes(['id' => 7, 'shop_id' => 41], true);
        $ownerRequest = $this->makeShopPaymentUpdateRequest(3, $sameShopRow);
        $ownerRequest->setUserResolver(static fn () => $owner);
        self::assertTrue($ownerRequest->authorize());

        $foreignRow = new ShopPayment();
        $foreignRow->setRawAttributes(['id' => 8, 'shop_id' => 42], true);
        $foreignRequest = $this->makeShopPaymentUpdateRequest(3, $foreignRow);
        $foreignRequest->setUserResolver(static fn () => $owner);
        self::assertFalse($foreignRequest->authorize());

        // The owner shortcut is not the only authorization path: accepted
        // shop staff continue to be evaluated through the existing
        // hasShopPermission grant used by the route middleware.
        $staff = Mockery::mock(User::class)->makePartial();
        $staff->setRelation('shop', null);
        $staff->setRelation('moderatorShop', $shop);
        $staff->shouldReceive('hasShopPermission')
            ->once()
            ->with(41, 'payments.gateways.manage')
            ->andReturn(true);
        $staffRequest = $this->makeShopPaymentUpdateRequest(3, $sameShopRow);
        $staffRequest->setUserResolver(static fn () => $staff);
        self::assertTrue($staffRequest->authorize());
    }

    public function test_shop_payment_delete_is_scoped_and_status_uses_explicit_desired_state(): void
    {
        Schema::create('shop_payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('shop_id');
            $table->unsignedInteger('payment_id')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
        $this->database->table('shop_payments')->insert([
            ['id' => 1, 'shop_id' => 41, 'payment_id' => 2, 'status' => 0],
            ['id' => 2, 'shop_id' => 42, 'payment_id' => 3, 'status' => 1],
        ]);
        $service = $this->serviceWithoutConstructor(ShopPaymentService::class);

        $service->delete([1, 2], 41);
        self::assertSame([2], $this->database->table('shop_payments')->pluck('id')->all());

        $explicitDisable = $service->setActive(2, 42, false);
        self::assertTrue($explicitDisable['status']);
        self::assertSame(0, $this->database->table('shop_payments')->where('id', 2)->value('status'));
        $service->setActive(2, 42, false);
        self::assertSame(0, $this->database->table('shop_payments')->where('id', 2)->value('status'));

        $denied = $service->setActive(2, 41, false);
        self::assertFalse($denied['status']);
        self::assertSame(0, $this->database->table('shop_payments')->where('id', 2)->value('status'));
    }

    public function test_shop_payment_requests_accept_location_type_and_update_checks_credential_presence_raw(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag')->nullable();
        });
        $this->database->table('payments')->insert([
            ['id' => 1, 'tag' => Payment::TAG_ORANGE],
            ['id' => 2, 'tag' => Payment::TAG_MTN],
            ['id' => 3, 'tag' => Payment::TAG_CASH],
        ]);

        $existingOrange = new ShopPayment();
        $existingOrange->setRawAttributes([
            'merchant_key' => 'opaque-ciphertext-not-decrypted',
        ], true);
        $orangeRules = $this->makeShopPaymentUpdateRequest(1, $existingOrange)->rules();
        self::assertSame('', (string) $orangeRules['merchant_key'][0]);
        self::assertSame('nullable|integer|in:1,2', $orangeRules['location_type']);

        $missingMtnCredentials = $this->makeShopPaymentUpdateRequest(2, new ShopPayment())->rules();
        foreach (['subscription_key', 'api_user', 'api_key'] as $credential) {
            self::assertSame('required', (string) $missingMtnCredentials[$credential][0]);
        }

        $storeRules = ShopPaymentStoreRequest::create('/', 'POST', ['payment_id' => 3])->rules();
        self::assertSame('nullable|integer|in:1,2', $storeRules['location_type']);
    }

    public function test_status_endpoint_accepts_the_explicit_status_boolean(): void
    {
        $controller = $this->controllerWithoutConstructor(ShopPaymentController::class);
        $shop = new Shop();
        $shop->setRawAttributes(['id' => 41], true);
        $shopProperty = new \ReflectionProperty(ShopPaymentController::class, 'shop');
        $shopProperty->setAccessible(true);
        $shopProperty->setValue($controller, $shop);

        $service = Mockery::mock(ShopPaymentService::class);
        $service->shouldReceive('setActive')
            ->once()
            ->with(6, 41, false)
            ->andThrow(new \RuntimeException('desired status was passed'));
        $this->setControllerProperty($controller, ShopPaymentController::class, 'service', $service);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('exists')->with('status')->once()->andReturn(true);
        $request->shouldReceive('validate')
            ->with(['status' => ['required', 'boolean']])
            ->once()
            ->andReturn(['status' => false]);

        try {
            $controller->setActive(6, $request);
            self::fail('The expected service call should have thrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame('desired status was passed', $exception->getMessage());
        }
    }

    public function test_seller_cannot_update_foreign_payout_and_creator_is_prohibited_input(): void
    {
        $this->setAuthenticatedUserId(101);
        $controller = $this->controllerWithoutConstructor(PayoutsController::class);
        $service = Mockery::mock(PayoutService::class);
        $service->shouldNotReceive('update');
        $this->setControllerProperty($controller, PayoutsController::class, 'service', $service);

        $payout = new Payout();
        $payout->setRawAttributes(['id' => 5, 'created_by' => 202], true);

        try {
            $controller->update($payout, new PayoutUpdateRequest());
            self::fail('A seller must not update another user’s payout.');
        } catch (NotFoundHttpException) {
            self::assertTrue(true);
        }

        self::assertSame('prohibited', (new PayoutUpdateRequest())->rules()['created_by']);
    }

    public function test_country_payment_policy_write_is_limited_to_country_authority_or_global_superadmin(): void
    {
        $country = new Country();
        $country->setRawAttributes(['id' => 77], true);

        $scopedUser = Mockery::mock(User::class);
        $scopedUser->shouldReceive('isSuperAdmin')->once()->andReturn(false);
        $scopedUser->shouldReceive('hasCountryPermission')->with(77, 'transactions.manage')->once()->andReturn(false);
        self::assertFalse((new CountryPaymentPolicy())->allows($scopedUser, $country));

        $countryAuthorizedUser = Mockery::mock(User::class);
        $countryAuthorizedUser->shouldReceive('isSuperAdmin')->once()->andReturn(false);
        $countryAuthorizedUser->shouldReceive('hasCountryPermission')->with(77, 'transactions.manage')->once()->andReturn(true);
        self::assertTrue((new CountryPaymentPolicy())->allows($countryAuthorizedUser, $country));

        $globalUser = Mockery::mock(User::class);
        $globalUser->shouldReceive('isSuperAdmin')->once()->andReturn(true);
        $globalUser->shouldNotReceive('hasCountryPermission');
        self::assertTrue((new CountryPaymentPolicy())->allows($globalUser, $country));
    }

    public function test_country_payment_policy_rejects_country_authority_for_another_country(): void
    {
        $country = new Country();
        $country->setRawAttributes(['id' => 78], true);
        $countryAdmin = Mockery::mock(User::class);
        $countryAdmin->shouldReceive('isSuperAdmin')->once()->andReturn(false);
        $countryAdmin->shouldReceive('hasCountryPermission')->with(78, 'transactions.manage')->once()->andReturn(false);

        self::assertFalse((new CountryPaymentPolicy())->allows($countryAdmin, $country));
    }

    public function test_scoped_admin_cannot_mutate_global_payment_policy(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isSuperAdmin')->times(3)->andReturn(false);
        $this->setAuthenticatedUser($user);

        $controller = $this->controllerWithoutConstructor(AdminPaymentController::class);
        $service = Mockery::mock(PaymentService::class);
        $service->shouldNotReceive('update');
        $service->shouldNotReceive('setActive');
        $service->shouldNotReceive('dropAll');
        $this->setControllerProperty($controller, AdminPaymentController::class, 'service', $service);

        $this->assertForbidden(static fn () => $controller->update(new Payment(), new PaymentUpdateRequest()));
        $this->assertForbidden(static fn () => $controller->setActive(1));
        $this->assertForbidden(static fn () => $controller->dropAll());
    }

    public function test_scoped_admin_cannot_mutate_global_payment_payloads(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isSuperAdmin')->times(4)->andReturn(false);
        $this->setAuthenticatedUser($user);

        $controller = $this->controllerWithoutConstructor(PaymentPayloadController::class);
        $service = Mockery::mock(PaymentPayloadService::class);
        $service->shouldNotReceive('create');
        $service->shouldNotReceive('update');
        $service->shouldNotReceive('delete');
        $service->shouldNotReceive('dropAll');
        $this->setControllerProperty($controller, PaymentPayloadController::class, 'service', $service);

        $this->assertForbidden(static fn () => $controller->store(new PaymentPayloadStoreRequest()));
        $this->assertForbidden(static fn () => $controller->update(1, new PaymentPayloadUpdateRequest()));
        $this->assertForbidden(static fn () => $controller->destroy(new FilterParamsRequest()));
        $this->assertForbidden(static fn () => $controller->dropAll());
    }

    private function sellerShopPaymentController(int $shopId): ShopPaymentController
    {
        $controller = $this->controllerWithoutConstructor(ShopPaymentController::class);
        $shop = new Shop();
        $shop->setRawAttributes(['id' => $shopId], true);
        $property = new \ReflectionProperty(ShopPaymentController::class, 'shop');
        $property->setAccessible(true);
        $property->setValue($controller, $shop);

        $repository = Mockery::mock(\App\Repositories\ShopPaymentRepository\ShopPaymentRepository::class);
        $repository->shouldNotReceive('show');
        $service = Mockery::mock(ShopPaymentService::class);
        $service->shouldNotReceive('update');
        $this->setControllerProperty($controller, ShopPaymentController::class, 'repository', $repository);
        $this->setControllerProperty($controller, ShopPaymentController::class, 'service', $service);

        return $controller;
    }

    private function makeShopPaymentUpdateRequest(int $paymentId, ?ShopPayment $shopPayment): ShopPaymentUpdateRequest
    {
        $request = ShopPaymentUpdateRequest::create('/', 'PUT', ['payment_id' => $paymentId]);
        $request->setRouteResolver(static fn () => new class($shopPayment) {
            public function __construct(private ?ShopPayment $shopPayment)
            {
            }

            public function parameter(string $key, mixed $default = null): mixed
            {
                return $key === 'shopPayment' ? $this->shopPayment : $default;
            }
        });

        return $request;
    }

    private function controllerWithoutConstructor(string $class): object
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    private function serviceWithoutConstructor(string $class): object
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    private function setControllerProperty(object $controller, string $class, string $propertyName, object $value): void
    {
        $property = new \ReflectionProperty($class, $propertyName);
        $property->setAccessible(true);
        $property->setValue($controller, $value);
    }

    private function setAuthenticatedUserId(int $id): void
    {
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
    }

    private function setAuthenticatedUser(User $user): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        $translator = Mockery::mock(\Illuminate\Contracts\Translation\Translator::class);
        $translator->shouldReceive('get')->andReturn('Forbidden');
        $this->app->instance('translator', $translator);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('auth');
    }

    private function assertForbidden(callable $operation): void
    {
        try {
            $operation();
            self::fail('A scoped administrator must not mutate global payment configuration.');
        } catch (HttpException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
    }
}