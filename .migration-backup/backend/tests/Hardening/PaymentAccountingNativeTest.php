<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProcess;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Observers\TransactionObserver;
use App\Services\PaymentAccounting\AllocationBalances;
use App\Services\PaymentAccounting\NativePaymentAccounting;
use App\Services\PaymentAccounting\WalletContributionAdapter;
use App\Services\PaymentService\BaseService;
use App\Services\TransactionService\TransactionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class PaymentAccountingNativeTest extends PaymentAccountingFixture
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
    }
    public function test_native_cash_paid_creation_and_stale_replay_write_canonical_effects_only(): void
    {
        DB::table('payments')->where('id',1)->update(['tag'=>'cash']);
        DB::table('bookings')->insert(['id'=>1,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100]);
        $id = $this->writer->commit($this->quote());
        Transaction::observe(TransactionObserver::class);
        $booking = Booking::findOrFail(1);
        $t = $booking->createTransaction(['price'=>100,'payment_sys_id'=>1,'status'=>'paid','user_id'=>2]);
        $booking->createTransaction(['price'=>100,'payment_sys_id'=>1,'status'=>'paid','user_id'=>2]);
        $value = (new AllocationBalances)->current($id);
        self::assertSame(0,$value['vendor_payable']);
        self::assertSame(1000,$value['commission_receivable']);
        self::assertSame($id,(int)$t->allocation_id);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->whereNotNull('transaction_id')->count());
    }

    public function test_native_two_wallet_legs_keep_distinct_transactions_receipts_and_one_commission(): void
    {
        DB::table('payments')->where('id',1)->update(['tag'=>'wallet']);
        DB::table('bookings')->insert(['id'=>1,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100]);
        Schema::create('wallets',function (Blueprint $t): void {
            $t->id(); $t->string('uuid'); $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('currency_id')->default(1); $t->decimal('price',22,2);
        });
        Schema::create('wallet_histories',function (Blueprint $t): void {
            $t->id(); $t->string('uuid'); $t->string('wallet_uuid'); $t->unsignedBigInteger('transaction_id');
            $t->string('type'); $t->decimal('price',22,2); $t->text('note'); $t->string('status');
            $t->unsignedBigInteger('created_by'); $t->timestamps();
        });
        Schema::create('translations',function (Blueprint $t): void {
            $t->id(); $t->string('key'); $t->text('value');
        });
        DB::table('wallets')->insert(['id'=>1,'uuid'=>(string)Str::uuid(),'user_id'=>2,'price'=>200]);
        $id = $this->writer->commit($this->quote());
        $book = Booking::findOrFail(1);
        $user = (new User)->forceFill(['id'=>2]);
        $user->setRelation('wallet',Wallet::findOrFail(1));
        $service = new class extends TransactionService {
            public function __construct() { $this->language='en'; }
        };
        DB::transaction(function () use ($book,$user,$service): void {
            (new NativePaymentAccounting)->prepare($book,Payment::findOrFail(1),'40','wallet_contribution');
            DB::table('wallets')->where('id',1)->decrement('price',40);
            $first = $book->createTransaction(['price'=>40,'payment_sys_id'=>1,'status'=>'progress','user_id'=>2]);
            $service->walletHistoryAdd($user,$first,$book,'Booking','withdraw');
            (new NativePaymentAccounting)->prepare($book,Payment::findOrFail(1),'60','selected_method');
            DB::table('wallets')->where('id',1)->decrement('price',60);
            $second = $book->createTransaction(['price'=>60,'payment_sys_id'=>1,'status'=>'progress','user_id'=>2]);
            self::assertNotSame($first->id,$second->id);
            $service->walletHistoryAdd($user,$second,$book,'Booking','withdraw');
        });
        self::assertSame(100,(int)DB::table('wallets')->find(1)->price);
        self::assertSame(2,DB::table('wallet_histories')->count());
        self::assertSame(2,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_synthetic_provider_callback_uses_existing_verification_boundary_and_replays_once(): void
    {
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        DB::table('payments')->where('id',1)->update(['tag'=>'synthetic_provider']);
        DB::table('bookings')->insert(['id'=>1,'shop_id'=>1,'user_id'=>2,'currency_id'=>1,'total_price'=>100]);
        Schema::create('payment_process',function (Blueprint $t): void {
            $t->string('id')->primary(); $t->string('model_type'); $t->unsignedBigInteger('model_id');
            $t->unsignedBigInteger('user_id'); $t->json('data'); $t->timestamps();
        });
        $id = $this->writer->commit($this->quote());
        $context = $this->writer->stage($id,$this->evidence('platform',10000));
        $this->writer->pending([$context]);
        $reference=(string)Str::uuid();
        $process = new PaymentProcess;
        $process->setTable('payment_process');
        $process->forceFill(['id'=>$reference,'model_type'=>Booking::class,'model_id'=>1,'user_id'=>2,'data'=>[
            'payment_id'=>1,'model_type'=>Booking::class,'model_id'=>1,'total_price'=>10000,'currency'=>'USD',
            'status'=>'progress','accounting_context_ids'=>[$context],
        ]])->save();
        $booking = Booking::findOrFail(1);
        $booking->createTransaction(['price'=>100,'payment_sys_id'=>1,'status'=>'progress','user_id'=>2]);
        $service = new class extends BaseService {
            public function __construct() { $this->language='en'; $this->currency=1; }
        };
        $proof=['authenticated'=>true,'merchant_verified'=>true,'reference'=>$reference,'payment_id'=>1,
            'model_type'=>Booking::class,'model_id'=>1,'amount_minor'=>10000,'currency'=>'USD'];
        self::assertFalse($service->afterHook($reference,'paid',null,array_replace($proof,['authenticated'=>false]))['status']);
        self::assertNull(DB::table('commerce_payment_allocations')->find($id)->finalized_at);
        self::assertTrue($service->afterHook($reference,'paid',null,$proof)['status']);
        self::assertTrue($service->afterHook($reference,'paid',null,$proof)['status']);
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_legacy_native_payment_cannot_invent_an_allocation(): void
    {
        DB::table('payments')->where('id',1)->update(['tag'=>'cash']);
        DB::table('bookings')->insert(['id'=>1]);
        $this->expectException(\DomainException::class);
        Booking::findOrFail(1)->createTransaction(['price'=>100,'payment_sys_id'=>1,'status'=>'paid','user_id'=>2]);
    }
}