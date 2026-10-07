<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\User;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;

final class WalletSpendBoundaryTest extends WalletTransferFixture
{
    public static function spends(): array
    {
        return [[70,70,30,1],[60,60,40,1],[70,40,30,1],
            [40,50,10,2],[100,1,0,1],[50,50,0,2]];
    }

    #[DataProvider('spends')]
    public function test_cached_wallet_never_reauthorizes_spent_value(int $a,int $b,int $remaining,int $count): void
    {
        $user=User::with('wallet')->findOrFail(1);
        foreach ([$a,$b] as $amount) {
            try {
                $this->service->create(['type'=>'withdraw','price'=>$amount,'user'=>$user,'status'=>WalletHistory::PROCESSED]);
            } catch (\DomainException $e) {
                self::assertSame(109,$e->getCode());
            }
        }
        self::assertEquals($remaining,DB::table('wallets')->where('id',1)->value('price'));
        self::assertSame($count,DB::table('wallet_histories')->count());
        self::assertSame($count,DB::table('transactions')->count());
        self::assertSame(0,DB::connection()->transactionLevel());
    }

    public function test_downstream_failure_restores_debit_history_and_transaction(): void
    {
        $before=$this->fingerprint();
        try {
            DB::transaction(function (): void {
                $this->service->create(['type'=>'withdraw','price'=>70,'user'=>User::findOrFail(1)]);
                throw new \RuntimeException('synthetic downstream failure');
            });
        } catch (\RuntimeException $e) {self::assertSame('synthetic downstream failure',$e->getMessage());}
        self::assertSame($before,$this->fingerprint());
        self::assertSame(0,DB::connection()->transactionLevel());
    }
}