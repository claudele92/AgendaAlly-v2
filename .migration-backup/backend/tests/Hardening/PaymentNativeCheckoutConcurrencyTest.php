<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Services\PaymentAccounting\CartAllocationFactory;
use App\Services\PaymentAccounting\NativeQuoteFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

final class PaymentNativeCheckoutConcurrencyTest extends PaymentNativeCheckoutFixture
{
    private array $files=[];
    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->files as $file) @unlink($file);
    }
    public function test_two_connections_native_same_cart_checkout_retry(): void
    {
        $this->cart();
        $operation=fn () => (new CartAllocationFactory)->prepare(\App\Models\Cart::findOrFail(1),[]);
        $this->contend($operation,'insert or ignore into "commerce_payment_allocations"');
        self::assertSame(1,DB::table('commerce_payment_allocations')->count());
    }

    public function test_two_connections_native_cash_contribution_paid_finalization_and_fee(): void
    {
        (new NativeQuoteFactory)->commitNew($this->booking());
        $operation=fn () => Booking::findOrFail(1)->createTransaction([
            'payment_sys_id'=>1,'price'=>100,'user_id'=>2,'status'=>'paid',
        ]);
        $this->contend($operation,'update "commerce_payment_allocations"');
        self::assertSame(1,DB::table('commerce_payment_allocations')->count());
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(1,DB::table('transactions')->count());
    }

    private function contend(callable $operation,string $prefix): void
    {
        $file=tempnam(sys_get_temp_dir(),'native-payment-contention-');
        $this->files[]=$file;
        $manager=$this->database->getDatabaseManager();
        $manager->connection()->getPdo()->exec('VACUUM INTO '.$manager->connection()->getPdo()->quote($file));
        $manager->purge('hardening');
        foreach (['hardening','contender'] as $name) {
            $this->database->addConnection(['driver'=>'sqlite','database'=>$file,'prefix'=>''],$name);
            $manager->connection($name)->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager);
            $manager->connection($name)->getPdo()->exec('PRAGMA busy_timeout=1');
        }
        $attempted=false; $failure=null;
        try {
            DB::listen(function (QueryExecuted $query) use ($manager,$operation,$prefix,&$attempted,&$failure): void {
                if ($attempted || !str_starts_with($query->sql,$prefix)) return;
                $attempted=true;
                $manager->setDefaultConnection('contender');
                try { $operation(); } catch (\Throwable $e) { $failure=$e; }
                finally { $manager->setDefaultConnection('hardening'); }
            });
            $operation();
            self::assertTrue($attempted);
            self::assertInstanceOf(\Illuminate\Database\QueryException::class,$failure);
            self::assertStringContainsString('locked',$failure->getMessage());
            $manager->setDefaultConnection('contender');
            $operation();
            $manager->setDefaultConnection('hardening');
        } finally {
            $manager->setDefaultConnection('hardening');
            $manager->disconnect('contender'); $manager->disconnect('hardening');
        }
    }
}