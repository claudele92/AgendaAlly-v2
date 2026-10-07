<?php
declare(strict_types=1);

namespace App\Services\WalletHistoryService;

use DB;
use Log;
use Throwable;
use App\Models\User;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Services\CoreService;
use App\Models\WalletHistory;
use App\Helpers\ResponseError;
use App\Rules\PositiveWalletAmount;

class WalletHistoryService extends CoreService
{
    protected function getModelClass(): string
    {
        return WalletHistory::class;
    }

    /**
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function create(array $data): array
    {
        return $this->createHistory($data, false);
    }

    /** Product fulfillment opt-in; existing callers and overrides retain their API. */
    public function createForFulfillment(array $data): array
    {
        return $this->createHistory($data, true);
    }

    /** Native payout requires both persisted history legs and their arithmetic. */
    public function createForPayout(array $data): array
    {
        return $this->createHistory($data, true);
    }

    private function createHistory(array $data, bool $requireSuccess): array
    {
        // All reviewed callers express direction with topup/withdraw and pass
        // an unsigned magnitude. Fail before records, observers or arithmetic.
        if (!PositiveWalletAmount::accepts(data_get($data, 'price'))) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }

        if (!data_get($data, 'type') || !data_get($data, 'price') || !data_get($data, 'user')
        ) {
            Log::error('wallet history empty', [
                'type'  => data_get($data, 'type'),
                'price' => data_get($data, 'price'),
                'user'  => data_get($data, 'user'),
                'data'  => $data
            ]);
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'data' => 'empty'];
        }

        $walletHistory = DB::transaction(function () use ($data, $requireSuccess) {

            /** @var User $user */
            $user   = data_get($data, 'user');
            $type   = data_get($data, 'type', 'withdraw');
            $status = $data['status'] ?? WalletHistory::PROCESSED;
            if ($requireSuccess && !$user->wallet?->exists) {
                throw new \RuntimeException('Required settlement Wallet is unavailable.');
            }
            if ($type === 'withdraw') {
                if (!$user->wallet) {
                    throw new \DomainException('Owned Wallet is unavailable.', 109);
                }
                WalletDebit::debit($user->wallet, data_get($data, 'price'), (int) $user->id);
            }

            /** @var WalletHistory $walletHistory */
            $walletHistory = $this->model()->create([
                'uuid'        => Str::uuid(),
                'wallet_uuid' => $user?->wallet?->uuid ?? data_get($user, 'wallet.uuid'),
                'type'        => $type,
                'price'       => data_get($data, 'price'),
                'note'        => data_get($data, 'note'),
                'created_by'  => data_get($data, 'created_by') ?? $user->id,
                'status'      => $status,
            ]);
            if ($requireSuccess && !$walletHistory->exists) {
                throw new \RuntimeException('Required settlement WalletHistory was not saved.');
            }

            $walletId = Payment::where('tag', 'wallet')->first()?->id;

            $status = match($status) {
                WalletHistory::REJECTED => Transaction::STATUS_CANCELED,
                WalletHistory::PROCESSED => Transaction::STATUS_PROGRESS,
                default => $status
            };

            $transaction = $walletHistory->createTransaction([
                'price'                 => data_get($data, 'price'),
                'user_id'               => $user->id,
                'payment_sys_id'        => data_get($data, 'payment_sys_id', $walletId),
                'payment_trx_id'        => data_get($data, 'payment_trx_id', $user->wallet?->id),
                'note'                  => $user->wallet?->id,
                'perform_time'          => now(),
                'status'                => $status,
                'status_description'    => "Transaction for wallet #{$user->wallet?->id}"
            ]);

            if ($requireSuccess && (!$transaction?->exists || !$transaction->newQuery()
                ->whereKey($transaction->id)->where('status', Transaction::STATUS_PAID)->exists())) {
                throw new \RuntimeException('Required settlement Transaction was not saved.');
            }
            $linked = $walletHistory->update(['transaction_id' => $transaction->id]);
            if ($requireSuccess && (!$linked || !$walletHistory->newQuery()->whereKey($walletHistory->id)
                ->where('transaction_id', $transaction->id)->where('status', WalletHistory::PAID)->exists())) {
                throw new \RuntimeException('Required settlement history linkage failed.');
            }

            if ($status === Transaction::STATUS_PAID && $walletHistory->type == 'topup') {
                $credited = $walletHistory->user->wallet()->increment('price', $walletHistory->price);
                if ($requireSuccess && $credited !== 1) {
                    throw new \RuntimeException('Required settlement Wallet credit failed.');
                }
            }

            return $walletHistory;
        });

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $walletHistory];
    }

    /**
     * @param string $uuid
     * @param string|null $status
     * @return array
     */
    public function changeStatus(string $uuid, ?string $status = null, ?int $ownerId = null): array
    {
        // The customer route supplies the authenticated owner; the native
        // privileged Admin route retains its existing authority. Both routes
        // share this terminal-state check and atomic, once-only restoration.
        return DB::transaction(function () use ($uuid, $status, $ownerId): array {
            /** @var WalletHistory $walletHistory */
            $walletHistory = $this->model()->with('user.wallet', 'transaction')
                ->when($ownerId !== null, fn ($query) => $query->whereHas(
                    'wallet', fn ($wallet) => $wallet->where('user_id', $ownerId)
                ))
                ->lockForUpdate()->firstWhere('uuid', $uuid);

            if (!$walletHistory || $walletHistory->status !== WalletHistory::PROCESSED) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            // Do not approve/restore invalid legacy magnitudes; no data repair.
            if (!PositiveWalletAmount::accepts($walletHistory->price)) {
                return ['status' => false, 'code' => ResponseError::ERROR_400];
            }

            $status = in_array($status, [WalletHistory::REJECTED, WalletHistory::CANCELED])
                ? Transaction::STATUS_CANCELED : $status;

            if ($status === Transaction::STATUS_PAID && $walletHistory->type == 'topup') {
                $walletHistory->user->wallet()->increment('price', $walletHistory->price);
            }

            $walletHistory->update([
                'status' => $status,
                'price' => $walletHistory->price
            ]);

            $status = match($status) {
                WalletHistory::REJECTED => Transaction::STATUS_CANCELED,
                WalletHistory::PROCESSED => Transaction::STATUS_PROGRESS,
                default => $status
            };

            $walletHistory->transaction?->update([
                'status' => $status
            ]);

            $isCancel = $status === WalletHistory::REJECTED || $status === WalletHistory::CANCELED;

            if ($isCancel && $walletHistory->type === 'withdraw') {
                $walletHistory->user->wallet()->increment('price', $walletHistory->price);
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        });
    }

}
