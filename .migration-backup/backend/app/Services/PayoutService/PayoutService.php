<?php
declare(strict_types=1);

namespace App\Services\PayoutService;

use App\Helpers\ResponseError;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Rules\PositiveWalletAmount;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletDebit;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Support\Facades\DB;
use Throwable;

class PayoutService extends CoreService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $data['status'] = 'pending';
            $this->model()->create($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => ResponseError::ERROR_501, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(Payout $payout, array $data): array
    {
        try {
            return DB::transaction(function () use ($payout, $data): array {
                $payout = Payout::query()->lockForUpdate()->findOrFail($payout->id);
                // Requests cannot drive financial status or replace its actors.
                $data = array_intersect_key($data, array_flip(['currency_id', 'payment_id', 'cause', 'answer', 'price']));
                if ($payout->status === Payout::STATUS_ACCEPTED
                    && array_intersect_key($data, array_flip(['currency_id', 'payment_id', 'price']))) {
                    return ['status' => false, 'code' => ResponseError::ERROR_400,
                        'message' => 'Accepted payout financial terms are immutable'];
                }
                if (!$data) return ['status' => true, 'message' => ResponseError::NO_ERROR];
                if (!$payout->update($data)) throw new \RuntimeException('Payout update failed.');
                return ['status' => true, 'message' => ResponseError::NO_ERROR];
            });


        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function delete(?array $ids = []): void
    {

        foreach (Payout::find(is_array($ids) ? $ids : []) as $payout) {

            if ($payout->created_by !== auth('sanctum')->id()) {
                continue;
            }

            $payout->delete();
        }

    }

    /**
     * @param int|null $id
     * @param string|null $status
     * @return array
     * @throws Throwable
     */
    public function statusChange(?int $id = null, ?string $status = null): array
    {
        if (empty($id) || !in_array($status, Payout::STATUSES)) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }

        $actor = auth('sanctum')->user();
        if (!$actor?->hasRole(['admin', 'manager'])) {
            return ['status' => false, 'code' => ResponseError::ERROR_101];
        }
        try {
            return DB::transaction(function () use ($id, $status, $actor): array {
                $payout = Payout::query()->lockForUpdate()->find($id);
                if (!$payout) return ['status' => false, 'code' => ResponseError::ERROR_404];
                if ($payout->status === Payout::STATUS_ACCEPTED || $payout->status === $status) {
                    return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => 'Payout already ' . $payout->status];
                }
                if ($status !== Payout::STATUS_ACCEPTED) {
                    // Pending/canceled lifecycle changes do not collect or settle money.
                    if (!$payout->update(['status' => $status, 'approved_by' => $actor->id])
                        || !Payout::query()->whereKey($id)->where('status', $status)->exists()) {
                        throw new \RuntimeException('Payout status update failed.');
                    }
                    return ['status' => true, 'code' => ResponseError::NO_ERROR];
                }
                if (!PositiveWalletAmount::accepts($payout->price) || !$payout->createdBy || !$payout->payment) {
                    throw new \RuntimeException('Payout amount, recipient and payment are required.');
                }
                // Stable lock order also serializes distinct payouts sharing a funding Wallet.
                $wallets = Wallet::query()->whereIn('user_id', [$actor->id, $payout->created_by])
                    ->orderBy('id')->lockForUpdate()->get();
                $authWallet = $wallets->firstWhere('user_id', $actor->id);
                $recipientWallet = $wallets->firstWhere('user_id', $payout->created_by);
                if (!$authWallet || !$recipientWallet) throw new \RuntimeException('Payout Wallet is unavailable.');
                if ($authWallet->price < $payout->price) {
                    return ['status' => false, 'code' => ResponseError::ERROR_109, 'message' => 'Insufficient wallet balance'];
                }
                $actor->setRelation('wallet', $authWallet);
                $payout->createdBy->setRelation('wallet', $recipientWallet);
                $payout->setRelation('approvedBy', $actor);
                if (Payout::query()->whereKey($id)->where('status', $payout->status)
                    ->update(['status' => Payout::STATUS_ACCEPTED]) !== 1) {
                    throw new \RuntimeException('Payout claim was not acquired.');
                }
                $createdByNote = "Payment for {$payout->createdBy->firstname}/{$payout->createdBy->lastname}";
                $approvedByNote = "Payment for {$actor->firstname}/{$actor->lastname}";
                if ($payout->payment->tag === 'wallet') {
                    $this->walletHistory($payout, $authWallet, $createdByNote, $approvedByNote);
                }
                // Retain native Wallet-payable progress bookkeeping, not external payout proof.
                foreach ([[$recipientWallet, $payout->created_by, $createdByNote],
                    [$authWallet, $actor->id, $approvedByNote]] as [$wallet, $userId, $note]) {
                    // Same native Payable key, but retain the actual save boolean:
                    // updateOrCreate hides a vetoed update on an existing summary.
                    $transaction = $wallet->transactions()->whereNull('parent_id')->firstOrNew([
                        'payable_id' => $wallet->id, 'payable_type' => get_class($wallet),
                        'payment_sys_id' => $payout->payment_id,
                    ]);
                    $transaction->fill([
                        'price' => $payout->price, 'user_id' => $userId, 'payment_sys_id' => $payout->payment_id,
                        'payment_trx_id' => null, 'note' => $note, 'perform_time' => now(),
                        'status_description' => $note, 'status' => Transaction::STATUS_PROGRESS,
                    ]);
                    if (!$transaction->save() || !$transaction->exists || !Transaction::query()->whereKey($transaction->id)
                        ->where('status', Transaction::STATUS_PROGRESS)->exists()) {
                        throw new \RuntimeException('Required payout Transaction was not saved.');
                    }
                }
                if (!$payout->update(['status' => $status, 'approved_by' => $actor->id])
                    || !Payout::query()->whereKey($id)->where('status', $status)
                        ->where('approved_by', $actor->id)->exists()) {
                    throw new \RuntimeException('Payout finalization failed.');
                }
                // Saved histories/summaries and model callbacks must not leave
                // an accepted payout referring to a missing or replaced Wallet.
                // Re-read both original identities inside the owning transaction.
                WalletDebit::lockOrdered([
                    [$authWallet, (int) $actor->id],
                    [$recipientWallet, (int) $payout->created_by],
                ]);
                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            });
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param Payout $payout
     * @param Wallet $authWallet
     * @param string $createdByNote
     * @param string $approveBydNote
     * @return void
     * @throws Throwable
     */
    public function walletHistory(Payout $payout, Wallet $authWallet, string $createdByNote, string $approveBydNote): void
    {
        $service = app(WalletHistoryService::class);
        $credit = $service->createForPayout([
            'type'      => 'topup',
            'price'     => $payout->price,
            'note'      => $createdByNote,
            'status'    => WalletHistory::PAID,
            'user'      => $payout->createdBy
        ]);

        if (($credit['status'] ?? false) !== true) throw new \RuntimeException('Required payout recipient credit failed.');
        // The native withdraw history performs the one and only funding debit.
        $debit = $service->createForPayout([
            'type'      => 'withdraw',
            'price'     => $payout->price,
            'note'      => $approveBydNote,
            'status'    => WalletHistory::PAID,
            'user'      => $payout->approvedBy
        ]);
        if (($debit['status'] ?? false) !== true) throw new \RuntimeException('Required payout funding debit failed.');
    }
}
