<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\EnvironmentPolicy;
use App\Http\Requests\FilterParamsRequest;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Models\Translation;
use App\Models\Wallet;
use App\Services\PaymentService\StripeService;
use Http;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Redirect;

class StripeController extends PaymentBaseController
{
    // Last-resort fallback if config('app.front_url') itself is broken
    // (e.g. FRONT_URL set to an empty string rather than unset, which
    // env()'s own default can't catch) - never re-derived from the same
    // config that just failed, so this can't also be empty.
    private const FALLBACK_FRONT_URL = 'https://agendaally.com/';

    public function __construct(private StripeService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function resultTransaction(Request $request): RedirectResponse
    {

        //[
        //  "num_transaction_from_gu" => "1718355554823"
        //  "num_command" => "1718355553865"
        //  "amount" => "100"
        //  "errorCode" => "500"
        //]
        $status         = $request->input('status');
        $parcelId       = (int)$request->input('parcel_id');
        $adsPackageId   = (int)$request->input('ads_package_id');
        $subscriptionId = (int)$request->input('subscription_id');
        $walletId       = (int)$request->input('wallet_id');
        $bookingId      = (int)$request->input('booking_id');
        $giftCartId     = (int)$request->input('gift_cart_id');
        $memberShipId   = (int)$request->input('member_ship_id');

        csrf_token();

        $to = config('app.front_url') . ($status === 'error' ? 'payment/error' : '');

        if ($parcelId) {
            $to = config('app.front_url') . "parcels/$parcelId";
        } else if ($bookingId) {
            $to = config('app.front_url') . 'appointments';
        } else if ($adsPackageId) {
            $to = config('app.admin_url');
        } else if ($subscriptionId) {
            $to = config('app.admin_url');
        } else if ($giftCartId) {
            $to = config('app.front_url') . 'gift-cards';
        } else if ($memberShipId) {
            $to = config('app.front_url') . 'memberships';
        } else if ($walletId) {

            /** @var Wallet $wallet */
            $wallet = Wallet::with('user.roles')->find($walletId);

            $to = config('app.front_url') . 'wallet';

            if ($wallet?->user?->hasRole(['seller', 'admin', 'moderator', 'deliveryman', 'manager', 'shop_manager'])) {
                $to = config('app.admin_url');
            }

        }

        // A bare relative path here (FRONT_URL/ADMIN_URL unset or blank)
        // would resolve against this API's own host rather than the
        // storefront/admin panel, sending the customer's browser to an
        // unmatched API route that renders as a raw JSON 404 - exactly
        // the failure this guard exists to catch instead of doing that
        // silently.
        if (!filter_var($to, FILTER_VALIDATE_URL)) {
            Log::error('Payment redirect resolved to a non-absolute URL; check FRONT_URL/ADMIN_URL configuration');

            $to = self::FALLBACK_FRONT_URL;
        }

        return Redirect::to($to);
    }

    public function mtnProcess(FilterParamsRequest $request): Application|Factory|View
    {
        $buttonText = Translation::where('locale', $request->input('lang'))
            ->where('key', 'mtn')
            ->value('value') ?? 'Continuer';

        return view('mtn', $request->merge(['button_text' => $buttonText])->all());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function paymentWebHook(Request $request): array
    {
        if (!EnvironmentPolicy::paymentProviderEnabled('stripe')) {
            return ['status' => false, 'message' => 'Stripe payments are disabled for this application environment.'];
        }

        $payment = Payment::where('tag', Payment::TAG_STRIPE)->first();
        $paymentPayload = PaymentPayload::where('payment_id', $payment?->id)->first();
        $payload        = $paymentPayload?->payload;
        $candidate = (string)($request->input('data.object.metadata.attempt_id')
            ?? $request->input('data.object.client_reference_id') ?? $request->input('data.object.id'));
        $payload = (new \App\Services\PaymentAccounting\MerchantRevisions)->forReference($candidate,'stripe') ?? $payload;
        $webhookSecret  = data_get($payload, 'stripe_webhook_secret');
        $signature      = (string) $request->header('stripe-signature');
        $secretKey      = (string) data_get($payload, 'stripe_sk', '');

        if (
            !$webhookSecret
            || (EnvironmentPolicy::paymentMode() === 'sandbox' && !str_starts_with($secretKey, 'sk_test_'))
            || (EnvironmentPolicy::paymentMode() === 'live' && !str_starts_with($secretKey, 'sk_live_'))
            || !$this->hasValidStripeSignature($request->getContent(), $signature, $webhookSecret)
        ) {
            return ['status' => false, 'message' => 'Invalid payment callback'];
        }

        $event = json_decode($request->getContent(), true);
        if (!is_array($event) || !in_array(data_get($event, 'type'), [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            return ['status' => true, 'message' => 'Event ignored'];
        }

        $objectId = (string) data_get($event, 'data.object.id', '');
        $paymentIntentId = (string) data_get($event, 'data.object.payment_intent', '');
        if ($objectId === '') {
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $accountResponse = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->get('https://api.stripe.com/v1/account');
        if (!$secretKey || !$accountResponse->successful()) {
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $providerResponse = str_starts_with($objectId, 'cs_')
            ? Http::withBasicAuth($secretKey, '')->acceptJson()
                ->get('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($objectId))
            : Http::withBasicAuth($secretKey, '')->acceptJson()
                ->get('https://api.stripe.com/v1/checkout/sessions', [
                'limit' => 1,
                'payment_intent' => $paymentIntentId ?: $objectId,
            ]);

        if (!$providerResponse->successful()) {
            Log::warning('Stripe provider verification failed', ['event_id' => data_get($event, 'id')]);
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $session = $providerResponse->json('data.0') ?? $providerResponse->json();
        $sessionId = (string) data_get($session, 'id', '');
        $intentId = (string) data_get($session, 'payment_intent', '');
        $paymentProcess = PaymentProcess::query()
            ->where(function ($query) use ($intentId, $sessionId): void {
                $query->where('id', $intentId)->orWhere('id', $sessionId);
            })
            ->first();
        $paymentProcess = $paymentProcess
            ?? \App\Services\PaymentAccounting\DurableCollections::process((string)data_get($session,'client_reference_id'))
            ?? \App\Services\PaymentAccounting\DurableCollections::process($sessionId);
        $generic = !empty($paymentProcess?->data['generic_attempt_id']);

        if (!$paymentProcess
            || (!$generic && !in_array($paymentProcess->id, [$intentId, $sessionId], true))
            || ($generic && (string)data_get($session,'client_reference_id')!==$paymentProcess->id)
            || (int) data_get($paymentProcess->data, 'payment_id') !== (int) $payment?->id
            || (string) data_get($paymentProcess->data, 'merchant_id') !== (string) $accountResponse->json('id', '')
            || data_get($session, 'payment_status') !== 'paid'
            || (int) data_get($session, 'amount_total', -1) !== (int) data_get($paymentProcess->data, 'total_price', -2)
            || strtoupper((string) data_get($session, 'currency')) !== strtoupper((string) data_get($paymentProcess->data, 'currency'))) {
            Log::warning('Rejected mismatched Stripe callback', ['payment_process_id' => $paymentProcess?->id]);
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $token = $generic ? $paymentProcess->id : ($intentId ?: $sessionId);

        return $this->service->afterHook($token, Transaction::STATUS_PAID, $sessionId, [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => $paymentProcess->id,
            'provider_collection_reference' => $sessionId,
            'provider_payment_id' => $intentId,
            'amount_minor' => data_get($session, 'amount_total'),
            'currency' => data_get($session, 'currency'),
            'payment_id' => data_get($paymentProcess->data, 'payment_id'),
            'model_type' => $paymentProcess->model_type,
            'model_id' => $paymentProcess->model_id,
        ]);
    }

    private function hasValidStripeSignature(string $body, string $header, string $secret): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$key][] = $value;
        }

        $timestamp = (int) data_get($parts, 't.0', 0);
        if ($timestamp <= 0 || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $body, $secret);
        foreach (data_get($parts, 'v1', []) as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

}
