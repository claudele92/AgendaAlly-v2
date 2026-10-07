<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Order;
use App\Models\PlatformFeeLedgerEntry;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Observers\TransactionObserver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class TransactionObserverHardeningTest extends IsolatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('platform_fee_ledger_entries', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('payable_type');
            $table->unsignedInteger('payable_id');
            $table->unsignedInteger('shop_id');
            $table->string('entry_type');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('payment_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status');
            $table->timestamps();
            $table->unique(['transaction_id', 'entry_type']);
        });
    }

    private function transaction(ModelPayable $payable, string $status, string $originalStatus): Transaction
    {
        $transaction = new Transaction();
        $transaction->setRawAttributes([
            'id' => (int) $payable->model->id + 100,
            'payable_type' => $payable->type,
            'payable_id' => $payable->model->id,
            'payment_sys_id' => 6,
            'status' => $originalStatus,
        ], true);
        $transaction->setRelation('payable', $payable->model);
        $transaction->status = $status;

        return $transaction;
    }

    public function test_created_paid_booking_records_frozen_fee_and_payable_once_despite_live_shop_toggle(): void
    {
        $booking = (new Booking())->forceFill([
            'id' => 15,
            'shop_id' => 3,
            'currency_id' => 2,
            'total_price' => 13.50,
            'service_fee' => 4.25,
            'commission_fee' => 0,
            'coupon_price' => 0,
            'collect_via_platform' => true,
        ]);
        $booking->setRelation('shop', (new Shop())->forceFill([
            'id' => 3,
            'collect_via_platform' => false,
        ]));
        $transaction = $this->transaction(new ModelPayable(Booking::class, $booking), Transaction::STATUS_PAID, Transaction::STATUS_PAID);
        $observer = new TransactionObserver();

        $observer->created($transaction);
        $observer->created($transaction);
        // A duplicate/out-of-order update of a row created already-paid
        // must not make a second fee or payable row.
        $transaction->setRawAttributes([
            'id' => 115,
            'payable_type' => Booking::class,
            'payable_id' => 15,
            'payment_sys_id' => 6,
            'status' => Transaction::STATUS_PAID,
        ], true);
        $transaction->status = Transaction::STATUS_REFUND;
        $observer->updated($transaction);

        self::assertSame(2, PlatformFeeLedgerEntry::query()->count());
        self::assertSame(1, PlatformFeeLedgerEntry::query()->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_FEE)->count());
        self::assertSame(1, PlatformFeeLedgerEntry::query()->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)->count());
        self::assertSame(9.25, (float) PlatformFeeLedgerEntry::query()->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)->value('amount'));
    }

    public function test_progress_to_paid_and_later_replay_create_only_one_fee_and_payable_and_orders_never_create_booking_payables(): void
    {
        $booking = (new Booking())->forceFill([
            'id' => 15,
            'shop_id' => 3,
            'currency_id' => 2,
            'total_price' => 13.50,
            'service_fee' => 4.25,
            'commission_fee' => 0,
            'coupon_price' => 0,
            'collect_via_platform' => true,
        ]);
        $observer = new TransactionObserver();
        $transaction = $this->transaction(new ModelPayable(Booking::class, $booking), Transaction::STATUS_PAID, Transaction::STATUS_PROGRESS);
        $observer->updated($transaction);
        $observer->updated($transaction);

        $transaction->setRawAttributes([
            'id' => 115,
            'payable_type' => Booking::class,
            'payable_id' => 15,
            'payment_sys_id' => 6,
            'status' => Transaction::STATUS_PAID,
        ], true);
        $transaction->status = Transaction::STATUS_REFUND;
        $observer->updated($transaction);
        $transaction->setRawAttributes([
            'id' => 115,
            'payable_type' => Booking::class,
            'payable_id' => 15,
            'payment_sys_id' => 6,
            'status' => Transaction::STATUS_REFUND,
        ], true);
        $transaction->status = Transaction::STATUS_PAID;
        $observer->updated($transaction);

        $order = (new Order())->forceFill([
            'id' => 16,
            'shop_id' => 3,
            'currency_id' => 2,
            'service_fee' => 2.00,
        ]);
        $observer->created($this->transaction(new ModelPayable(Order::class, $order), Transaction::STATUS_PAID, Transaction::STATUS_PAID));

        $wallet = (new Wallet())->forceFill(['id' => 17]);
        $observer->created($this->transaction(new ModelPayable(Wallet::class, $wallet), Transaction::STATUS_PAID, Transaction::STATUS_PAID));

        self::assertSame(3, PlatformFeeLedgerEntry::query()->count());
        self::assertSame(2, PlatformFeeLedgerEntry::query()->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_FEE)->count());
        self::assertSame(1, PlatformFeeLedgerEntry::query()->where('entry_type', PlatformFeeLedgerEntry::ENTRY_TYPE_PAYABLE)->count());
        self::assertSame(0, PlatformFeeLedgerEntry::query()->where('payable_type', Wallet::class)->count());
    }
}

/**
 * Keeps fixture model/type paired without querying payable relations.
 */
final class ModelPayable
{
    public function __construct(public string $type, public \Illuminate\Database\Eloquent\Model $model)
    {
    }
}