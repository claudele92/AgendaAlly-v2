<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Payout;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Model;
use Str;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class OrangeService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess|Model
     * @throws ApiErrorException|Throwable
     */
    public function processTransaction(array $data): Model|PaymentProcess
    {
        // Orange's QR callback has no verified signature or authoritative
        // status API wired in this adapter. Do not create a payable that
        // cannot later be authenticated.
        throw new ServiceUnavailableHttpException(0, 'Orange Money payment verification is unavailable');

        /** @var Payment $payment */
        $payment = Payment::query()->firstOrCreate(
            ['tag' => Payment::TAG_ORANGE],
            ['active' => true, 'input' => 15]
        );

        [$key, $before] = $this->getPayload($data, [], (int) $payment->id);

        $modelId = data_get($before, 'model_id');
        $config  = $this->resolveGatewayConfig($before, $payment->id);

        if (!$config?->hasOrangeCredentials()) {
            throw new Exception('Orange Money has not been configured for this transaction yet');
        }
        if (strtoupper((string) $config->getCurrency()) !== strtoupper((string) data_get($before, 'currency'))) {
            throw new Exception('Orange Money currency does not match the checkout currency');
        }

        // Orange's OAuth client credentials: client_id is the existing
        // shared client_id column, merchant_key is the client_secret
        // (encrypted at rest — see ShopPayment/PlatformPaymentConfig).
        $baseUrl = $config->getBaseUrl() ?: 'https://api.sandbox.orange-sonatel.com';
        if (!str_starts_with(strtolower($baseUrl), 'https://')) {
            throw new Exception('Orange Money requires an HTTPS provider endpoint');
        }

        $tokenPayload = $this->getToken($baseUrl, [
            'client_id'     => $config->getClientId(),
            'client_secret' => $config->getMerchantKey(),
        ]);

        $token = $tokenPayload['token']['access_token'];

        $host = request()->getSchemeAndHttpHost();

        $amountMinor = data_get($before, 'total_price');
        if (!is_numeric($amountMinor) || (int) $amountMinor <= 0 || (int) $amountMinor % 100 !== 0) {
            throw new Exception('Orange Money only supports positive whole-unit amounts for the selected currency');
        }
        $amount = (int) $amountMinor / 100;

        $request = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => "Bearer $token",
        ])
            ->post("$baseUrl/api/eWallet/v4/qrcode", [
                'amount' => ['unit' => $config->getCurrency(), 'value' => $amount],
                'callbackCancelUrl'  => "$host/api/v1/webhook/orange/payment",
                'callbackSuccessUrl' => "$host/api/v1/webhook/orange/payment",
                'code' => 123456,
                'metadata' => (object) [$key => $modelId],
                'name' => auth('sanctum')->user()->full_name,
                'validity' => 15,
            ]);

        if (!$request->successful() || !is_string($request->json('deepLink'))) {
            throw new Exception('Orange Money could not create a payment request');
        }

        return PaymentProcess::create([
            'id' => Str::uuid()->toString(),
            'user_id'    => auth('sanctum')->id(),
            'model_type' => data_get($before, 'model_type'),
            'model_id'   => $modelId,
            'data' => array_merge([
                'url'        => $request->json('deepLink'),
                'payment_id' => $payment->id,
            ], $before)
        ]);
    }

    /**
     * @param string $baseUrl
     * @param array $payload
     * @return array
     * @throws Exception
     */
    public function getToken(string $baseUrl, array $payload): array
    {
        try {

            $response = Http::asForm()->post("$baseUrl/oauth/token", [
                    'client_id'     => $payload['client_id'] ?? null,
                    'client_secret' => $payload['client_secret'] ?? null,
                    'grant_type'    => 'client_credentials',
                ]);
            $getToken = $response->json();

            if (!$response->successful() || !isset($getToken['access_token'])) {
                throw new Exception('Orange Money token request failed');
            }

            $payload['token'] = $getToken;

        } catch (Throwable $e) {
            throw new Exception('Orange Money authentication failed');
        }

        return $payload;
    }
}
