<?php
declare(strict_types=1);
namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/** Native single withdrawal may legitimately cover several frozen Shop/service portions. */
final class WalletContributionAdapter
{
    public function prepared(Booking|Cart|Order $model): ?object
    {
        $type = $model instanceof Cart ? 'cart' : ($model instanceof Booking ? 'booking' : 'order');
        return DB::table('payment_collection_contexts as c')
            ->join('commerce_payment_allocations as a','a.id','=','c.allocation_id')
            ->where(function ($query) use ($type,$model): void {
                $query->where(fn ($origin) => $origin->where('a.origin_type',$type)->where('a.origin_id',$model->id));
                if ($type !== 'cart') {
                    $query->orWhere(fn ($bound) => $bound->where('a.payable_type',$type)->where('a.payable_id',$model->id));
                }
            })
            ->where('c.collection_mode','internal')->whereIn('c.state',['committed','pending'])
            ->orderBy('c.id')->select('c.*')->first();
    }

    public function confirm(Booking|Cart|Order $model, Transaction $transaction, object $history): void
    {
        $first = $this->prepared($model);
        if (!$first) throw new \DomainException('Wallet withdrawal has no original pre-debit authority.');
        $contexts = DB::table('payment_collection_contexts')->where('funding_event_key',$first->funding_event_key)->orderBy('id')->get();
        $wallet = DB::table('wallets')->where('id',$first->wallet_id)->first();
        if (!$wallet || $history->wallet_uuid !== $wallet->uuid || $history->uuid !== $first->wallet_history_reference
            || (int) $history->transaction_id !== (int) $transaction->id || $history->status !== Transaction::STATUS_PAID
            || $history->type !== 'withdraw'
            || ExactMoney::units((string) $history->getRawOriginal('price'), (int) $first->money_scale) !== (int) $first->receipt_total_amount) {
            throw new \DomainException('Shared native Wallet receipt does not match original debit.');
        }
        foreach ($contexts as $context) {
            $a = DB::table('commerce_payment_allocations')->find($context->allocation_id);
            if ($a->payable_id !== null && (int) $a->payable_id === (int) $model->id
                && $a->payable_type === ($model instanceof Booking ? 'booking' : 'order')) {
                (new AllocationWriter)->linkTransaction((int) $transaction->id,(int) $a->id,(int) $context->id);
            }
        }
        (new AllocationWriter)->confirm($contexts->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'wallet-withdrawal:'.$history->uuid,(int) $first->receipt_total_amount);
        $transaction->refresh();
    }
}