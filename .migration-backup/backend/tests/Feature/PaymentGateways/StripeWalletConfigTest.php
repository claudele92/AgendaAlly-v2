<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\ShopAdsPackage;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\PaymentService\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Apple Pay/Google Pay ride the existing hosted Stripe Checkout Session
 * unchanged — they're wallet UI variants of 'card', not a separate
 * integration — scoped to platform-fee purchases (Subscription,
 * ShopAdsPackage) only via a dedicated Stripe "Payment Method
 * Configuration" ID, set on the Stripe payload's
 * wallet_payment_method_configuration field. Everything else keeps the
 * plain card-only Checkout Session unchanged, so wallets never appear
 * for regular shop checkout.
 */
class StripeWalletConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * StripeService::paymentMethodParams() is the one piece of new logic
     * here; it never calls Stripe's API, so it's tested directly via
     * reflection rather than through the full processTransaction() flow
     * (which would require faking the Stripe SDK's own HTTP layer, not
     * Laravel's — a different, heavier concern than this small change
     * warrants).
     */
    private function callPaymentMethodParams(?string $modelType, ?array $payload): array
    {
        $method = new ReflectionMethod(StripeService::class, 'paymentMethodParams');
        $method->setAccessible(true);

        return $method->invoke(new StripeService(), $modelType, $payload);
    }

    public function test_subscription_purchase_gets_the_wallet_configuration_when_one_is_set(): void
    {
        $params = $this->callPaymentMethodParams(Subscription::class, ['wallet_payment_method_configuration' => 'pmc_123']);

        $this->assertSame(['payment_method_configuration' => 'pmc_123'], $params);
    }

    public function test_ads_package_purchase_gets_the_wallet_configuration_when_one_is_set(): void
    {
        $params = $this->callPaymentMethodParams(ShopAdsPackage::class, ['wallet_payment_method_configuration' => 'pmc_123']);

        $this->assertSame(['payment_method_configuration' => 'pmc_123'], $params);
    }

    public function test_a_booking_purchase_never_gets_the_wallet_configuration_even_if_one_is_set(): void
    {
        $params = $this->callPaymentMethodParams(Booking::class, ['wallet_payment_method_configuration' => 'pmc_123']);

        $this->assertSame(['payment_method_types' => ['card']], $params);
    }

    public function test_a_cart_purchase_never_gets_the_wallet_configuration_even_if_one_is_set(): void
    {
        $params = $this->callPaymentMethodParams(Cart::class, ['wallet_payment_method_configuration' => 'pmc_123']);

        $this->assertSame(['payment_method_types' => ['card']], $params);
    }

    public function test_a_subscription_purchase_falls_back_to_plain_card_when_no_wallet_configuration_is_set(): void
    {
        $params = $this->callPaymentMethodParams(Subscription::class, []);

        $this->assertSame(['payment_method_types' => ['card']], $params);
    }

    /**
     * The webhook/completion path is untouched by this change — proving
     * a platform-fee Stripe transaction still resolves to paid exactly
     * the same way a regular one does, regardless of which payment
     * method params the session was created with.
     */
    public function test_a_subscription_stripe_transaction_completes_via_the_same_webhook_path(): void
    {
        $payment = Payment::query()->create(['tag' => Payment::TAG_STRIPE, 'active' => true, 'input' => 15]);
        $subscription = Subscription::factory()->create(['price' => 500, 'active' => 1]);

        $paymentIntentId  = 'pi_test_' . uniqid();
        $checkoutSessionId = 'cs_test_' . uniqid();

        // StripeController::paymentWebHook() resolves PaymentProcess by the
        // checkout session id it looks up from Stripe (data.0.id below),
        // not the original webhook payload's own id — matches
        // StripeService::processTransaction()'s `$session->payment_intent
        // ?? $session->id` fallback when payment_intent isn't the one
        // Stripe actually returns for this session.
        $process = PaymentProcess::updateOrCreate([
            'user_id'    => null,
            'model_type' => Subscription::class,
            'model_id'   => $subscription->id,
        ], [
            'id'   => $checkoutSessionId,
            'data' => [
                'payment_id' => $payment->id,
                'model_type' => Subscription::class,
                'model_id'   => $subscription->id,
                'status'     => 'new',
            ],
        ]);

        Http::fake([
            '*/v1/checkout/sessions*' => Http::response([
                'data' => [
                    ['id' => $checkoutSessionId, 'payment_status' => 'paid'],
                ],
            ], 200),
        ]);

        $this->postJson('/api/v1/webhook/stripe/payment', [
            'data' => ['object' => ['id' => $paymentIntentId]],
        ])->assertStatus(200);

        $process->refresh();
        $this->assertSame(Transaction::STATUS_PAID, $process->data['status']);
    }
}
