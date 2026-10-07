<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Console\Commands\ReconcilePendingMtnPayments;
use App\Http\Controllers\API\v1\Dashboard\Payment\FlutterWaveController;
use App\Http\Controllers\API\v1\Dashboard\Payment\MtnController;
use App\Http\Controllers\API\v1\Dashboard\Payment\OrangeController;
use App\Http\Controllers\API\v1\Dashboard\Payment\PayStackController;
use App\Http\Controllers\API\v1\Dashboard\Payment\StripeController;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Services\PaymentService\Contracts\GatewayConfig;
use App\Services\PaymentService\FlutterWaveService;
use App\Services\PaymentService\MtnService;
use App\Services\PaymentService\OrangeService;
use App\Services\PaymentService\PayStackService;
use App\Services\PaymentService\StripeService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class PaymentProviderCallbackTest extends IsolatedTestCase
{
    private const PAYMENT_ID = 5;
    private const REF = 'checkout-reference';

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        Schema::create('payments', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tag');
            $table->boolean('active')->default(true);
        });
        Schema::create('payment_payloads', function (Blueprint $table): void {
            $table->unsignedInteger('payment_id')->primary();
            $table->text('payload')->nullable();
        });
        Schema::create('payment_process', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->text('data');
        });
        Schema::create('translations', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });
    }

    private function putGateway(string $tag, array $payload): void
    {
        $this->database->table('payments')->insert(['id' => self::PAYMENT_ID, 'tag' => $tag, 'active' => true]);
        $this->database->table('payment_payloads')->insert([
            'payment_id' => self::PAYMENT_ID,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
        ]);
    }

    private function putIntent(string $reference = self::REF, array $overrides = []): PaymentProcess
    {
        $data = array_merge([
            'payment_id' => self::PAYMENT_ID,
            'total_price' => 1000,
            'currency' => 'GBP',
            'model_type' => Booking::class,
            'model_id' => 17,
            'status' => Transaction::STATUS_PROGRESS,
        ], $overrides);
        $process = new PaymentProcess();
        $process->setRawAttributes([
            'id' => $reference,
            'user_id' => 9,
            'model_type' => data_get($data, 'model_type'),
            'model_id' => data_get($data, 'model_id'),
            'data' => json_encode($data, JSON_THROW_ON_ERROR),
        ], true);
        $process->save();

        return $process;
    }

    private function controller(string $class, object $service): object
    {
        $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $property = new \ReflectionProperty($class, 'service');
        $property->setAccessible(true);
        $property->setValue($controller, $service);

        return $controller;
    }

    private function request(string $uri, array $data, array $headers = []): Request
    {
        $request = Request::create(
            $uri,
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data, JSON_THROW_ON_ERROR)
        );
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    private function authenticate(int $id): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn($id);
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $factory);
    }

    private function stripeSignature(string $body, string $secret): string
    {
        $timestamp = time();
        return "t=$timestamp,v1=" . hash_hmac('sha256', "$timestamp.$body", $secret);
    }

    public function test_paystack_signed_account_reference_and_exact_amount_are_required_and_duplicate_callbacks_reach_one_intent(): void
    {
        $secret = 'paystack-secret';
        $this->putGateway(Payment::TAG_PAY_STACK, ['paystack_sk' => $secret]);
        $process = $this->putIntent(self::REF);
        $service = new RecordingPayStackService();
        $controller = $this->controller(PayStackController::class, $service);
        $body = json_encode([
            'event' => 'charge.success',
            'data' => ['reference' => self::REF, 'amount' => 1000, 'currency' => 'GBP'],
        ], JSON_THROW_ON_ERROR);

        $invalid = $this->request('/api/v1/webhook/paystack', json_decode($body, true), ['x-paystack-signature' => 'wrong']);
        self::assertFalse($controller->paymentWebHook($invalid)['status']);
        self::assertCount(0, $service->settlements);

        $validSignature = hash_hmac('sha512', $body, $secret);
        $wrongAmount = $this->request('/api/v1/webhook/paystack', [
            'event' => 'charge.success',
            'data' => ['reference' => self::REF, 'amount' => 1001, 'currency' => 'GBP'],
        ], ['x-paystack-signature' => hash_hmac('sha512', json_encode([
            'event' => 'charge.success', 'data' => ['reference' => self::REF, 'amount' => 1001, 'currency' => 'GBP'],
        ], JSON_THROW_ON_ERROR), $secret)]);
        self::assertFalse($controller->paymentWebHook($wrongAmount)['status']);

        foreach ([
            ['reference' => self::REF, 'amount' => 1000.5, 'currency' => 'GBP'],
            ['reference' => self::REF, 'amount' => 1000, 'currency' => 'USD'],
            ['reference' => 'unknown-reference', 'amount' => 1000, 'currency' => 'GBP'],
        ] as $badData) {
            $badEvent = ['event' => 'charge.success', 'data' => $badData];
            $badBody = json_encode($badEvent, JSON_THROW_ON_ERROR);
            self::assertFalse($controller->paymentWebHook($this->request('/api/v1/webhook/paystack', $badEvent, [
                'x-paystack-signature' => hash_hmac('sha512', $badBody, $secret),
            ]))['status']);
        }

        $foreignData = [
            'event' => 'charge.success',
            'data' => ['reference' => 'foreign-reference', 'amount' => 1000, 'currency' => 'GBP'],
        ];
        $this->putIntent('foreign-reference', ['payment_id' => self::PAYMENT_ID + 1]);
        $foreignBody = json_encode($foreignData, JSON_THROW_ON_ERROR);
        $foreign = $this->request('/api/v1/webhook/paystack', $foreignData, [
            'x-paystack-signature' => hash_hmac('sha512', $foreignBody, $secret),
        ]);
        self::assertFalse($controller->paymentWebHook($foreign)['status']);

        $success = $this->request('/api/v1/webhook/paystack', json_decode($body, true), ['x-paystack-signature' => $validSignature]);
        self::assertTrue($controller->paymentWebHook($success)['status']);
        self::assertTrue($controller->paymentWebHook($success)['status']);
        self::assertCount(2, $service->settlements);
        self::assertSame(1000, $service->settlements[0][3]['amount_minor']);
        self::assertSame(Booking::class, $service->settlements[0][3]['model_type']);
    }

    public function test_flutterwave_signature_provider_account_reference_currency_and_amount_are_verified_before_settlement(): void
    {
        $this->putGateway(Payment::TAG_FLUTTER_WAVE, [
            'flw_sk' => 'flw-api-secret',
            'flw_webhook_secret_hash' => 'hook-secret',
            'flw_account_id' => 'account-123',
        ]);
        $this->putIntent(self::REF, ['total_price' => 1999]);
        $service = new RecordingFlutterWaveService();
        $controller = $this->controller(FlutterWaveController::class, $service);
        Http::fake([
            'https://api.flutterwave.com/v3/transactions/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'tx_ref' => self::REF,
                    'amount' => 19.99,
                    'currency' => 'GBP',
                    'account_id' => 'account-123',
                ],
            ], 200),
        ]);
        $event = ['data' => ['id' => 771, 'tx_ref' => self::REF]];

        $badSignature = $this->request('/api/v1/webhook/flutterwave', $event, ['verif-hash' => 'wrong']);
        self::assertFalse($controller->paymentWebHook($badSignature)['status']);
        Http::assertNothingSent();
        self::assertCount(0, $service->settlements);

        $valid = $this->request('/api/v1/webhook/flutterwave', $event, ['verif-hash' => 'hook-secret']);
        self::assertTrue($controller->paymentWebHook($valid)['status']);
        self::assertSame(1999, $service->settlements[0][3]['amount_minor']);
    }

    public function test_flutterwave_provider_response_mismatch_does_not_settle(): void
    {
        $this->putGateway(Payment::TAG_FLUTTER_WAVE, [
            'flw_sk' => 'flw-api-secret',
            'flw_webhook_secret_hash' => 'hook-secret',
            'flw_account_id' => 'account-123',
        ]);
        $this->putIntent(self::REF);
        $service = new RecordingFlutterWaveService();
        $controller = $this->controller(FlutterWaveController::class, $service);
        Http::fake([
            'https://api.flutterwave.com/v3/transactions/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'successful',
                    'tx_ref' => 'wrong-reference',
                    'amount' => 10.01,
                    'currency' => 'USD',
                    'account_id' => 'other-account',
                ],
            ], 200),
        ]);

        $response = $controller->paymentWebHook($this->request('/api/v1/webhook/flutterwave', [
            'data' => ['id' => 771, 'tx_ref' => self::REF],
        ], ['verif-hash' => 'hook-secret']));
        self::assertFalse($response['status']);
        self::assertCount(0, $service->settlements);
    }

    public function test_stripe_signature_api_account_session_reference_and_money_are_required(): void
    {
        $secret = 'stripe-webhook-secret';
        $this->putGateway(Payment::TAG_STRIPE, [
            'stripe_sk' => 'stripe-api-key',
            'stripe_webhook_secret' => $secret,
        ]);
        $this->putIntent('pi_123', ['merchant_id' => 'acct_123']);
        $service = new RecordingStripeService();
        $controller = $this->controller(StripeController::class, $service);
        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response(['id' => 'acct_123'], 200),
            'https://api.stripe.com/v1/checkout/sessions/cs_123' => Http::response([
                'id' => 'cs_123',
                'payment_intent' => 'pi_123',
                'payment_status' => 'paid',
                'amount_total' => 1000,
                'currency' => 'gbp',
            ], 200),
        ]);
        $event = [
            'id' => 'evt_123',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_123', 'payment_intent' => 'pi_123']],
        ];
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $bad = $this->request('/api/v1/webhook/stripe', $event, ['stripe-signature' => 'bad']);
        self::assertFalse($controller->paymentWebHook($bad)['status']);
        Http::assertNothingSent();

        $good = $this->request('/api/v1/webhook/stripe', $event, [
            'stripe-signature' => $this->stripeSignature($body, $secret),
        ]);
        self::assertTrue($controller->paymentWebHook($good)['status']);
        self::assertCount(1, $service->settlements);
        self::assertSame('pi_123', $service->settlements[0][0]);
        self::assertSame(1000, $service->settlements[0][3]['amount_minor']);
    }

    public function test_stripe_account_or_verified_session_mismatch_fails_closed(): void
    {
        $secret = 'stripe-webhook-secret';
        $this->putGateway(Payment::TAG_STRIPE, ['stripe_sk' => 'stripe-api-key', 'stripe_webhook_secret' => $secret]);
        $this->putIntent('pi_123', ['merchant_id' => 'acct_expected']);
        $service = new RecordingStripeService();
        $controller = $this->controller(StripeController::class, $service);
        Http::fake([
            'https://api.stripe.com/v1/account' => Http::response(['id' => 'acct_other'], 200),
            'https://api.stripe.com/v1/checkout/sessions/cs_123' => Http::response([
                'id' => 'cs_123', 'payment_intent' => 'pi_123', 'payment_status' => 'paid',
                'amount_total' => 1000, 'currency' => 'gbp',
            ], 200),
        ]);
        $event = ['id' => 'evt_123', 'type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_123', 'payment_intent' => 'pi_123']]];
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $response = $controller->paymentWebHook($this->request('/api/v1/webhook/stripe', $event, [
            'stripe-signature' => $this->stripeSignature($body, $secret),
        ]));
        self::assertFalse($response['status']);
        self::assertCount(0, $service->settlements);
    }

    public function test_legacy_mtn_status_poll_without_durable_binding_remains_fail_closed(): void
    {
        $this->database->table('payments')->insert(['id' => self::PAYMENT_ID, 'tag' => Payment::TAG_MTN, 'active' => true]);
        $config = new StubGatewayConfig();
        $service = new RecordingMtnService($config, [
            'status' => 'SUCCESSFUL',
            'externalId' => self::REF,
            'amount' => '0.29',
            'currency' => 'GBP',
        ]);
        $fingerprint = $service->configFingerprint($config);
        $process = $this->putIntent(self::REF, [
            'total_price' => 29,
            'mtn_reference_id' => self::REF,
            'mtn_config_fingerprint' => $fingerprint,
        ]);
        $this->authenticate(9);
        $controller = $this->controller(MtnController::class, $service);
        $language = new \ReflectionProperty(\App\Http\Controllers\Controller::class, 'language');
        $language->setAccessible(true);
        $language->setValue($controller, 'en');
        $loggedException = null;
        $logger = Mockery::mock();
        $logger->shouldReceive('warning')->andReturnUsing(function ($message, $context) use (&$loggedException): void {
            $loggedException = $context['exception'] ?? null;
        });
        $this->app->instance('log', $logger);

        $response = $controller->checkStatus(self::REF);
        self::assertFalse($response->getData(true)['status'], (string) $loggedException);
        self::assertCount(0, $service->settlements);
        self::assertSame(0,$service->statusChecks);

        $service->settlements = [];
        $fractional = new RecordingMtnService($config, [
            'status' => 'SUCCESSFUL',
            'externalId' => self::REF,
            'amount' => '0.291',
            'currency' => 'GBP',
        ]);
        $controller = $this->controller(MtnController::class, $fractional);
        $language->setValue($controller, 'en');
        $response = $controller->checkStatus(self::REF);
        self::assertFalse($response->getData(true)['status']);
        self::assertCount(0, $fractional->settlements);

        foreach ([
            ['status' => 'SUCCESSFUL', 'externalId' => 'wrong-reference', 'amount' => '0.29', 'currency' => 'GBP'],
            ['status' => 'SUCCESSFUL', 'externalId' => self::REF, 'amount' => '0.29', 'currency' => 'USD'],
        ] as $mismatch) {
            $unmatched = new RecordingMtnService($config, $mismatch);
            $controller = $this->controller(MtnController::class, $unmatched);
            $language->setValue($controller, 'en');
            $response = $controller->checkStatus(self::REF);
            self::assertFalse($response->getData(true)['status']);
            self::assertCount(0, $unmatched->settlements);
        }

        $wrongAccount = new RecordingMtnService(new StubGatewayConfig(apiKey: 'another-account'), [
            'status' => 'SUCCESSFUL', 'externalId' => self::REF, 'amount' => '0.29', 'currency' => 'GBP',
        ]);
        $controller = $this->controller(MtnController::class, $wrongAccount);
        $language->setValue($controller, 'en');
        $response = $controller->checkStatus(self::REF);
        self::assertFalse($response->getData(true)['status']);
        self::assertCount(0, $wrongAccount->settlements);
    }

    public function test_mtn_worker_does_not_infer_or_settle_legacy_attempt_binding(): void
    {
        $this->database->table('payments')->insert(['id' => self::PAYMENT_ID, 'tag' => Payment::TAG_MTN, 'active' => true]);
        $config = new StubGatewayConfig();
        $service = new RecordingMtnService($config, [
            'status' => 'SUCCESSFUL',
            'externalId' => self::REF,
            'amount' => 19.99,
            'currency' => 'GBP',
        ]);
        $process = $this->putIntent(self::REF, [
            'total_price' => 1999,
            'mtn_reference_id' => self::REF,
            'mtn_config_fingerprint' => $service->configFingerprint($config),
            'mtn_resolved' => false,
            'requested_at' => '2020-01-01 00:00:00',
        ]);
        $process = PaymentProcess::findOrFail(self::REF);
        $command = (new ReflectionClass(ReconcilePendingMtnPayments::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(ReconcilePendingMtnPayments::class, 'reconcileOne');
        $method->setAccessible(true);

        $method->invoke($command, $process, $service);

        self::assertSame(0, $service->statusChecks);
        self::assertCount(0, $service->settlements);
        self::assertFalse((bool) data_get(PaymentProcess::find(self::REF)->data, 'mtn_resolved'));
    }

    public function test_orange_initiation_and_callbacks_fail_closed_without_verified_provider_support(): void
    {
        $service = (new ReflectionClass(OrangeService::class))->newInstanceWithoutConstructor();
        try {
            $service->processTransaction(['booking_id' => 17]);
            self::fail('Orange initiation should be disabled until its callback is verifiable');
        } catch (ServiceUnavailableHttpException) {
            self::assertTrue(true);
        }

        $controller = $this->controller(OrangeController::class, $service);
        $result = $controller->paymentWebHook($this->request('/api/v1/webhook/orange', [
            'id' => self::REF,
            'status' => Transaction::STATUS_PAID,
            'reference' => self::REF,
        ]));
        self::assertFalse($result['status']);
        Http::assertNothingSent();
    }
}

final class RecordingPayStackService extends PayStackService
{
    public array $settlements = [];

    public function __construct()
    {
    }

    public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
    {
        $this->settlements[] = [$token, $status, $secondToken, $verification];
        return ['status' => true];
    }
}

final class RecordingFlutterWaveService extends FlutterWaveService
{
    public array $settlements = [];

    public function __construct()
    {
    }

    public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
    {
        $this->settlements[] = [$token, $status, $secondToken, $verification];
        return ['status' => true];
    }
}

final class RecordingStripeService extends StripeService
{
    public array $settlements = [];

    public function __construct()
    {
    }

    public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
    {
        $this->settlements[] = [$token, $status, $secondToken, $verification];
        return ['status' => true];
    }
}

final class RecordingMtnService extends MtnService
{
    public array $settlements = [];
    public int $statusChecks = 0;
    public array $result;
    private GatewayConfig $config;

    public function __construct(GatewayConfig $config, array $result)
    {
        $this->config = $config;
        $this->result = $result;
    }

    public function resolveGatewayConfig(array $before, int $paymentId): ?GatewayConfig
    {
        return $this->config;
    }

    public function checkStatus(GatewayConfig $config, string $referenceId): array
    {
        $this->statusChecks++;
        return $this->result;
    }

    public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
    {
        $this->settlements[] = [$token, $status, $secondToken, $verification];
        return ['status' => true];
    }
}

final class StubGatewayConfig implements GatewayConfig
{
    public function __construct(private ?string $apiKey = 'mtn-api-key')
    {
    }

    public function getClientId(): ?string { return 'client-id'; }
    public function getMerchantKey(): ?string { return 'merchant-key'; }
    public function getSubscriptionKey(): ?string { return 'subscription-key'; }
    public function getApiUser(): ?string { return 'api-user'; }
    public function getApiKey(): ?string { return $this->apiKey; }
    public function getTargetEnvironment(): ?string { return 'sandbox'; }
    public function getCurrency(): ?string { return 'GBP'; }
    public function getBaseUrl(): ?string { return 'https://sandbox.momodeveloper.mtn.com'; }
    public function hasOrangeCredentials(): bool { return false; }
    public function hasMtnCredentials(): bool { return true; }
}