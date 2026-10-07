<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Crypt,DB};
if(($argv[1]??'')==='seed') {
    $text='Isolated staging recovery/decryption sentinel; not a user challenge';
    DB::table('staging_operational_probes')->updateOrInsert(['id'=>'encrypted-recovery'],
        ['state'=>'READY','payload'=>Crypt::encryptString($text)]);
}
$row=DB::table('staging_operational_probes')->find('encrypted-recovery');
$out=[
    'environment'=>$app->environment(),'keyFingerprint'=>hash('sha256',(string)config('app.key')),
    'encryptedDataDecrypts'=>$row && Crypt::decryptString($row->payload)==='Isolated staging recovery/decryption sentinel; not a user challenge',
    'bookingCount'=>DB::table('bookings')->count(),
    'wallets'=>DB::table('wallets')->orderBy('user_id')->get(['user_id','price','currency_id'])->toArray(),
    'retainedOperations'=>DB::table('payment_financial_operations')->whereIn('state',['UNKNOWN','CANCELED'])->orderBy('id')->get(['id','state','amount_units'])->toArray(),
];
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;