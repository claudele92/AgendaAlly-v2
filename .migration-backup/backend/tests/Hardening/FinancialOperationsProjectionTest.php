<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PaymentAccounting\FinancialOperationsProjection;
use Illuminate\Support\Facades\DB;

final class FinancialOperationsActor extends User
{
    public bool $globalAdmin = false;
    public ?int $allowedShop = null;
    public function hasRole($roles, ?string $guard = null): bool { return $this->globalAdmin; }
    public function hasShopPermission(int $shopId, string $permissionKey): bool
    {
        return $permissionKey === 'payments.gateways.manage' && $shopId === $this->allowedShop;
    }
}

final class FinancialOperationsProjectionTest extends PaymentAccountingFixture
{
    private function actor(bool $admin = false, ?int $shop = null, ?int $country = null): FinancialOperationsActor
    {
        $actor=new FinancialOperationsActor;
        $actor->globalAdmin=$admin; $actor->allowedShop=$shop;
        $actor->setRelation('countryAdmin',$country === null ? null : (object)['country_id'=>$country]);
        return $actor;
    }

    private function transaction(int $id = 1): Transaction
    {
        return (new Transaction)->forceFill(['id'=>1,'payable_type'=>Booking::class,'payable_id'=>$id]);
    }

    public function test_legacy_customer_and_cross_shop_never_receive_financial_projection(): void
    {
        $service=new FinancialOperationsProjection;
        self::assertNull($service->forTransaction($this->transaction(),$this->actor(true)));
        $this->writer->commit($this->quote());
        self::assertNull($service->forTransaction($this->transaction(),null));
        self::assertNull($service->forTransaction($this->transaction(),$this->actor()));
        self::assertNull($service->forTransaction($this->transaction(),$this->actor(false,2)));
        self::assertNull($service->forTransaction($this->transaction(),$this->actor(true,null,2)));
        self::assertNotNull($service->forTransaction($this->transaction(),$this->actor(true,null,1)));
        self::assertNotNull($service->forTransaction($this->transaction(),$this->actor(false,1)));
    }

    public function test_confirmed_custody_and_liability_are_exact_read_only_and_not_external_settlement(): void
    {
        $id=$this->writer->commit($this->quote());
        $context=$this->writer->stage($id,$this->evidence('platform',10000));
        $this->writer->confirm([$context],'synthetic-evidence',10000);
        $before=json_encode([
            DB::table('commerce_payment_allocations')->get(),
            DB::table('payment_collection_contexts')->get(),
            DB::table('platform_fee_ledger_entries')->get(),
        ]);
        $result=(new FinancialOperationsProjection)->forTransaction($this->transaction(),$this->actor(true));
        self::assertTrue($result['payment_verified']);
        self::assertSame('9000',$result['balances']['vendor_payable']);
        self::assertSame('0',$result['balances']['commission_receivable']);
        self::assertSame('NOT_VERIFIED',$result['external_settlement_status']);
        self::assertSame('PAYABLE_OPEN_EXTERNAL_PAYOUT_NOT_READY',$result['payout_status']);
        self::assertSame('platform',$result['contributions'][0]['custody']);
        self::assertSame($before,json_encode([
            DB::table('commerce_payment_allocations')->get(),
            DB::table('payment_collection_contexts')->get(),
            DB::table('platform_fee_ledger_entries')->get(),
        ]));
    }

    public function test_cash_and_vendor_direct_do_not_display_platform_principal_payable(): void
    {
        foreach (['offline','vendor_direct'] as $index=>$mode) {
            $id=$this->writer->commit($this->quote($index+1));
            $context=$this->writer->stage($id,$this->evidence($mode,10000));
            $this->writer->confirm([$context],'synthetic-'.$mode,10000);
            $result=(new FinancialOperationsProjection)->forTransaction($this->transaction($index+1),$this->actor(false,1));
            self::assertSame('0',$result['balances']['vendor_payable']);
            self::assertSame('1000',$result['balances']['commission_receivable']);
            self::assertSame('OPEN',$result['commission_receivable_state']);
            self::assertSame('vendor',$result['contributions'][0]['custody']);
        }
    }
}