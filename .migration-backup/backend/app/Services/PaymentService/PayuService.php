<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use Cache;
use Http;
use Throwable;
use Mockery\Exception;
use App\Models\Payout;
use App\Models\Payment;
use Illuminate\Support\Str;
use App\Models\PaymentProcess;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PayuService extends BaseService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return PaymentProcess|Model
     * @throws Throwable
     */
    public function processTransaction(array $data): Model|PaymentProcess
    {
        throw new ServiceUnavailableHttpException(0, 'Payment provider temporarily unavailable');

        /** @var Payment $payment */
        $payment = Payment::with(['paymentPayload'])->where('tag', Payment::TAG_PAYU)->first();

        $payload = $payment?->paymentPayload?->payload ?? [];

        [$key, $before] = $this->getPayload($data, $payload);

        $host = request()->getSchemeAndHttpHost();

        $modelId      = data_get($before, 'model_id');
        $modelType    = Str::replace('App\\Models\\', '', data_get($before, 'model_type'));
        $clientId     = $payload['client_id'];
        $clientSecret = $payload['client_secret'];
        $merchantId   = $payload['merchant_id'];
        $sandbox      = $payload['sandbox'];

        $authData = Cache::remember('payu_auth_data', 7000, function () use ($sandbox, $clientId, $clientSecret) {
            return $this->getAuthData($sandbox, $clientId, $clientSecret);
        });

        $uuid = Str::uuid();

        $response = Http::withHeaders([
            'Authorization' => $authData['token_type'] . ' ' . $authData['access_token'],
            'merchantId'    => $merchantId,
            'Content-Type'  => 'application/json'
        ])
            ->post(($sandbox ? 'https://uatoneapi.payu.in' : 'https://oneapi.payu.in') . '/payment-links', [
                'subAmount'               => data_get($before, 'total_price') / 100,
                'isPartialPaymentAllowed' => false,
                'description'             => "Payment for $modelType #$modelId",
                'source'                  => 'API',
                'transactionId'           => $uuid,
                'successURL'              => "$host/payment-success?$key=$modelId&lang=$this->language",
                'failureURL'              => "$host/payment-success?$key=$modelId&lang=$this->language&status=error",
            ]);

        if (!in_array($response->status(), [200, 201]) || (int)$response->json('status') === -1) {
            throw new Exception($response->json('message'));
        }

        return PaymentProcess::updateOrCreate([
            'user_id'    => auth('sanctum')->id(),
            'model_type' => data_get($before, 'model_type'),
            'model_id'   => $modelId,
        ], [
            'id' => $uuid,
            'data' => array_merge([
                'url'        => $response->json('result.paymentLink'),
                'invoice_id' => $response->json('result.invoiceNumber'),
                'payment_id' => $payment->id,
            ], $before)
        ]);
    }

    /**
     * @param bool $sandbox
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */
    public function getAuthData(bool $sandbox, string $clientId, string $clientSecret): array
    {
        $authData = Http::withHeaders([
            'accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ])
            ->post(($sandbox ? 'https://uat-accounts.payu.in' : 'https://accounts.payu.in') . '/oauth/token', [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'scope'         => 'create_payment_links',
                'grant_type'    => 'client_credentials',
            ])
            ->json();

        if (!data_get($authData, 'access_token')) {
            throw new Exception($authData['message'] ?? 'Auth error');
        }

        return $authData;
    }
}
