<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
require __DIR__.'/db-evidence.php';
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Invitation;
use App\Models\ServiceMaster;
use App\Models\Booking;
$app=stagingApplication();$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$kernel->bootstrap();
$app->instance('request',Illuminate\Http\Request::create('https://localhost:8444/api/v1/dashboard/seller/bookings'));
$out=['scope'=>'Real isolated native HTTP kernel; synthetic audit fixtures in a rolled-back transaction; no provider, wallet, payout, refund or protected writes','checks'=>[],'observations'=>[]];
function requestAudit(string $method,string $path,array $body,string $token):array {
    global $app,$kernel;
    Illuminate\Support\Facades\Auth::forgetGuards();$app['session']->driver()->flush();
    foreach($app['router']->getRoutes()as$route)$route->flushController();
    $request=Illuminate\Http\Request::create('https://localhost:8444/api/v1/'.$path,$method,$body,[],[],[
        'HTTP_ACCEPT'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.$token,'HTTPS'=>'on','REMOTE_ADDR'=>'127.0.0.1']);
    $app->instance('request',$request);$response=$kernel->handle($request);
    $data=json_decode($response->getContent(),true);$kernel->terminate($request,$response);
    return [$response->getStatusCode(),$data];
}
function checkAudit(string $key,bool $pass,array $evidence=[]):void {
    global $out;$out['checks'][$key]=$pass;if($evidence)$out['observations'][$key]=$evidence;
}
$before=stagingFingerprints(stagingRootPdo());
DB::beginTransaction();
try {
    $foreign=User::findOrFail(105)->replicate();
    $foreign->firstname='Audit B-only';$foreign->lastname='Specialist';$foreign->email='b-only@audit.invalid';$foreign->phone=null;
    $foreign->save();$foreign->assignRole('master');
    $invite=Invitation::findOrFail(103)->replicate();$invite->user_id=$foreign->id;$invite->save();
    $assignment=ServiceMaster::findOrFail(103)->replicate();$assignment->master_id=$foreign->id;$assignment->save();
    $foreignBooking=Booking::findOrFail(8)->replicate();$foreignBooking->shop_id=102;
    $foreignBooking->master_id=$foreign->id;$foreignBooking->service_master_id=$assignment->id;
    $foreignBooking->parent_id=null;$foreignBooking->start_date='2032-11-12 09:00:00';$foreignBooking->end_date='2032-11-12 10:00:00';
    $foreignBooking->save();
    \Spatie\Permission\Models\Role::findOrCreate('moderator','web');
    $staff=User::findOrFail(105)->replicate();$staff->firstname='Audit';$staff->lastname='Shop A staff';
    $staff->email='a-staff@audit.invalid';$staff->phone=null;$staff->save();$staff->assignRole('moderator');
    $role=DB::table('shop_roles')->insertGetId(['shop_id'=>101,'name'=>'Audit booking staff']);
    $permissionIds=[];
    foreach(['bookings.view','bookings.manage','bookings.status','bookings.view_all_branches']as$key)
        $permissionIds[]=\App\Models\ShopPermission::firstOrCreate(['key'=>$key],['group'=>'bookings','label'=>'Audit fixture'])->id;
    foreach($permissionIds as$permission)
        DB::table('shop_role_permissions')->insert(['shop_role_id'=>$role,'shop_permission_id'=>$permission]);
    $staffInvite=Invitation::findOrFail(101)->replicate();$staffInvite->user_id=$staff->id;$staffInvite->role='moderator';
    $staffInvite->shop_role_id=$role;$staffInvite->save();
    $tokens=[103=>User::findOrFail(103)->createToken('rolled-back-audit')->plainTextToken,
        106=>User::findOrFail(106)->createToken('rolled-back-audit')->plainTextToken,
        'staff'=>$staff->createToken('rolled-back-audit')->plainTextToken];
    [$code,$body]=requestAudit('GET','rest/masters',['perPage'=>100],$tokens[103]);
    checkAudit('authenticated_vendor_public_fallback_is_scoped',$code===200&&!in_array($foreign->id,array_column($body['data']??[],'id')),['http'=>$code]);
    [$code,$body]=requestAudit('GET','dashboard/seller/booking-masters',['perPage'=>100],$tokens[103]);
    $ids=array_column($body['data']??[],'id');
    checkAudit('own_specialist_105_is_visible',$code===200&&in_array(105,$ids),['http'=>$code,'ids'=>$ids]);
    checkAudit('legitimate_shared_specialist_102_remains_visible',in_array(102,$ids));
    checkAudit('b_only_specialist_not_enumerated',!in_array($foreign->id,$ids)&&$code===200);
    [$code,$body]=requestAudit('GET','dashboard/seller/booking-masters',['shop_id'=>102],$tokens[103]);
    checkAudit('crafted_lookup_shop_context_rejected',in_array($code,[400,422],true),['http'=>$code]);
    [$code]=requestAudit('GET','dashboard/seller/booking-masters/'.$foreign->id,[],$tokens[103]);
    checkAudit('direct_foreign_specialist_rejected',$code===404,['http'=>$code]);
    [$code,$body]=requestAudit('GET','dashboard/seller/booking-masters/102',[],$tokens[103]);
    checkAudit('shared_profile_assignments_are_only_shop_A',$code===200&&count($body['data']['service_masters']??[])>0
        &&!array_filter($body['data']['service_masters']??[],fn($row)=>(int)$row['shop_id']!==101),['http'=>$code]);
    [$timeCode,$window]=requestAudit('GET','rest/master/times-all',
        ['service_master_ids'=>[101],'start_date'=>'2026-10-05 18:30'],$tokens[103]);
    $selectedDay=collect($window['data'][0]['times']??[])->firstWhere('date','2026-10-06');
    checkAudit('availability_window_contains_selected_next_day',$timeCode===200
        &&$selectedDay!==null&&in_array('10:00',$selectedDay['times']??[],true),
        ['http'=>$timeCode,'selected_date'=>'2026-10-06','selected_time_available'=>in_array('10:00',$selectedDay['times']??[],true)]);
    $newClient=['name'=>'Audit isolated local','email'=>'local@audit.invalid','phone'=>'+15550101010'];
    [$code,$client]=requestAudit('POST','dashboard/seller/booking-clients',$newClient,$tokens[103]);
    $clientId=$client['data']['id']??null;
    checkAudit('local_client_created_without_account',$code===201&&$clientId&&!User::where('email',$newClient['email'])->exists(),['http'=>$code]);
    [$retryCode,$retry]=requestAudit('POST','dashboard/seller/booking-clients',$newClient,$tokens[103]);
    checkAudit('contact_retry_reuses_same_client',$retryCode===200&&($retry['data']['id']??null)===$clientId);
    [$existingCode,$existing]=requestAudit('POST','dashboard/seller/booking-clients',
        ['name'=>'Audit existing account','email'=>'native-101@agendaally.test'],$tokens[103]);
    checkAudit('authorized_existing_platform_customer_is_reused',$existingCode===200
        &&($existing['data']['kind']??null)==='registered'&&(int)($existing['data']['id']??0)===101);
    [$code,$clients]=requestAudit('GET','dashboard/seller/booking-clients',['search'=>'local@audit.invalid'],$tokens[106]);
    checkAudit('local_client_PII_not_visible_to_shop_B',$code===200&&empty($clients['data']));
    $base=['local_client_id'=>$clientId,'start_date'=>'2032-11-12 11:00','data'=>[['service_master_id'=>102,'start_date'=>'2032-11-12 11:00']]];
    foreach(['foreign_assignment'=>$assignment->id,'foreign_shop_shared_assignment'=>103]as$key=>$id) {
        $input=$base;$input['data'][0]['service_master_id']=$id;
        [$code,$response]=requestAudit('POST','dashboard/seller/bookings',$input,$tokens[103]);
        checkAudit($key.'_create_denied',in_array($code,[400,422],true),['http'=>$code,'message'=>$response['message']??null]);
    }
    $otherClient=App\Models\SellerBookingClient::create(['shop_id'=>102,'name'=>'Audit private B client','dedupe_scope'=>'shop']);
    $input=$base;$input['local_client_id']=$otherClient->id;
    [$code]=requestAudit('POST','dashboard/seller/bookings',$input,$tokens[103]);
    checkAudit('foreign_local_client_create_denied',$code===422,['http'=>$code]);
    $input=$base;$input['shop_id']=102;
    [$code]=requestAudit('POST','dashboard/seller/bookings',$input,$tokens[103]);
    checkAudit('foreign_shop_body_create_denied',in_array($code,[400,422],true),['http'=>$code]);
    foreach(['owner'=>$tokens[103],'staff'=>$tokens['staff']]as$kind=>$token) {
        [$ownCode]=requestAudit('GET','dashboard/seller/bookings/8',[],$token);
        checkAudit($kind.'_can_read_authorized_own_booking',$ownCode===200,['http'=>$ownCode]);
        if($kind==='staff') {
            // Read-only reproduction of the shared role check that the old
            // notes/time controllers relied on. Never invoke its mutation.
            $accepted=false;
            try {app(\App\Services\BookingService\BookingService::class)
                ->checkAssignedBeforeUpdate($foreignBooking);$accepted=true;}catch(Throwable $e){}
            checkAudit('shared_service_check_alone_does_not_protect_foreign_staff_target',$accepted);
        }
        [$code]=requestAudit('GET','dashboard/seller/bookings/'.$foreignBooking->id,[],$token);
        checkAudit($kind.'_foreign_booking_read_denied',in_array($code,[400,403,404],true),['http'=>$code]);
        $notesBefore=Booking::findOrFail($foreignBooking->id)->notes;
        [$code]=requestAudit('POST','dashboard/seller/bookings/'.$foreignBooking->id.'/notes/update',['note'=>'SAFE_ROLLED_BACK_AUDIT'],$token);
        checkAudit($kind.'_foreign_booking_notes_denied',in_array($code,[400,403,404],true)
            &&Booking::findOrFail($foreignBooking->id)->notes===$notesBefore,['http'=>$code]);
        [$code]=requestAudit('PUT','dashboard/seller/bookings/'.$foreignBooking->id,['start_date'=>'2032-11-12 12:00'],$token);
        checkAudit($kind.'_foreign_booking_edit_denied',in_array($code,[400,403,404],true),['http'=>$code]);
        [$code]=requestAudit('POST','dashboard/seller/bookings/'.$foreignBooking->id.'/times/update',
            ['start_date'=>'2032-11-12 12:00','end_date'=>'2032-11-12 13:00','next_times_update'=>false],$token);
        checkAudit($kind.'_foreign_booking_time_change_denied',in_array($code,[400,403,404],true),['http'=>$code]);
        [$code]=requestAudit('POST','dashboard/seller/bookings/'.$foreignBooking->id.'/status/update',
            ['status'=>'canceled'],$token);
        checkAudit($kind.'_foreign_booking_cancel_denied',in_array($code,[400,403,404],true),['http'=>$code]);
    }
    [$code,$preview]=requestAudit('POST','dashboard/seller/bookings/calculate',$base,$tokens[103]);
    checkAudit('own_assignment_local_client_preview_allowed',$code===200&&!empty($preview['data']['items']),['http'=>$code,'message'=>$preview['message']??null]);
    [$code]=requestAudit('PUT','dashboard/seller/bookings/8',['service_master_id'=>$assignment->id],$tokens[103]);
    checkAudit('own_booking_cannot_be_reassigned_to_foreign_service_or_specialist',in_array($code,[400,422],true),['http'=>$code]);
    Invitation::whereKey(102)->update(['status'=>0]);
    [$code]=requestAudit('GET','dashboard/seller/booking-masters/105',[],$tokens[103]);
    checkAudit('revoked_own_specialist_lookup_denied',$code===404);
    [$code]=requestAudit('POST','dashboard/seller/bookings/calculate',$base,$tokens[103]);
    checkAudit('revoked_own_assignment_preview_denied',in_array($code,[400,422],true),['http'=>$code]);
    // Native monetary/capacity transactions deliberately refuse nested
    // transactions. Positive persistence is exercised in a separate scratch DB.
} catch(Throwable $e) {
    $out['error']=['type'=>get_class($e),'message'=>substr($e->getMessage(),0,220)];
} finally {
    while(DB::transactionLevel()>0)DB::rollBack();
    Illuminate\Support\Facades\Auth::forgetGuards();
}
$after=stagingFingerprints(stagingRootPdo());
checkAudit('isolated_fixture_rows_all_rolled_back',$before['tables']===$after['tables']);
$out['status']=!isset($out['error'])&&!in_array(false,$out['checks'],true)?'PASS':'FAIL';
$output=$argv[1]??getcwd().'/.local/staging-mvp/vendor-calendar-audit.json';
file_put_contents($output,json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);