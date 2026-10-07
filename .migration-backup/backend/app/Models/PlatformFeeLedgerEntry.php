<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\PlatformFeeLedgerEntry
 *
 * One row per paid booking transaction that carried a service_fee —
 * see TransactionObserver, which creates these automatically. Not a
 * money movement itself, just the record of what the platform is owed
 * and whether it has actually been collected yet.
 *
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property int $shop_id
 * @property string $entry_type
 * @property int $transaction_id
 * @property int|null $payment_id
 * @property int|null $currency_id
 * @property float $amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $collected_at
 * @property string|null $note
 * @property-read Booking|null $payable
 * @property-read Shop|null $shop
 * @property-read Transaction|null $transaction
 * @property-read Payment|null $payment
 * @property-read Currency|null $currency
 */
class PlatformFeeLedgerEntry extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'collected_at' => 'datetime',
        'amount'       => 'float',
        'effect_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $row): void {
            if ($row->allocation_id !== null || $row->getOriginal('allocation_id') !== null) {
                throw new \DomainException('Linked economic effects are immutable and server-owned.');
            }
        });
        static::deleting(function (self $row): void {
            if ($row->allocation_id !== null) {
                throw new \DomainException('Linked economic effects cannot be deleted.');
            }
        });
    }

    const STATUS_PENDING   = 'pending';
    const STATUS_COLLECTED = 'collected';
    const STATUS_WAIVED    = 'waived';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COLLECTED,
        self::STATUS_WAIVED,
    ];

    // The pre-existing row: what the platform is owed from the shop
    // (booking_service_fee), unrelated to collect_via_platform.
    const ENTRY_TYPE_FEE = 'fee';

    // What the platform owes the shop back, written only when the shop had
    // collect_via_platform=true at settlement time - see TransactionObserver.
    const ENTRY_TYPE_PAYABLE = 'payable';

    // A signed correction against a 'payable' row for a booking that was
    // later canceled/refunded - never a mutation of the original row.
    const ENTRY_TYPE_PAYABLE_ADJUSTMENT = 'payable_adjustment';

    const ENTRY_TYPES = [
        self::ENTRY_TYPE_FEE,
        self::ENTRY_TYPE_PAYABLE,
        self::ENTRY_TYPE_PAYABLE_ADJUSTMENT,
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForShop($query, int $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    /**
     * What the platform currently owes $shopId for payments it collected on
     * the shop's behalf: the sum of every 'payable' entry plus every signed
     * 'payable_adjustment' correction against them, minus whatever has
     * already been marked collected (paid out). Read-only aggregate - never
     * mutates history, matching the ledger's append-only design.
     */
    public static function payableBalanceForShop(int $shopId): float
    {
        return (float) self::query()
            ->where('shop_id', $shopId)
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('platform_fee_ledger_entries', 'allocation_id'),
                fn ($q) => $q->whereNull('allocation_id'))
            ->whereIn('entry_type', [self::ENTRY_TYPE_PAYABLE, self::ENTRY_TYPE_PAYABLE_ADJUSTMENT])
            ->where('status', '!=', self::STATUS_COLLECTED)
            ->sum('amount');
    }

    public function markCollected(?string $note = null): bool
    {
        return $this->update([
            'status'       => self::STATUS_COLLECTED,
            'collected_at' => now(),
            'note'         => $note ?? $this->note,
        ]);
    }

    public function markWaived(?string $note = null): bool
    {
        return $this->update([
            'status'       => self::STATUS_WAIVED,
            'collected_at' => now(),
            'note'         => $note ?? $this->note,
        ]);
    }
}
