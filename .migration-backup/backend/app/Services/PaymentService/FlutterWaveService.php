<?php
declare(strict_types=1);

namespace App\Services\PaymentService;

use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\User;
use Exception;
use Http;
use Illuminate\Database\Eloquent\Model;
use Str;
use Throwable;

class FlutterWaveService extends BaseService
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
        return (new \App\Services\PaymentAccounting\DurableCollections)->initiate($this, $data, 'flutter-wave');

        $payment = Payment::where('tag', Payment::TAG_FLUTTER_WAVE)->first();

        $paymentPayload = PaymentPayload::where('payment_id', $payment?->id)->first();

        $payload        = $this->requireConfiguredPayload($paymentPayload?->payload, 'Flutterwave');
        if (!data_get($payload, 'flw_sk')
            || !data_get($payload, 'flw_webhook_secret_hash')
            || !data_get($payload, 'flw_account_id')) {
            throw new Exception('Flutterwave account and webhook verification settings are required');
        }

        $host = request()->getSchemeAndHttpHost();

        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . data_get($payload, 'flw_sk')
        ];

        [$key, $before] = $this->getPayload($data, $payload, (int) $payment->id);

        $modelId    = data_get($before, 'model_id');
        $totalPrice = (int) data_get($before, 'total_price') / 100;

        $trxRef     = "$modelId-" . time();

        /** @var User $user */
        $user       = auth('sanctum')->user();

        $data = [
            'tx_ref'            => $trxRef,
            'amount'            => $totalPrice,
            'currency'          => Str::upper(data_get($before, 'currency')),
            'payment_options'   => 'card,account,ussd,mobilemoneyghana,mobilemoneyfranco',
            'redirect_url'      => "$host/payment-success?$key=$modelId&lang=$this->language",
            'customer'          => [
                'name'          => "$user?->firstname $user?->lastname",
                'phonenumber'   => $user?->phone,
                'email'         => $user?->email
            ],
            'customizations'    => [
                'title'         => data_get($payload, 'title', ''),
                'description'   => data_get($payload, 'description', ''),
                'logo'          => data_get($payload, 'logo', ''),
            ]
        ];

        $request = Http::withHeaders($headers)->post('https://api.flutterwave.com/v3/payments', $data);

        $body = json_decode($request->body());

        if (data_get($body, 'status') === 'error') {
            throw new Exception(data_get($body, 'message'));
        }

        return PaymentProcess::create([
            'id' => $trxRef,
            'user_id'    => auth('sanctum')->id(),
            'model_type' => data_get($before, 'model_type'),
            'model_id'   => data_get($before, 'model_id'),
            'data'  => array_merge($before, [
                'url' => data_get($body, 'data.link'),
                'payment_id' => $payment->id,
            ])
        ]);
    }

}
