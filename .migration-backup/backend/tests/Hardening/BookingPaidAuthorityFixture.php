<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\PaymentProcess;
use App\Models\ShopLocation;
use App\Services\PaymentService\Contracts\GatewayConfig;
use App\Services\PaymentService\MtnService;
use App\Services\TransactionService\TransactionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionProperty;

final class BookingPaidControllerFixture extends \App\Http\Controllers\API\v1\Dashboard\Payment\TransactionController
{
    public function successResponse(string $message = '', $data = null): \Illuminate\Http\JsonResponse
    { return new \Illuminate\Http\JsonResponse(['status' => true]); }
}

final class BookingSyntheticGateway implements GatewayConfig
{
    public function getClientId(): ?string { return 'synthetic-client'; }
    public function getMerchantKey(): ?string { return 'synthetic-merchant'; }
    public function getSubscriptionKey(): ?string { return 'synthetic-subscription'; }
    public function getApiUser(): ?string { return 'synthetic-user'; }
    public function getApiKey(): ?string { return 'synthetic-key'; }
    public function getTargetEnvironment(): ?string { return 'sandbox'; }
    public function getCurrency(): ?string { return 'EUR'; }
    public function getBaseUrl(): ?string { return null; }
    public function hasOrangeCredentials(): bool { return false; }
    public function hasMtnCredentials(): bool { return true; }
}

/** Only configuration lookup and network response are synthetic; verifier/settlement are real. */
final class BookingVerifiedMtnFixture extends MtnService
{
    public int $checks = 0;
    public array $result = ['status' => 'SUCCESSFUL', 'externalId' => 'synthetic-booking-reference',
        'amount' => '50.00', 'currency' => 'EUR'];
    public function __construct(private GatewayConfig $gateway)
    { $this->language = 'en'; $this->currency = 1; }
    public function resolveGatewayConfig(array $before, int $paymentId): ?GatewayConfig { return $this->gateway; }
    public function checkStatus(GatewayConfig $config, string $referenceId): array
    { ++$this->checks; return $this->result; }
}

final class BookingLifecyclePaymentFixture extends \App\Services\BookingService\BookingService
{
    public function sendAllUpdateBooking(Booking $model, string $key, array $data, ?string $text = null): void {}
}

abstract class BookingPaidAuthorityFixture extends BookingRefundFixture
{
    protected TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['DB' => \Illuminate\Support\Facades\DB::class, 'Log' => \Illuminate\Support\Facades\Log::class] as $alias => $class) {
            if (!class_exists($alias)) class_alias($class, $alias);
        }
        $this->database->table('transactions')->where('payable_type', Booking::class)->delete();
        $this->database->table('platform_fee_ledger_entries')->where('payable_type', Booking::class)->delete();
        Schema::table('shops', fn (Blueprint $t) => $t->boolean('collect_via_platform')->default(true));
        Schema::create('countries', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('currency_id'); $t->boolean('active')->default(true);
        });
        Schema::create('shop_locations', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('shop_id'); $t->integer('country_id'); $t->integer('type');
        });
        Schema::create('country_payments', function (Blueprint $t): void {
            $t->integer('country_id'); $t->integer('payment_id'); $t->boolean('active');
        });
        foreach (['platform_payment_configs', 'shop_payments'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table): void {
                $t->increments('id'); $t->integer($table === 'shop_payments' ? 'shop_id' : 'country_id');
                $t->integer('payment_id'); $t->boolean('status');
                foreach (['api_key', 'subscription_key', 'api_user', 'target_environment', 'currency', 'merchant_key'] as $field) {
                    $t->string($field)->nullable();
                }
            });
        }
        Schema::create('payment_payloads', function (Blueprint $t): void {
            $t->integer('payment_id'); $t->text('payload')->nullable();
        });
        Schema::create('payment_process', function (Blueprint $t): void {
            $t->string('id')->primary(); $t->integer('user_id'); $t->string('model_type'); $t->integer('model_id'); $t->text('data');
        });
        $db = $this->database;
        $db->table('currencies')->where('id', 1)->update(['title' => 'EUR']);
        $db->table('countries')->insert(['id' => 10, 'currency_id' => 1, 'active' => true]);
        $db->table('shop_locations')->insert(['shop_id' => 1, 'country_id' => 10, 'type' => ShopLocation::SERVICE]);
        $db->table('payments')->insert(['id' => 8, 'tag' => 'mtn', 'active' => true]);
        $db->table('payments')->where('id', 1)->update(['active' => true]);
        $db->table('country_payments')->insert(['country_id' => 10, 'payment_id' => 8, 'active' => true]);
        $db->table('platform_payment_configs')->insert([
            'country_id' => 10, 'payment_id' => 8, 'status' => true, 'api_key' => 'synthetic-key',
            'subscription_key' => 'synthetic-subscription', 'api_user' => 'synthetic-user',
            'target_environment' => 'sandbox', 'currency' => 'EUR',
        ]);
        $this->app['config']->set('payment_eligibility', require dirname(__DIR__, 2) . '/config/payment_eligibility.php');
        // Complete the synthetic runtime contract; no callback/provider request is sent.
        $this->app['config']->set('development.urls.api', 'https://synthetic.example');
        $this->app['config']->set('development.payments.mode', 'sandbox');
        $this->app['config']->set('development.payments.sandbox_providers', ['mtn']);
        $this->authenticate(2);
        $this->transactionService = new TransactionService();
        $controller = (new ReflectionClass(BookingPaidControllerFixture::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(\App\Http\Controllers\API\v1\Dashboard\Payment\TransactionController::class, 'service'))
            ->setValue($controller, $this->transactionService);
        (new ReflectionProperty(\App\Http\Controllers\Controller::class, 'language'))->setValue($controller, 'en');
        $this->app->instance(BookingPaidControllerFixture::class, $controller);
        $this->app['router']->post('/fixture/payment/{type}/{id}/transactions', [BookingPaidControllerFixture::class, 'store'])
            ->middleware(\App\Http\Middleware\SanctumCheck::class);
        $this->app['router']->put('/fixture/payment/{type}/{id}/transactions', [BookingPaidControllerFixture::class, 'updateStatus'])
            ->middleware(\App\Http\Middleware\SanctumCheck::class);
    }

    protected function bookingState(): array
    {
        $state = [];
        foreach (['bookings', 'wallets', 'wallet_histories', 'transactions', 'platform_fee_ledger_entries',
            'shops', 'payment_process', 'order_refunds', 'point_histories'] as $table) {
            $state[$table] = hash('sha256', json_encode($this->database->table($table)->orderBy('id')->get()->all(), JSON_THROW_ON_ERROR));
        }
        return $state;
    }

    protected function paymentHttp(array $data, string $method = 'POST'): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/fixture/payment/booking/1/transactions', $method, $data);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try { return $this->app['router']->dispatch($request); }
        catch (\Illuminate\Http\Exceptions\HttpResponseException $e) { return $e->getResponse(); }
        catch (\Illuminate\Validation\ValidationException $e) { return new \Illuminate\Http\JsonResponse(['errors' => $e->errors()], 422); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { return new \Illuminate\Http\JsonResponse([], $e->getStatusCode()); }
    }

    protected function verifiedMtn(): array
    {
        Booking::findOrFail(1)->createTransaction(['price' => 50, 'payment_sys_id' => 8, 'user_id' => 2, 'status' => 'progress']);
        $gateway = new BookingSyntheticGateway();
        $service = new BookingVerifiedMtnFixture($gateway);
        PaymentProcess::create(['id' => 'synthetic-booking-reference', 'user_id' => 2, 'model_type' => Booking::class, 'model_id' => 1,
            'data' => ['payment_id' => 8, 'model_type' => Booking::class, 'model_id' => 1,
                'total_price' => 5000, 'currency' => 'EUR', 'status' => 'progress',
                'mtn_config_fingerprint' => $service->configFingerprint($gateway)]]);
        $controller = (new ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Payment\MtnController::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($controller, 'service'))->setValue($controller, $service);
        (new ReflectionProperty(\App\Http\Controllers\Controller::class, 'language'))->setValue($controller, 'en');
        return [$controller, $service];
    }
}