<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\ResponseError;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Services\PaymentService\MtnService;
use App\Services\PaymentService\Verification\DecimalAmount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MtnController extends PaymentBaseController
{
    public function __construct(private MtnService $service)
    {
        parent::__construct($service);
    }

    /**
     * MTN's own webhook shape: `externalId` is the reference id we
     * generated at request time (see MtnService::processTransaction),
     * `status` is SUCCESSFUL/FAILED/PENDING. Delivery isn't guaranteed —
     * see the mtn:reconcile-pending-payments scheduled command for the
     * backstop when it doesn't arrive.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentWebHook(Request $request): JsonResponse
    {
        try {
            $token = $request->input('externalId');
            $paymentProcess = $token ? PaymentProcess::query()->find($token) : null;
            if (!$paymentProcess
                || (string) data_get($paymentProcess->data, 'mtn_reference_id') !== (string) $token
                || !$this->isMtnProcess($paymentProcess)) {
                return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
            }

            if ($paymentProcess->mtn_funding_event_key) {
                $settled=$this->service->reconcileAttempt($paymentProcess->id);
                return ($settled['status'] ?? false)
                    ? $this->successResponse(__('errors.' . ResponseError::NO_ERROR))
                    : response()->json(['status'=>false,'message'=>'Payment verification failed'],400);
            }

            // MTN callbacks are not signed. Treat their reference/status as
            // a notification only and authenticate current state by polling
            // MTN with the merchant credentials belonging to the frozen
            // payment intent.
            $config = $this->service->resolveGatewayConfig($paymentProcess->data, (int) data_get($paymentProcess->data, 'payment_id'));
            if (!$config) {
                return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
            }
            if (!hash_equals(
                (string) data_get($paymentProcess->data, 'mtn_config_fingerprint', ''),
                $this->service->configFingerprint($config)
            )) {
                return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
            }

            $result = $this->service->checkStatus($config, (string) $token);
            $status = $this->statusFromMtn($result);
            if ($status === null || !$this->mtnResultMatchesIntent($result, $paymentProcess, $config->getCurrency())) {
                return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
            }

            $settled = $this->service->afterHook($token, $status, null, $this->proof($paymentProcess, $result));
            if (!data_get($settled, 'status')) {
                return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
            }

        } catch (Throwable $e) {
            Log::warning('MTN callback verification failed', ['exception' => get_class($e)]);
            return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
        }

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR));
    }

    /**
     * Lets the client (or an admin) actively poll MTN for a pending
     * request-to-pay's status, rather than only waiting on the webhook.
     *
     * @param string $referenceId
     * @return JsonResponse
     */
    public function checkStatus(string $referenceId): JsonResponse
    {
        try {
            $paymentProcess = PaymentProcess::find($referenceId);

            if (!$paymentProcess) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }
            if ((int) $paymentProcess->user_id !== (int) auth('sanctum')->id() || !$this->isMtnProcess($paymentProcess)) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }

            if ($paymentProcess->mtn_funding_event_key) {
                $result=$this->service->reconcileAttempt($paymentProcess->id);
                return ($result['status'] ?? false)
                    ? $this->successResponse(__('errors.' . ResponseError::NO_ERROR),$result)
                    : $this->onErrorResponse(['code'=>ResponseError::ERROR_400,'message'=>'Payment verification failed']);
            }

            $config = $this->service->resolveGatewayConfig($paymentProcess->data, data_get($paymentProcess->data, 'payment_id'));

            if (!$config) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }
            if (!hash_equals(
                (string) data_get($paymentProcess->data, 'mtn_config_fingerprint', ''),
                $this->service->configFingerprint($config)
            )) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
            }

            $result = $this->service->checkStatus($config, $referenceId);

            $status = $this->statusFromMtn($result);
            if ($status === null || !$this->mtnResultMatchesIntent($result, $paymentProcess, $config->getCurrency())) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'Payment verification failed']);
            }

            $settled = $this->service->afterHook($referenceId, $status, null, $this->proof($paymentProcess, $result));
            if (!data_get($settled, 'status')) {
                return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'Payment verification failed']);
            }

            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), $result);
        } catch (Throwable $e) {
            Log::warning('MTN status verification failed', ['exception' => get_class($e)]);

            return $this->onErrorResponse(['message' => 'Payment verification failed']);
        }
    }

    private function statusFromMtn(array $result): ?string
    {
        return match (data_get($result, 'status')) {
            'FAILED' => Transaction::STATUS_CANCELED,
            'SUCCESSFUL' => Transaction::STATUS_PAID,
            'PENDING' => Transaction::STATUS_PROGRESS,
            default => null,
        };
    }

    private function mtnResultMatchesIntent(array $result, PaymentProcess $process, ?string $merchantCurrency): bool
    {
        if ((string) data_get($result, 'externalId') !== (string) $process->id
            || strtoupper((string) data_get($result, 'currency')) !== strtoupper((string) $merchantCurrency)
            || strtoupper((string) data_get($result, 'currency')) !== strtoupper((string) data_get($process->data, 'currency'))) {
            return false;
        }

        $amountMinor = DecimalAmount::minor(data_get($result, 'amount'));
        $expectedAmountMinor = $this->expectedAmountMinor(data_get($process->data, 'total_price'));

        return $amountMinor !== null
            && $expectedAmountMinor !== null
            && $amountMinor === $expectedAmountMinor;
    }

    private function proof(PaymentProcess $process, array $result): array
    {
        return [
            'authenticated' => true,
            'merchant_verified' => true,
            'reference' => $process->id,
            'amount_minor' => DecimalAmount::minor(data_get($result, 'amount')),
            'currency' => data_get($result, 'currency'),
            'payment_id' => data_get($process->data, 'payment_id'),
            'model_type' => $process->model_type,
            'model_id' => $process->model_id,
        ];
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

    private function isMtnProcess(PaymentProcess $process): bool
    {
        return $process->mtn_funding_event_key !== null && Payment::query()
            ->whereKey(data_get($process->data, 'payment_id'))
            ->where('tag', Payment::TAG_MTN)
            ->exists();
    }
}
