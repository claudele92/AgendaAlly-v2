<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Services\PaymentService\MtnService;
use App\Services\PaymentService\Verification\DecimalAmount;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Log;
use Throwable;

/**
 * MTN's webhook delivery isn't guaranteed — a successful payment whose
 * callback never arrives would otherwise sit unrecorded indefinitely.
 * This actively polls MTN's own status endpoint for every request-to-pay
 * still unresolved after a few minutes, using the same
 * MtnService::checkStatus()/BaseService::afterHook() path the webhook
 * itself uses, so a late poll and an on-time webhook land identically.
 *
 * Covers both customer-facing checkout (shop-level ShopPayment config)
 * and platform-fee purchases (platform-level PlatformPaymentConfig) —
 * every pending MTN PaymentProcess row is queried by `payment_id` alone,
 * regardless of which config resolved it; resolveGatewayConfig() (see
 * BaseService) picks the right one per row the same way processTransaction
 * did when the row was created.
 *
 * The reconciliation job never invents a failed outcome from age alone:
 * a delayed provider response is still queried and must pass the same
 * authoritative reference/amount/currency checks before settlement.
 */
class ReconcilePendingMtnPayments extends Command
{
    protected $signature = 'mtn:reconcile-pending-payments';

    protected $description = 'Poll MTN for the status of request-to-pay transactions whose webhook has not arrived';

    private const MIN_AGE_MINUTES = 2;

    public function handle(MtnService $service): int
    {
        $payment = Payment::query()->where('tag', Payment::TAG_MTN)->first();

        if (!$payment) {
            return 0;
        }

        $candidates = PaymentProcess::query()
            ->whereNotNull('mtn_funding_event_key')
            ->where('data->payment_id', $payment->id)
            ->get()
            ->filter(fn (PaymentProcess $process) => data_get($process->data, 'mtn_reference_id')
                && !data_get($process->data, 'mtn_resolved'));

        foreach ($candidates as $process) {
            $this->reconcileOne($process, $service);
        }

        return 0;
    }

    private function reconcileOne(PaymentProcess $process, MtnService $service): void
    {
        if (!$process->mtn_funding_event_key) return; // No historical inference, even for internal callers.
        $requestedAt = data_get($process->data, 'requested_at');
        $ageMinutes  = $requestedAt ? Carbon::parse($requestedAt)->diffInMinutes(now()) : null;

        if ($ageMinutes === null || $ageMinutes < self::MIN_AGE_MINUTES) {
            return;
        }

        $referenceId = data_get($process->data, 'mtn_reference_id');

        try {
            if ($process->mtn_funding_event_key) {
                $service->reconcileAttempt($process->id);
                return;
            }

            $config = $service->resolveGatewayConfig($process->data, data_get($process->data, 'payment_id'));

            if (!$config) {
                return;
            }
            if (!hash_equals(
                (string) data_get($process->data, 'mtn_config_fingerprint', ''),
                $service->configFingerprint($config)
            )) {
                return;
            }

            $result = $service->checkStatus($config, $referenceId);

            $status = match ($result['status'] ?? null) {
                'FAILED'     => Transaction::STATUS_CANCELED,
                'SUCCESSFUL' => Transaction::STATUS_PAID,
                default      => null,
            };

            if ($status === null) {
                return;
            }

            if ((string) data_get($result, 'externalId') !== (string) $process->id
                || strtoupper((string) data_get($result, 'currency')) !== strtoupper((string) $config->getCurrency())
                || strtoupper((string) data_get($result, 'currency')) !== strtoupper((string) data_get($process->data, 'currency'))) {
                return;
            }

            $amountMinor = DecimalAmount::minor(data_get($result, 'amount'));
            $expectedAmountMinor = $this->expectedAmountMinor(data_get($process->data, 'total_price'));
            if ($amountMinor === null || $expectedAmountMinor === null || $amountMinor !== $expectedAmountMinor) {
                return;
            }

            Log::debug('mtn reconciliation verified amount', [
                'reference' => $referenceId,
                'amount_minor' => $amountMinor,
                'expected_amount_minor' => $expectedAmountMinor,
            ]);
            $settlement = $service->afterHook($referenceId, $status, null, [
                'authenticated' => true,
                'merchant_verified' => true,
                'reference' => $process->id,
                'amount_minor' => $amountMinor,
                'currency' => data_get($result, 'currency'),
                'payment_id' => data_get($process->data, 'payment_id'),
                'model_type' => $process->model_type,
                'model_id' => $process->model_id,
            ]);
            Log::debug('mtn reconciliation settlement result', ['status' => data_get($settlement, 'status')]);
            if (data_get($settlement, 'status')) {
                $this->markResolved($process->refresh());
            }
        } catch (Throwable $e) {
            Log::error('mtn:reconcile-pending-payments', [
                'reference_id' => $referenceId,
                'exception'    => get_class($e),
            ]);
        }
    }

    private function markResolved(PaymentProcess $process): void
    {
        $process->update([
            'data' => array_merge($process->data, ['mtn_resolved' => true]),
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
