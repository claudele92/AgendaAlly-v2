<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Services\PaymentService\Verification\PayPalVerification;
use App\Services\PaymentService\PayPalService;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PayPalVerificationTest extends IsolatedTestCase
{
    private function intent(): array
    {
        return ['payment_reference' => 'synthetic-reference', 'total_price' => 1025,
            'merchant_id' => 'synthetic-merchant',
            'currency' => 'USD', 'payment_id' => 3, 'model_type' => 'App\\Models\\Booking', 'model_id' => 7];
    }

    private function order(): array
    {
        return ['id' => 'synthetic-order', 'status' => 'COMPLETED', 'purchase_units' => [[
            'reference_id' => 'synthetic-reference', 'payee' => ['merchant_id' => 'synthetic-merchant'],
            'payments' => ['captures' => [['status' => 'COMPLETED',
                'amount' => ['currency_code' => 'USD', 'value' => '10.25']]]],
        ]]];
    }

    public function testExactProviderAmountsNeverRoundUp(): void
    {
        self::assertSame('10.25', PayPalVerification::amount(1025, 'USD'));
        self::assertSame('10', PayPalVerification::amount(1000, 'JPY'));
        self::assertSame(1025, PayPalVerification::minor('10.25'));
        $this->expectException(RuntimeException::class);
        PayPalVerification::amount(1025, 'JPY');
    }

    public function testMissingMerchantAndWebhookConfigFailBeforeNetwork(): void
    {
        $this->expectException(RuntimeException::class);
        PayPalVerification::credentials(['paypal_sandbox_client_id' => 'synthetic-client',
            'paypal_sandbox_client_secret' => 'synthetic-secret']);
    }

    public function testAuthoritativeOrderMustMatchEveryFrozenField(): void
    {
        $order = $this->order();
        self::assertNotNull(PayPalVerification::proof($order, 'synthetic-order', $this->intent(), 'synthetic-merchant'));
        foreach ([
            ['id', 'wrong-order'], ['status', 'APPROVED'],
            ['purchase_units.0.reference_id', 'other-payable'],
            ['purchase_units.0.payee.merchant_id', 'other-tenant'],
            ['purchase_units.0.payments.captures.0.amount.value', '10.26'],
            ['purchase_units.0.payments.captures.0.amount.currency_code', 'EUR'],
            ['purchase_units.0.payments.captures.0.status', 'PENDING'],
        ] as [$field, $value]) {
            $changed = $order;
            data_set($changed, $field, $value);
            self::assertNull(PayPalVerification::proof($changed, 'synthetic-order', $this->intent(), 'synthetic-merchant'), $field);
        }
        $changed = $order;
        $changed['purchase_units'][] = $changed['purchase_units'][0];
        self::assertNull(PayPalVerification::proof($changed, 'synthetic-order', $this->intent(), 'synthetic-merchant'));
    }

    public function testProviderSignatureVerificationIsRequiredAndUsesConfiguredWebhook(): void
    {
        Http::fake(['https://api-m.sandbox.paypal.com/*' => Http::response(['verification_status' => 'SUCCESS'])]);
        $request = Request::create('/callback', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'],
            '{"event_type":"PAYMENT.CAPTURE.COMPLETED"}');
        foreach (['auth-algo' => 'SHA256withRSA', 'cert-url' => 'https://api.paypal.com/cert',
            'transmission-id' => 'synthetic-event', 'transmission-sig' => 'synthetic-signature',
            'transmission-time' => '2026-09-30T00:00:00Z'] as $name => $value) {
            $request->headers->set('paypal-' . $name, $value);
        }
        $credentials = ['url' => 'https://api-m.sandbox.paypal.com', 'webhook' => 'synthetic-webhook'];
        $verifier = new PayPalVerification();
        self::assertTrue($verifier->authenticated($request, $credentials, 'synthetic-token'));
        Http::assertSent(fn ($sent) => $sent['webhook_id'] === 'synthetic-webhook'
            && $sent['webhook_event']['event_type'] === 'PAYMENT.CAPTURE.COMPLETED');
        $request->headers->set('paypal-cert-url', 'https://attacker.invalid/cert');
        self::assertFalse($verifier->authenticated($request, $credentials, 'synthetic-token'));
        $request->headers->remove('paypal-transmission-sig');
        self::assertFalse($verifier->authenticated($request, $credentials, 'synthetic-token'));
    }

    public function testUnsignedWebhookFailsBeforeDatabaseOrProviderAccess(): void
    {
        $service = (new \ReflectionClass(PayPalService::class))->newInstanceWithoutConstructor();
        try {
            $service->verifiedWebhook(Request::create('/callback', 'POST'));
            self::fail('Unsigned callback was accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            self::assertSame(401, $error->getStatusCode());
        }
        Http::assertNothingSent();
    }

    public function testAuthenticatedCompletedWebhookRetrievesAuthoritativeOrderBeforeSettlement(): void
    {
        $schema = $this->database->schema();
        $schema->create('payments', function ($table): void {
            $table->integer('id')->primary(); $table->string('tag');
        });
        $schema->create('payment_payloads', function ($table): void {
            $table->integer('payment_id')->primary(); $table->text('payload');
        });
        $schema->create('payment_process', function ($table): void {
            $table->string('id')->primary(); $table->text('data');
        });
        $this->database->table('payments')->insert(['id' => 3, 'tag' => Payment::TAG_PAY_PAL]);
        $this->database->table('payment_payloads')->insert(['payment_id' => 3, 'payload' => json_encode([
            'paypal_sandbox_client_id' => 'synthetic-client',
            'paypal_sandbox_client_secret' => 'synthetic-secret',
            'paypal_merchant_id' => 'synthetic-merchant', 'paypal_webhook_id' => 'synthetic-webhook',
        ])]);
        $this->database->table('payment_process')->insert(['id' => 'synthetic-order', 'data' => json_encode($this->intent())]);
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'synthetic-token']),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
            '*/v2/checkout/orders/synthetic-order' => Http::response($this->order()),
        ]);
        $service = new class extends PayPalService {
            public array $settlements = [];
            public function __construct() {}
            public function afterHook($token, $status, ?string $secondToken = null, ?array $verification = null): array
            {
                $this->settlements[] = $verification;
                return ['status' => true];
            }
        };
        $request = Request::create('/callback', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
            'resource' => ['supplementary_data' => ['related_ids' => ['order_id' => 'synthetic-order']]],
        ]));
        foreach (['auth-algo' => 'SHA256withRSA', 'cert-url' => 'https://api.paypal.com/cert',
            'transmission-id' => 'synthetic-event', 'transmission-sig' => 'synthetic-signature',
            'transmission-time' => '2026-09-30T00:00:00Z'] as $name => $value) {
            $request->headers->set('paypal-' . $name, $value);
        }
        self::assertTrue($service->verifiedWebhook($request)['status']);
        self::assertCount(1, $service->settlements);
        self::assertSame(1025, $service->settlements[0]['amount_minor']);
        // A valid signature still cannot settle the wrong authoritative amount.
        $wrong = $this->order();
        data_set($wrong, 'purchase_units.0.payments.captures.0.amount.value', '11.00');
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*/v2/checkout/orders/synthetic-order' => Http::response($wrong),
            '*/v1/oauth2/token' => Http::response(['access_token' => 'synthetic-token']),
            '*/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS'])]);
        try {
            $service->verifiedWebhook($request);
            self::fail('Wrong amount was accepted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            self::assertSame(400, $error->getStatusCode());
        }
        self::assertCount(1, $service->settlements);
    }
}