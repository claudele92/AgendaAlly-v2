<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Services\PaymentService\FlutterWaveService;
use App\Services\PaymentService\Verification\DecimalAmount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterWaveController extends PaymentBaseController
{
    public function __construct(private FlutterWaveService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function paymentWebHook(Request $request): array
    {
        $payment = Payment::query()->where('tag', Payment::TAG_FLUTTER_WAVE)->first();
        $payload = PaymentPayload::query()->where('payment_id', $payment?->id)->first()?->payload;
        $payload = (new \App\Services\PaymentAccounting\MerchantRevisions)->forReference(
            (string)$request->input('data.tx_ref'),'flutter-wave') ?? $payload;
        $apiSecret = data_get($payload, 'flw_sk');
        $webhookSecret = data_get($payload, 'flw_webhook_secret_hash');
        $merchantAccountId = data_get($payload, 'flw_account_id');
        $signature = (string) $request->header('verif-hash');

        // Flutterwave's configured secret hash is the callback authenticator.
        // Do not trust the event's status/amount alone: fetch the transaction
        // independently using the server-side API key before settlement.
        if (!$apiSecret || !$webhookSecret || !$merchantAccountId
            || !$signature || !hash_equals((string) $webhookSecret, $signature)) {
            return ['status' => false, 'message' => 'Invalid payment callback'];
        }

        $transactionId = $request->input('data.id');
        $reference = (string) ($request->input('data.tx_ref') ?? '');
        if (!$transactionId || $reference === '') {
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $process = \App\Services\PaymentAccounting\DurableCollections::process($reference);
        if (!$process || (int) data_get($process->data, 'payment_id') !== (int) $payment?->id) {
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        $response = Http::withToken((string) $apiSecret)
            ->acceptJson()
            ->get("https://api.flutterwave.com/v3/transactions/" . rawurlencode((string) $transactionId) . "/verify");

        $verified = $response->json('data');
        $verifiedAmountMinor = DecimalAmount::minor(data_get($verified, 'amount'));
        $expectedAmountMinor = $this->expectedAmountMinor(data_get($process->data, 'total_price'));
        if (!$response->successful()
            || $response->json('status') !== 'success'
            || data_get($verified, 'status') !== 'successful'
            || (string) data_get($verified, 'account_id') !== (string) $merchantAccountId
            || (string) data_get($verified, 'tx_ref') !== $reference
            || strtoupper((string) data_get($verified, 'currency')) !== strtoupper((string) data_get($process->data, 'currency'))
            || $verifiedAmountMinor === null
            || $expectedAmountMinor === null
            || $verifiedAmountMinor !== $expectedAmountMinor) {
            Log::warning('Rejected mismatched Flutterwave callback', ['payment_process_id' => $process->id]);
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        return $this->service->afterHook($reference, Transaction::STATUS_PAID, null, [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => $reference,
            'provider_payment_id' => (string)data_get($verified,'id',$transactionId),
            'amount_minor' => $verifiedAmountMinor,
            'currency' => data_get($verified, 'currency'),
            'payment_id' => data_get($process->data, 'payment_id'),
            'model_type' => $process->model_type,
            'model_id' => $process->model_id,
        ]);
    }

    private function expectedAmountMinor(mixed $amount): ?int
    {
        if (is_int($amount)) {
            return $amount > 0 ? $amount : null;
        }

        return is_string($amount) && preg_match('/^[1-9][0-9]*$/D', $amount)
            ? (filter_var($amount, FILTER_VALIDATE_INT) ?: null)
            : null;
    }

}
