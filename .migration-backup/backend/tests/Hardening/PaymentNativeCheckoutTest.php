<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\PaymentAccounting\AllocationBalances;
use App\Services\PaymentAccounting\CartAllocationFactory;
use App\Services\PaymentAccounting\NativePaymentAccounting;
use App\Services\PaymentAccounting\NativeQuoteFactory;
use App\Services\TransactionService\TransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PaymentNativeCheckoutTest extends PaymentNativeCheckoutFixture
{
    public static function cartMethods(): array { return [['cash',1],['wallet',2]]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('cartMethods')]
    public function test_full_native_cart_to_order_cash_and_wallet(string $tag,int $method): void
    {
        $this->cart();
        DB::table('products')->update(['digital'=>1]);
        $result=(new \App\Services\OrderService\OrderService)->create([
            'cart_id'=>1,'user_id'=>2,'payment_id'=>$method,'delivery_type'=>Order::DIGITAL,
        ]);
        self::assertTrue($result['status'],$result['message'] ?? '');
        $order=Order::firstOrFail();
        self::assertSame(100,(int)$order->total_price);
        self::assertSame(49,(int)DB::table('stocks')->value('quantity'));
        $a=DB::table('commerce_payment_allocations')->first();
        self::assertSame('cart',$a->origin_type);
        self::assertSame($order->id,(int)$a->payable_id);
        if ($tag==='cash') $order->transaction->update(['status'=>'paid']);
        self::assertSame('paid',$order->fresh()->transaction->status);
        self::assertSame($tag==='cash'?1000:0,(new AllocationBalances)->current((int)$a->id)['commission_receivable']);
        self::assertSame($tag==='cash'?0:9000,(new AllocationBalances)->current((int)$a->id)['vendor_payable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    protected function syntheticMtn(string $type,int $wallet=0,bool $direct=false): array
    {
        if ($type==='cart') {
            $model=$this->cart(); DB::table('products')->update(['digital'=>1]);
        } else {
            $model=$this->booking(); (new NativeQuoteFactory)->commitNew($model);
            $model->createTransaction(['price'=>100-$wallet,'payment_sys_id'=>3,'user_id'=>2,'status'=>'progress']);
        }
        $config=$direct?new \App\Models\ShopPayment:new \App\Models\PlatformPaymentConfig;
        // No credentials, no provider enabled outside this disposable database.
        $config->forceFill(['id'=>1,'payment_id'=>3,'country_id'=>1,'shop_id'=>1,'currency'=>'XAF',
            'updated_at'=>'2026-10-03 12:00:00']);
        $service=new NativeCheckoutSyntheticMtn($config);
        $key=$type==='cart'?'cart_id':'booking_id';
        $data=[$key=>1,'user_id'=>2,'payment_id'=>3,'delivery_type'=>Order::DIGITAL,'from_wallet_price'=>$wallet];
        $context=(new \App\Services\PaymentAccounting\ProviderContributionAdapter)->prepare(
            $key,$data,Payment::findOrFail(3),['collect_via_platform'=>!$direct],$service);
        if ($wallet>0) {
            (new \App\Services\PaymentService\BaseService)->walletPriceWithdraw(
                $model,array_merge($data,$context),\App\Models\User::with('wallet')->findOrFail(2)
            );
        }
        $data=array_merge($data,$context,['payment_id'=>3,'model_type'=>get_class($model),'model_id'=>1,
            'total_price'=>(100-$wallet)*100,'currency'=>'XAF','status'=>'progress',
            'mtn_config_fingerprint'=>$service->configFingerprint($config)]);
        $attempts=new \App\Services\PaymentService\MtnAttempt;
        $process=DB::transaction(fn()=>$attempts->reserve($data,3,$service->configFingerprint($config)));
        $reference=$process->id;
        $attempts->claim($reference); // Disposable fixture simulates already dispatched request.
        $process=\App\Models\PaymentProcess::findOrFail($reference);
        $service->result=['status'=>'SUCCESSFUL','externalId'=>$reference,'amount'=>(string)(100-$wallet),'currency'=>'XAF'];
        $controller=(new \ReflectionClass(\App\Http\Controllers\API\v1\Dashboard\Payment\MtnController::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($controller,'service'))->setValue($controller,$service);
        (new \ReflectionProperty(\App\Http\Controllers\Controller::class,'language'))->setValue($controller,'en');
        return [$controller,$service,$process];
    }

    public static function verifierCases(): array
    {
        return [['cart',0,false],['booking',0,false],['cart',0,true],['booking',0,true],
            ['cart',40,false],['booking',40,false],['cart',40,true],['booking',40,true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('verifierCases')]
    public function test_real_mtn_verifier_native_settlement_custody_mixed_funding_and_callback_replay(string $type,int $wallet,bool $direct): void
    {
        [$controller,$service,$process]=$this->syntheticMtn($type,$wallet,$direct);
        self::assertSame($wallet?1:0,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        $response=$controller->checkStatus($process->id)->getData(true);
        self::assertTrue($response['status'],json_encode([$response,$this->diagnosticLog->messages]));
        $a=DB::table('commerce_payment_allocations')->first();
        $balances=(new AllocationBalances)->current((int)$a->id);
        self::assertSame($direct && !$wallet?1000:0,$balances['commission_receivable']);
        self::assertSame($direct?($wallet?3000:0):9000,$balances['vendor_payable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame($wallet?960:1000,(int)DB::table('wallets')->value('price'));
        $before=DB::table('payment_collection_contexts')->get()->toJson();
        self::assertTrue($controller->checkStatus($process->id)->getData(true)['status']);
        self::assertSame($before,DB::table('payment_collection_contexts')->get()->toJson());
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame($type==='cart'?(100-$wallet):100-$wallet,(int)Transaction::where('payment_sys_id',3)->value('price'));
    }

    public static function wrongProofs(): array { return [['cart','USD'],['booking','USD'],['cart','wrong_amount'],['booking','wrong_amount']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongProofs')]
    public function test_native_mtn_wrong_evidence_creates_no_funding(string $type,string $wrong): void
    {
        [$controller,$service,$process]=$this->syntheticMtn($type);
        $service->result[$wrong==='USD'?'currency':'amount']=$wrong==='USD'?'USD':'99';
        self::assertFalse($controller->checkStatus($process->id)->getData(true)['status']);
        self::assertSame(0,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_native_cart_quote_retry_cancellation_changed_cart_and_retained_origin(): void
    {
        $cart=$this->cart();
        $factory=new CartAllocationFactory;
        $first=$factory->prepare($cart,[]);
        self::assertSame($first,$factory->prepare($cart,[]));
        $old=DB::table('commerce_payment_allocations')->find($first[1]);
        self::assertSame(10000,(int)$old->gross_amount);
        self::assertSame('XAF',$old->currency_code);
        $this->writer->cancel($first[1]);
        $canceled=DB::table('commerce_payment_allocations')->find($first[1]);
        DB::table('cart_detail_products')->update(['quantity'=>2]);
        $next=$factory->prepare($cart,[]);
        self::assertNotSame($first,$next);
        self::assertNotSame($old->checkout_key,DB::table('commerce_payment_allocations')->find($next[1])->checkout_key);
        self::assertEquals($canceled,DB::table('commerce_payment_allocations')->find($first[1]));
        self::assertSame(19000,(int)DB::table('commerce_payment_allocations')->find($next[1])->gross_amount);
    }

    public function test_verified_rejection_cancels_only_no_collection_cart_and_allows_changed_new_checkout(): void
    {
        [$controller,$service,$process]=$this->syntheticMtn('cart');
        $old=DB::table('commerce_payment_allocations')->first();
        $service->result['status']='FAILED';
        self::assertTrue($controller->checkStatus($process->id)->getData(true)['status']);
        self::assertSame('canceled',DB::table('commerce_payment_allocations')->find($old->id)->state);
        self::assertSame(0,DB::table('payment_collection_contexts')->where('state','confirmed')->count());
        DB::table('cart_detail_products')->update(['quantity'=>2,'price'=>180]);
        DB::table('carts')->update(['total_price'=>180]);
        $next=(new CartAllocationFactory)->prepare(\App\Models\Cart::firstOrFail(),[]);
        self::assertNotSame($old->checkout_key,DB::table('commerce_payment_allocations')->find($next[1])->checkout_key);
        self::assertSame('canceled',DB::table('commerce_payment_allocations')->find($old->id)->state);
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public static function ledgerFaults(): array { return [['before'],['after']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('ledgerFaults')]
    public function test_real_cart_wallet_failure_after_debit_rolls_back_every_financial_effect(string $when): void
    {
        $this->cart(); DB::table('products')->update(['digital'=>1]);
        DB::unprepared('CREATE TRIGGER injected_failure '.strtoupper($when).' INSERT ON platform_fee_ledger_entries BEGIN SELECT RAISE(ABORT, "synthetic ledger failure"); END');
        $result=(new \App\Services\OrderService\OrderService)->create([
            'cart_id'=>1,'user_id'=>2,'payment_id'=>2,'delivery_type'=>Order::DIGITAL,
        ]);
        self::assertFalse($result['status']);
        foreach (['orders','transactions','wallet_histories','commerce_payment_allocations','payment_collection_contexts','platform_fee_ledger_entries'] as $table) {
            self::assertSame(0,DB::table($table)->count(),$table);
        }
        self::assertSame(1000,(int)DB::table('wallets')->value('price'));
        self::assertSame(50,(int)DB::table('stocks')->value('quantity'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nativePayables')]
    public function test_wallet_currency_mismatch_is_blocked_before_any_debit(string $type): void
    {
        $model=$type==='order'?$this->order():$this->booking();
        (new NativeQuoteFactory)->commitNew($model);
        DB::table('wallets')->update(['currency_id'=>2]);
        try {
            if ($type==='order') (new TransactionService)->orderTransaction(1,['payment_sys_id'=>2]);
            else (new TransactionService)->bookingTransaction(1,['payment_sys_id'=>2]);
            self::fail();
        } catch (\DomainException $e) { self::assertStringContainsString('currency',$e->getMessage()); }
        self::assertSame(1000,(int)DB::table('wallets')->value('price'));
        self::assertSame(0,DB::table('wallet_histories')->count());
        self::assertSame(0,DB::table('payment_collection_contexts')->count());
    }

    public function test_verified_provider_effect_failure_retains_original_pending_intent_and_safe_retry(): void
    {
        [$controller,$service,$process]=$this->syntheticMtn('cart');
        $before=DB::table('payment_collection_contexts')->get()->toJson();
        DB::unprepared('CREATE TRIGGER injected_failure AFTER INSERT ON platform_fee_ledger_entries BEGIN SELECT RAISE(ABORT, "synthetic ledger failure"); END');
        self::assertFalse($controller->checkStatus($process->id)->getData(true)['status']);
        self::assertSame($before,DB::table('payment_collection_contexts')->get()->toJson());
        self::assertSame('progress',\App\Models\PaymentProcess::findOrFail($process->id)->data['status']);
        self::assertSame(0,DB::table('orders')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
        DB::unprepared('DROP TRIGGER injected_failure');
        self::assertTrue($controller->checkStatus($process->id)->getData(true)['status']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_live_cart_cannot_be_repriced_or_use_another_shops_stock(): void
    {
        $cart=$this->cart();
        (new CartAllocationFactory)->prepare($cart,[]);
        DB::table('cart_detail_products')->update(['quantity'=>2]);
        $this->expectException(\DomainException::class);
        (new CartAllocationFactory)->prepare($cart,[]);
    }

    public function test_same_gross_different_stock_is_not_same_frozen_checkout_quote(): void
    {
        $cart=$this->cart(); (new CartAllocationFactory)->prepare($cart,[]);
        $this->row('products',['id'=>2,'uuid'=>(string)Str::uuid(),'shop_id'=>1,'tax'=>0,'active'=>1,
            'status'=>'published','min_qty'=>1,'max_qty'=>100]);
        $this->row('stocks',['id'=>2,'product_id'=>2,'price'=>90,'quantity'=>50,'tax'=>0]);
        DB::table('cart_detail_products')->update(['stock_id'=>2]);
        $this->expectException(\DomainException::class);
        (new CartAllocationFactory)->prepare($cart->fresh(),[]);
    }

    public static function unknownCartTerms(): array
    {
        return [['coupon'],['tips'],['product_tax'],['shop_tax'],['percentage'],['fx'],['bonus']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unknownCartTerms')]
    public function test_unproven_product_terms_fail_without_partial_financial_records(string $term): void
    {
        $cart=$this->cart(); $data=[];
        match($term) {
            'coupon'=>$data=['coupon'=>'unproven'],
            'tips'=>$data=['tips'=>5],
            'product_tax'=>DB::table('products')->update(['tax'=>10]),
            'shop_tax'=>DB::table('shops')->update(['tax'=>10]),
            'percentage'=>DB::table('shops')->update(['percentage'=>5]),
            'fx'=>DB::table('carts')->update(['rate'=>2]),
            'bonus'=>DB::table('cart_detail_products')->update(['bonus'=>1]),
        };
        try { (new CartAllocationFactory)->prepare($cart->fresh(),$data); self::fail('Unproven terms accepted'); }
        catch (\DomainException $e) { self::assertNotSame('',$e->getMessage()); }
        self::assertSame(0,DB::table('commerce_payment_allocations')->count());
        self::assertSame(0,DB::table('payment_collection_contexts')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public static function nativePayables(): array { return [['order'],['booking']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('nativePayables')]
    public function test_native_cash_and_replacement_paid_replay_have_one_commission_no_platform_principal(string $type): void
    {
        $model=$type==='order'?$this->order():$this->booking();
        $id=(new NativeQuoteFactory)->commitNew($model);
        $first=$model->createTransaction(['price'=>100,'user_id'=>2,'payment_sys_id'=>1,'status'=>'paid']);
        $model->createTransaction(['price'=>100,'user_id'=>2,'payment_sys_id'=>1,'status'=>'paid']);
        $values=(new AllocationBalances)->current($id);
        self::assertSame(0,$values['vendor_payable']);
        self::assertSame(1000,$values['commission_receivable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(1,DB::table('payment_collection_contexts')->count());
        self::assertSame($id,(int)$first->allocation_id);
        self::assertSame('vendor',DB::table('payment_collection_contexts')->first()->custody_type);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nativePayables')]
    public function test_real_generic_native_wallet_debit_history_and_replay(string $type): void
    {
        $model=$type==='order'?$this->order():$this->booking();
        $id=(new NativeQuoteFactory)->commitNew($model);
        $service=new TransactionService;
        $result=$type==='order'?$service->orderTransaction(1,['payment_sys_id'=>2])
            :$service->bookingTransaction(1,['payment_sys_id'=>2]);
        self::assertTrue($result['status'],json_encode($result));
        self::assertSame(900,(int)DB::table('wallets')->value('price'));
        self::assertSame(1,DB::table('wallet_histories')->count());
        self::assertSame(9000,(new AllocationBalances)->current($id)['vendor_payable']);
        self::assertSame('platform',DB::table('payment_collection_contexts')->first()->custody_type);
        $result=$type==='order'?$service->orderTransaction(1,['payment_sys_id'=>2])
            :$service->bookingTransaction(1,['payment_sys_id'=>2]);
        self::assertTrue($result['status']);
        self::assertSame(900,(int)DB::table('wallets')->value('price'));
        self::assertSame(1,DB::table('wallet_histories')->count());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nativePayables')]
    public function test_paid_flag_without_electronic_or_wallet_proof_cannot_create_financial_meaning(string $type): void
    {
        $model=$type==='order'?$this->order():$this->booking();
        (new NativeQuoteFactory)->commitNew($model);
        foreach ([2,3] as $method) {
            try { $model->createTransaction(['price'=>100,'payment_sys_id'=>$method,'user_id'=>2,'status'=>'paid']); self::fail(); }
            catch (\DomainException $e) { self::assertStringContainsString('confirmed',$e->getMessage()); }
        }
        self::assertSame(0,DB::table('transactions')->count());
        self::assertSame(0,DB::table('platform_fee_ledger_entries')->count());
    }

    public function test_source_priced_booking_extra_remains_vendor_entitlement(): void
    {
        $model=$this->booking(['extra_price'=>20,'total_price'=>120]);
        $id=(new NativeQuoteFactory)->commitNew($model);
        $model->createTransaction(['price'=>120,'payment_sys_id'=>1,'user_id'=>2,'status'=>'paid']);
        $a=DB::table('commerce_payment_allocations')->find($id);
        self::assertSame(12000,(int)$a->gross_amount);
        self::assertSame(11000,(int)$a->vendor_entitlement_amount);
        self::assertSame(1000,(int)$a->commission_amount);
        self::assertSame('2000',json_decode($a->native_components,true)['native_extra_price_units']);
    }

    public function test_real_native_booking_selection_calculation_creation_and_wallet_payment(): void
    {
        $this->booking();
        DB::table('bookings')->delete();
        DB::table('service_masters')->update(['interval'=>60,'pause'=>0]);
        DB::table('users')->where('id',1)->update(['active'=>1]);
        DB::table('services')->update(['status'=>'accepted']);
        $this->row('invitations',['user_id'=>1,'shop_id'=>1,'role'=>'master','status'=>2]);
        $this->row('user_working_days',['user_id'=>1,'day'=>'thursday','from'=>'09:00','to'=>'17:00','disabled'=>0]);
        $request=\Illuminate\Http\Request::create('/api/v1/dashboard/user/bookings','POST');
        $this->app->instance('request',$request);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        $result=(new \App\Services\BookingService\BookingService)->create([
            'user_id'=>2,'payment_id'=>2,'shop_location_id'=>2,'data'=>[
                ['service_master_id'=>1,'start_date'=>'2026-10-15 10:00:00','shop_location_id'=>2],
            ],
        ]);
        self::assertTrue($result['status'],json_encode([$result['message'] ?? '',$this->diagnosticLog->messages]));
        $booking=Booking::firstOrFail();
        self::assertSame(1,(int)$booking->shop_id);
        self::assertSame(2,(int)$booking->shop_location_id);
        self::assertSame(1,(int)$booking->service_master_id);
        self::assertSame(100,(int)$booking->total_price);
        self::assertSame('paid',$booking->transaction->status);
        self::assertSame(900,(int)DB::table('wallets')->value('price'));
        $a=DB::table('commerce_payment_allocations')->first();
        self::assertSame(9000,(new AllocationBalances)->current((int)$a->id)['vendor_payable']);
        self::assertSame(2,DB::table('platform_fee_ledger_entries')->count());
    }

    public static function unknownBookingTerms(): array
    {
        return [['commission_fee',5],['coupon_price',5],['gift_cart_price',5],['rate',2]];
    }

    public function test_multiple_native_service_bookings_keep_distinct_origin_obligations_and_charge_each_original_fee_once(): void
    {
        $this->booking(); DB::table('bookings')->delete();
        DB::table('service_masters')->update(['interval'=>60,'pause'=>0]);
        DB::table('users')->where('id',1)->update(['active'=>1]);
        DB::table('services')->update(['status'=>'accepted']);
        $this->row('services',['id'=>2,'category_id'=>1,'shop_id'=>1,'active'=>1,'status'=>'accepted']);
        $this->row('service_masters',['id'=>2,'service_id'=>2,'master_id'=>1,'shop_id'=>1,'price'=>90,
            'discount'=>0,'commission_fee'=>0,'active'=>1,'interval'=>60,'pause'=>0]);
        $this->row('invitations',['user_id'=>1,'shop_id'=>1,'role'=>'master','status'=>2]);
        $this->row('user_working_days',['user_id'=>1,'day'=>'thursday','from'=>'09:00','to'=>'17:00','disabled'=>0]);
        $this->app->instance('request',\Illuminate\Http\Request::create('/api/v1/dashboard/user/bookings','POST'));
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        $result=(new \App\Services\BookingService\BookingService)->create([
            'user_id'=>2,'payment_id'=>2,'shop_location_id'=>2,'data'=>[
                ['service_master_id'=>1,'start_date'=>'2026-10-15 10:00:00','shop_location_id'=>2],
                ['service_master_id'=>2,'start_date'=>'2026-10-15 12:00:00','shop_location_id'=>2],
            ],
        ]);
        self::assertTrue($result['status'],$result['message']??'');
        self::assertSame(2,DB::table('bookings')->count());
        self::assertSame(2,DB::table('commerce_payment_allocations')->count());
        self::assertSame(1,DB::table('commerce_payment_allocations')->distinct()->count('checkout_key'));
        self::assertSame(2,DB::table('commerce_payment_allocations')->distinct()->count('origin_id'));
        self::assertSame(4,DB::table('platform_fee_ledger_entries')->count());
        self::assertSame(2000,(int)DB::table('commerce_payment_allocations')->sum('commission_amount'));
        self::assertSame(2,DB::table('wallet_histories')->count());
        self::assertSame(800,(int)DB::table('wallets')->value('price'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unknownBookingTerms')]
    public function test_unproven_booking_terms_fail_closed(string $field,int $amount): void
    {
        $model=$this->booking([$field=>$amount]);
        try { (new NativeQuoteFactory)->commitNew($model); self::fail(); }
        catch (\DomainException $e) { self::assertNotSame('',$e->getMessage()); }
        self::assertSame(0,DB::table('commerce_payment_allocations')->count());
    }

    public function test_pending_native_quote_is_immutable_and_booking_lifecycle_is_not_payment(): void
    {
        $model=$this->booking(); $id=(new NativeQuoteFactory)->commitNew($model);
        $model->createTransaction(['price'=>100,'payment_sys_id'=>3,'status'=>'progress']);
        $model->update(['status'=>'ended']);
        self::assertSame('progress',$model->fresh()->transaction->status);
        self::assertNull(DB::table('commerce_payment_allocations')->find($id)->finalized_at);
        try { $model->update(['total_price'=>150]); self::fail(); }
        catch (\DomainException $e) { self::assertStringContainsString('quote',$e->getMessage()); }
        self::assertSame(100,(int)$model->fresh()->total_price);
    }

    public function test_booking_canonical_cash_cannot_enter_legacy_partner_payout_bookkeeping(): void
    {
        $model=$this->booking(); (new NativeQuoteFactory)->commitNew($model);
        $model->createTransaction(['price'=>100,'payment_sys_id'=>1,'user_id'=>2,'status'=>'paid']);
        try {
            (new \App\Services\PaymentToPartnerService\PaymentToPartnerService)->bookingStoreMany(['payment_id'=>1,'data'=>[1]]);
            self::fail();
        } catch (\DomainException $e) { self::assertStringContainsString('original-liability',$e->getMessage()); }
        self::assertSame(0,DB::table('payment_to_partners')->count());
        self::assertSame(1000,(int)DB::table('wallets')->value('price'));
    }

    public function test_multi_shop_native_cart_roots_cash_and_bound_order_identity_are_isolated(): void
    {
        $ids=(new CartAllocationFactory)->prepare($this->cart(2),[]);
        self::assertCount(2,$ids);
        foreach ($ids as $shop=>$id) {
            $this->row('orders',['id'=>$shop,'shop_id'=>$shop,'cart_id'=>1,'user_id'=>2,'currency_id'=>1,'rate'=>1,'total_price'=>95,'service_fee'=>5]);
            $this->writer->bind($id,'order',$shop);
            Order::findOrFail($shop)->createTransaction(['price'=>95,'payment_sys_id'=>1,'user_id'=>2,'status'=>'paid']);
            self::assertSame(500,(new AllocationBalances)->current($id)['commission_receivable']);
            self::assertSame(0,(new AllocationBalances)->current($id)['vendor_payable']);
        }
        self::assertSame(2,DB::table('payment_collection_contexts')->distinct()->count('allocation_id'));
        self::assertSame(4,DB::table('platform_fee_ledger_entries')->count());
    }
}