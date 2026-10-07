<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Services\PaymentService\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayStackController extends PaymentBaseController
{
    public function __construct(private PayStackService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): array
    {
        $payment = Payment::query()->where('tag', Payment::TAG_PAY_STACK)->first();
        $payload = PaymentPayload::query()->where('payment_id', $payment?->id)->first()?->payload;
        $payload = (new \App\Services\PaymentAccounting\MerchantRevisions)->forReference(
            (string)$request->input('data.reference'),'paystack') ?? $payload;
        $secret = data_get($payload, 'paystack_sk');
        $signature = (string) $request->header('x-paystack-signature');
        $expected = $secret ? hash_hmac('sha512', $request->getContent(), $secret) : '';

        if (!$secret || !$signature || !hash_equals($expected, $signature)) {
            return ['status' => false, 'message' => 'Invalid payment callback'];
        }

        if ($request->input('event') !== 'charge.success') {
            return ['status' => true, 'message' => 'Event ignored'];
        }

        $reference = (string) $request->input('data.reference');
        $process = \App\Services\PaymentAccounting\DurableCollections::process($reference);
        $data = $request->input('data', []);
        $actualAmount = $this->positiveMinorUnits(data_get($data, 'amount'));
        $expectedAmount = $this->positiveMinorUnits(data_get($process?->data, 'total_price'));

        if (!$process
            || (int) data_get($process->data, 'payment_id') !== (int) $payment?->id
            || $actualAmount === null
            || $expectedAmount === null
            || $actualAmount !== $expectedAmount
            || strtoupper((string) data_get($data, 'currency')) !== strtoupper((string) data_get($process->data, 'currency'))) {
            Log::warning('Rejected mismatched Paystack callback', ['payment_process_id' => $process?->id]);
            return ['status' => false, 'message' => 'Payment verification failed'];
        }

        return $this->service->afterHook($reference, Transaction::STATUS_PAID, null, [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => $reference,
            'provider_payment_id' => (string)data_get($data,'id',$reference),
            'amount_minor' => data_get($data, 'amount'),
            'currency' => data_get($data, 'currency'),
            'payment_id' => data_get($process->data, 'payment_id'),
            'model_type' => $process->model_type,
            'model_id' => $process->model_id,
        ]);
    }

    private function positiveMinorUnits(mixed $amount): ?int
    {
        if (is_int($amount)) {
            return $amount > 0 ? $amount : null;
        }

        if (!is_string($amount) || !preg_match('/^[1-9][0-9]*$/D', $amount)) {
            return null;
        }

        $integer = filter_var($amount, FILTER_VALIDATE_INT);

        return is_int($integer) && $integer > 0 ? $integer : null;
    }

}
