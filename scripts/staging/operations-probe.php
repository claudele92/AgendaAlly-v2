<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Queue,Artisan};
$mode=$argv[1]??'status';
if($mode==='enqueue-selected') {
    $id=DB::table('selected_email_deliveries')->whereIn('state',['PENDING','BLOCKED'])->where('expires_at','>',now())->orderBy('id')->value('id');
    if(!$id) {
        $user=App\Models\User::findOrFail(104);
        $challenge=app(App\Services\AuthService\PasswordResetService::class)->issueEmailToken($user);
        $id=app(App\Services\EmailSettingService\SelectedEmailDelivery::class)->enqueue($user,'reset',$challenge);
    }else App\Jobs\SelectedAccountEmail::dispatch($id)->onConnection('database')->onQueue('mvp-notifications');
    echo json_encode(['deliveryId'=>$id,'pendingJobs'=>DB::table('jobs')->where('queue','mvp-notifications')->count()]).PHP_EOL;
}elseif($mode==='failed-job') {
    DB::table('staging_operational_probes')->updateOrInsert(['id'=>'queue-recovery'],['state'=>'FAIL_ONCE','payload'=>null]);
    AgendaAlly\Staging\RecoveryProbe::dispatch('queue-recovery')->onConnection('database')->onQueue('mvp-notifications');
    echo "Queued safe isolated operational failure probe.\n";
}elseif($mode==='reminder') {
    $booking=App\Models\Booking::whereIn('status',['new','booked'])->whereNotNull('user_id')->orderBy('id')->firstOrFail();
    $notification=App\Models\Notification::firstOrCreate(['type'=>App\Models\Notification::PUSH],['payload'=>[]]);
    DB::table('notification_user')->updateOrInsert(['user_id'=>$booking->user_id,'notification_id'=>$notification->id],
        ['active'=>1]);
    Illuminate\Support\Carbon::setTestNow(Illuminate\Support\Carbon::parse($booking->start_date)->subMinutes(20));
    $before=DB::table('push_notifications')->where('model_id',$booking->id)->where('type',App\Models\PushNotification::BOOKING_NOTIFICATION)->count();
    Artisan::call('mvp:background-tick');
    $once=DB::table('push_notifications')->where('model_id',$booking->id)->where('type',App\Models\PushNotification::BOOKING_NOTIFICATION)->count();
    Artisan::call('mvp:background-tick');
    $twice=DB::table('push_notifications')->where('model_id',$booking->id)->where('type',App\Models\PushNotification::BOOKING_NOTIFICATION)->count();
    echo json_encode(['bookingId'=>$booking->id,'catchUpBeforeStart'=>true,'before'=>$before,'once'=>$once,'twice'=>$twice,
        'deduplicated'=>$twice===$once,'created'=>$once>$before,
        'scope'=>'Logical-clock selected reminder catch-up within native future30-minute window, no provider or after-start semantic widening']).PHP_EOL;
}