<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Helpers\EnvironmentPolicy;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\Settings;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use App\Services\PaymentService\Verification\PayPalVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PayPalService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess
     * @throws GuzzleException
     * @throws Exception
     */
    public function processTransaction(array $data): PaymentProcess
    {
        return (new \App\Services\PaymentAccounting\DurableCollections)->initiate($this, $data, 'paypal');

        $payment = Payment::where('tag', Payment::TAG_PAY_PAL)->first();
        [$url, $clientId, $clientSecret, $payload] = $this->resolvePayPalCredentials($payment);
        [$key, $before] = $this->getPayload($data, $payload, (int) $payment->id);
        $credentials = PayPalVerification::credentials($payload ?? []);
        $before['total_price'] = $this->exactMinor(data_get($before, 'total_price'));
        $before['currency'] = strtoupper((string) data_get($before, 'currency'));
        $before['payment_reference'] = (string) Str::uuid();
        $before['merchant_id'] = $credentials['merchant'];
        // PayPal has a single global merchant identity. BaseService has
        // restricted this intent to frozen platform-collection accounting,
        // not a shop-direct flow. No unfrozen conversion quote is used.

        $modelId     = data_get($before, 'model_id');
        $host        = request()->getSchemeAndHttpHost();
        $title       = Settings::where('key', 'title')->first()?->title ?? env('APP_NAME');

        $settlementCurrency = $before['currency'];
        $settlementAmount = PayPalVerification::amount($before['total_price'], $settlementCurrency);
        [$tokenType, $accessToken] = $this->getAccessToken($url, $clientId, $clientSecret);
        $response = Http::withOptions(['verify' => true])->timeout(20)->withHeaders([
            'Accept-Language' => 'en_US',
            'Authorization' => "$tokenType $accessToken",
            'PayPal-Request-Id' => $before['payment_reference'],
        ])->post("$url/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $before['payment_reference'],
                        'payee' => ['merchant_id' => $credentials['merchant']],
                        'amount' => [
                            'currency_code' => $settlementCurrency,
                            'value' => $settlementAmount
                        ]
                    ]
                ],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                            'brand_name'                => $title,
                            'locale'                    => 'en-US',
                            'landing_page'              => 'LOGIN',
                            'shipping_preference'       => 'NO_SHIPPING',
                            'user_action'               => 'PAY_NOW',
                            'return_url'                => "$host/payment-success?$key=$modelId&lang=$this->language",
                            'cancel_url'                => "$host/payment-success?$key=$modelId&lang=$this->language&status=error"
                        ]
                    ]
                ]
        ]);
        if (!$response->successful()) {
            throw new RuntimeException('PayPal checkout unavailable');
        }
        $response = $response->json();

        if (data_get($response, 'error')) {

            $message = data_get($response, 'message', 'Something went wrong');

            $message = implode(',', is_array($message) ? $message : [$message]);

            throw new RuntimeException('PayPal checkout unavailable');
        }

        $links = collect(data_get($response, 'links'));

        $checkoutNowUrl = $links->where('rel', 'approve')->first()['href'] ?? null;
        $checkoutNowUrl = $checkoutNowUrl ?? $links->where('rel', 'payer-action')->first()['href'] ?? null;
        if (!is_string(data_get($response, 'id')) || !is_string($checkoutNowUrl)
            || parse_url($checkoutNowUrl, PHP_URL_SCHEME) !== 'https') {
            throw new RuntimeException('PayPal checkout unavailable');
        }

        return PaymentProcess::create([
            'id'         => data_get($response, 'id'),
            'user_id'    => auth('sanctum')->id(),
            'model_type' => data_get($before, 'model_type'),
            'model_id'   => data_get($before, 'model_id'),
            'data' => array_merge($before, [
                'url'        => $checkoutNowUrl,
                'payment_id' => $payment->id,
            ])
        ]);

    }

    /**
     * PayPal's intent=CAPTURE flow does NOT auto-capture once the buyer
     * approves - approval only means PayPal will let us capture; a
     * separate server-side call to this endpoint is required before any
     * money actually moves (confirmed against PayPal's own Orders v2
     * docs). Called from PayPalController::paymentWebHook() on
     * CHECKOUT.ORDER.APPROVED - only THIS call's own result is trusted
     * to mean paid, never the approval event alone.
     *
     * @return array{status: string, capture_id: ?string, raw: array}
     * @throws GuzzleException
     */
    public function captureOrder(string $orderId, ?PaymentProcess $original = null): array
    {
        $payment = Payment::where('tag', Payment::TAG_PAY_PAL)->first();
        [$url, $clientId, $clientSecret] = $this->resolvePayPalCredentials($payment, $original?->id ?? $orderId);
        [$tokenType, $accessToken] = $this->getAccessToken($url, $clientId, $clientSecret);

        if (!preg_match('/^[A-Za-z0-9-]{1,128}$/D', $orderId)) {
            throw new RuntimeException('PayPal order invalid');
        }

        try {
            $response = Http::withOptions(['verify' => true])->timeout(20)->withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => "$tokenType $accessToken",
                    'PayPal-Request-Id' => hash('sha256', 'capture:' . $orderId),
                ])->withBody('{}', 'application/json')->post("$url/v2/checkout/orders/$orderId/capture");
            $response->throw();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorBody = $e->response->json();

            // A retried/duplicate CHECKOUT.ORDER.APPROVED delivery (PayPal
            // does not guarantee exactly-once webhook delivery) hits this
            // exact error on the second attempt - the original capture
            // already ran and already reported its own real result, so
            // this is NOT a failure and must not overwrite an
            // already-paid transaction with a false rejection.
            if (data_get($errorBody, 'details.0.issue') === 'ORDER_ALREADY_CAPTURED') {
                return ['status' => 'ALREADY_CAPTURED', 'capture_id' => null, 'raw' => $errorBody];
            }

            // Any other 4xx/5xx (e.g. ORDER_NOT_APPROVED) is PayPal
            // telling us capture genuinely didn't happen - never treat
            // this as paid.
            return ['status' => 'FAILED', 'capture_id' => null, 'raw' => []];
        }

        $body = $response->json();

        return [
            'status'     => data_get($body, 'purchase_units.0.payments.captures.0.status', data_get($body, 'status', 'FAILED')),
            'capture_id' => data_get($body, 'purchase_units.0.payments.captures.0.id'),
            'raw'        => $body,
        ];
    }

    /**
     * @return array{0: string, 1: ?string, 2: ?string, 3: ?array} [base_url, client_id, client_secret, raw_payload]
     */
    private function resolvePayPalCredentials(?Payment $payment, ?string $reference = null): array
    {
        $paymentPayload = PaymentPayload::where('payment_id', $payment?->id)->first();
        $payload        = $paymentPayload?->payload;
        if ($reference) $payload=(new \App\Services\PaymentAccounting\MerchantRevisions)
            ->forReference($reference,'paypal') ?? $payload;

        if (!EnvironmentPolicy::paymentProviderEnabled('paypal')) {
            throw new ServiceUnavailableHttpException(
                null,
                'PayPal payments are disabled for this application environment.'
            );
        }

        $mode = EnvironmentPolicy::paymentMode();
        if (
            ($mode === 'sandbox' && data_get($payload, 'paypal_mode') !== 'sandbox')
            || ($mode === 'live' && data_get($payload, 'paypal_mode') !== 'live')
        ) {
            throw new ServiceUnavailableHttpException(
                null,
                'PayPal credentials must match the explicitly configured payment mode.'
            );
        }

        $credentials = PayPalVerification::credentials($payload ?? []);
        return [$credentials['url'], $credentials['client'], $credentials['secret'], $payload];
    }

    /**
     * @return array{0: string, 1: ?string} [token_type, access_token]
     * @throws GuzzleException
     */
    private function getAccessToken(string $url, ?string $clientId, ?string $clientSecret): array
    {
        $response = Http::withOptions(['verify' => true])->timeout(20)
            ->withBasicAuth((string) $clientId, (string) $clientSecret)->asForm()
            ->post("$url/v1/oauth2/token", ['grant_type' => 'client_credentials']);
        if (!$response->successful()) {
            throw new RuntimeException('PayPal authentication unavailable');
        }
        $responseAuth = $response->json();
        if (!is_string(data_get($responseAuth, 'access_token')) || !data_get($responseAuth, 'access_token')) {
            throw new RuntimeException('PayPal authentication unavailable');
        }

        return [
            data_get($responseAuth, 'token_type', 'Bearer'),
            data_get($responseAuth, 'access_token'),
        ];
    }

    /**
     * Reject unrepresentable minor units instead of rounding a charge.
     */
    private function exactMinor(mixed $amount): int
    {
        if (!is_numeric($amount) || (float) $amount <= 0
            || (float) $amount !== (float) (int) $amount) {
            throw new RuntimeException('PayPal amount precision unsupported');
        }
        return (int) $amount;
    }

    public function verifiedWebhook(Request $request): array
    {
        if (!PayPalVerification::hasSignatureHeaders($request)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(401, 'Payment verification failed');
        }
        $payment = Payment::where('tag', Payment::TAG_PAY_PAL)->first();
        $candidateOrder = (string)($request->input('resource.supplementary_data.related_ids.order_id')
            ?? $request->input('resource.id'));
        $candidate = (string)($request->input('resource.purchase_units.0.custom_id')
            ?? $request->input('resource.custom_id') ?? $candidateOrder);
        [, , , $payload] = $this->resolvePayPalCredentials($payment, $candidate);
        $credentials = PayPalVerification::credentials($payload ?? []);
        $verifier = new PayPalVerification();
        $token = $verifier->token($credentials);
        if (!$verifier->authenticated($request, $credentials, $token)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(401, 'Payment verification failed');
        }
        $event = $request->input('event_type');
        $orderId = $event === 'CHECKOUT.ORDER.APPROVED'
            ? $request->input('resource.id')
            : $request->input('resource.supplementary_data.related_ids.order_id');
        if (!in_array($event, ['CHECKOUT.ORDER.APPROVED', 'PAYMENT.CAPTURE.COMPLETED'], true)
            || !is_string($orderId)) {
            return ['status' => false, 'message' => 'Payment event not settled'];
        }
        $process = \App\Services\PaymentAccounting\DurableCollections::process($orderId)
            ?? \App\Services\PaymentAccounting\DurableCollections::process($candidate);
        if (!$process || (int) data_get($process->data, 'payment_id') !== $payment->id) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(400, 'Payment verification failed');
        }
        // Before capture, independently retrieve the order and verify the
        // frozen identity/merchant/amount. Approval alone is never settlement.
        $order = $verifier->order($orderId, $credentials, $token);
        if ($event === 'CHECKOUT.ORDER.APPROVED' && ($order['status'] ?? null) !== 'COMPLETED') {
            $unit = $order['purchase_units'][0] ?? [];
            if (count($order['purchase_units'] ?? []) !== 1
                || ($unit['reference_id'] ?? null) !== data_get($process->data, 'payment_reference')
                || empty(data_get($process->data, 'payment_reference'))
                || ($unit['payee']['merchant_id'] ?? null) !== $credentials['merchant']
                || data_get($process->data, 'merchant_id') !== $credentials['merchant']
                || ($unit['amount']['currency_code'] ?? null) !== data_get($process->data, 'currency')
                || PayPalVerification::minor((string) ($unit['amount']['value'] ?? '')) !== data_get($process->data, 'total_price')) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(400, 'Payment verification failed');
            }
            $this->captureOrder($orderId, $process);
            $order = $verifier->order($orderId, $credentials, $token);
        }
        $proof = PayPalVerification::proof($order, $orderId, $process->data, $credentials['merchant']);
        if (!$proof) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(400, 'Payment verification failed');
        }
        $proof['reference'] = !empty($process->data['generic_attempt_id']) ? $process->id : $orderId;
        return $this->afterHook($process->id, \App\Models\Transaction::STATUS_PAID, null, $proof);
    }

}
