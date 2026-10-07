<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$cash=DB::table('transactions')->where('id',1503)->first();
$wallet=DB::table('transactions')->where('id',1504)->first();
$cashAllocation=DB::table('commerce_payment_allocations')->where('origin_type','booking')->where('origin_id',7)->first();
$walletAllocation=DB::table('commerce_payment_allocations')->where('origin_type','booking')->where('origin_id',8)->first();
$history=DB::table('wallet_histories')->where('transaction_id',1504)->get();
$checks=[
    'cashUncollected'=>$cash->status==='progress'&&$cashAllocation->state==='canceled'
        &&$cashAllocation->original_platform_amount===null&&$cashAllocation->original_vendor_direct_amount===null,
    'cashCancellationReleased'=>DB::table('bookings')->where('id',7)->value('status')==='canceled',
    'walletPaid'=>$wallet->status==='paid'&&$walletAllocation->state==='funded',
    'walletExactlyOneHistory'=>$history->count()===1&&$history[0]->type==='withdraw'&&(string)$history[0]->price==='100',
    'walletFrozenNativeUnits'=>(string)$walletAllocation->gross_amount==='10000'&&(string)$walletAllocation->original_platform_amount==='10000',
    'walletBookingStillActive'=>DB::table('bookings')->where('id',8)->value('status')!=='canceled',
];
$out=['scope'=>'Real production-mode staging Customer UI bookings7/8; native financial state, no writes',
    'checks'=>$checks,'status'=>!in_array(false,$checks,true)?'PASS':'FAIL'];
file_put_contents(getcwd().'/.local/staging-mvp/b1-ui-financial.json',json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);