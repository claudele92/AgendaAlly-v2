<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Native commerce/payment edits cannot supersede original canonical proof. */
final class NativeFinancialBoundary
{
    public function saving(Model $model): void
    {
        if (!NativePaymentAccounting::installed()) return;
        $transaction = $model instanceof Transaction;
        $type = $transaction ? match ($model->payable_type) {
            Booking::class => 'booking', Order::class => 'order', default => null,
        } : ($model instanceof Booking ? 'booking' : 'order');
        if ($type === null) return;
        $allocation = DB::table('commerce_payment_allocations')->where('payable_type', $type)
            ->where('payable_id', $transaction ? $model->payable_id : $model->id)
            ->where('purpose', 'base')->where('obligation_key', 'base')->first();
        // No classification or financial backfill of historical records.
        if (!$allocation) return;
        if (!$transaction) {
            if ($model->isDirty('status') && in_array($model->status, [Booking::STATUS_ENDED, Order::STATUS_DELIVERED],true)) {
                $this->assertNoUnprovenCashback($model);
            }
            foreach (['shop_id','user_id','local_client_id','currency_id','rate','total_price','price',
                'service_fee','commission_fee','coupon_price','gift_cart_price','extra_price',
                'discount','total_discount','total_tax','delivery_fee','tips','collect_via_platform'] as $field) {
                if ($model->exists && $model->isDirty($field)) {
                    throw new \DomainException('Committed native quote cannot be edited: '.$field);
                }
            }
            return;
        }
        if ($model->exists && $model->getRawOriginal('collection_context_id') !== null) {
            foreach (['payable_type','payable_id','user_id','payment_sys_id','currency_id','price','refund_time'] as $field) {
                if ($model->isDirty($field)) throw new \DomainException('Original linked payment evidence is immutable: '.$field);
            }
            if ($model->getRawOriginal('status') === Transaction::STATUS_PAID && $model->isDirty('status')) {
                throw new \DomainException('A paid flag cannot reverse canonical funding; paired effects are required.');
            }
        }
        if ($model->status !== Transaction::STATUS_PAID) return;
        $payment = Payment::findOrFail($model->payment_sys_id);
        if ($payment->tag === Payment::TAG_CASH) return; // Existing authorized Cash acceptance calls the writer.
        $contexts = DB::table('payment_collection_contexts')->where('allocation_id', $allocation->id)
            ->where('payment_id', $payment->id)->where('state', 'confirmed')
            ->where('amount', ExactMoney::units((string) $model->price, (int) $allocation->money_scale));
        if ($model->collection_context_id !== null) $contexts->where('id', $model->collection_context_id);
        if (!$contexts->exists()) {
            throw new \DomainException('Native paid state requires the original confirmed debit/provider contribution.');
        }
    }

    public function assertNoUnprovenCashback(Booking|Order $model): void
    {
        if (!NativePaymentAccounting::installed() || !\Illuminate\Support\Facades\Schema::hasTable('points')) return;
        $type=$model instanceof Booking?'booking':'order';
        if (!DB::table('commerce_payment_allocations')->where('payable_type',$type)->where('payable_id',$model->id)->exists()) return;
        $amount=$model instanceof Booking?\App\Models\Point::getBookingActualPoint($model->total_price)
            :\App\Models\Point::getActualPoint($model->total_price);
        if ($amount>0) throw new \DomainException('Unproven cashback cannot change a frozen canonical payment economic contract.');
    }
}