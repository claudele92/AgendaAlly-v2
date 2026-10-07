<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read Transaction|null $transaction
 * @property-read Collection|Transaction[] $transactions
 * @property-read int $transactions_count
 * */
trait Payable
{
    public function createTransaction(array $data): Model|Transaction
    {
        $status = data_get($data, 'status', Transaction::STATUS_PROGRESS);

        $data = [
            'price'                 => data_get($data, 'price'),
            'user_id'               => data_get($data, 'user_id', auth('sanctum')->id()),
            'payment_sys_id'        => data_get($data, 'payment_sys_id'),
            'payment_trx_id'        => data_get($data, 'payment_trx_id'),
            'note'                  => data_get($data, 'note', ''),
            'perform_time'          => data_get($data, 'perform_time', now()),
            'status_description'    => data_get($data, 'status_description', 'Transaction in progress'),
            'status'                => $status,
        ];

        if ($status == Transaction::STATUS_CANCELED) {
            $data['refund_time'] = now();
        }

        $native = $this instanceof \App\Models\Booking || $this instanceof \App\Models\Order;
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $status, $native) {
        $accounting = new \App\Services\PaymentAccounting\NativePaymentAccounting;
        $cashContext = null;
        if ($native && $accounting::installed()) {
            $allocation = $accounting->allocation($this);
            $method = \App\Models\Payment::find(data_get($data, 'payment_sys_id'));
            if ($status === Transaction::STATUS_PAID && $method?->tag === 'cash') {
                $cashContext = $accounting->prepare($this, $method, (string) data_get($data, 'price'));
            }
        }
        $existing = $this->transactions()->where('payment_sys_id', $data['payment_sys_id'])->first();
        $separateFundingLeg = false;
        if ($native && $accounting::installed() && $existing?->collection_context_id !== null) {
            $separateFundingLeg = \Illuminate\Support\Facades\DB::table('payment_collection_contexts')
                ->where('allocation_id', $allocation->id)->where('payment_id', $data['payment_sys_id'])
                ->whereIn('state', ['committed','pending'])
                ->where('id', '!=', $existing->collection_context_id)->exists();
        }
        $transaction = $separateFundingLeg ? $this->transactions()->create($data) : $this->transactions()
            ->whereNull('parent_id')
            ->updateOrCreate([
                'payable_id'    => $this->id,
                'payable_type'  => get_class($this),
                'payment_sys_id' => data_get($data, 'payment_sys_id'),
            ], $data);
        if ($cashContext !== null) {
            $accounting->cash($this, $transaction);
        } elseif ($native && $accounting::installed()) {
            (new \App\Services\PaymentAccounting\AllocationWriter)->linkTransaction(
                (int) $transaction->id, (int) $allocation->id, null
            );
            $transaction->refresh();
        }
        return $transaction;
        }, 3);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable')->whereNull('parent_id');
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, 'payable')->whereNull('parent_id');
    }
}
