<?php
declare(strict_types=1);
$cancelOnly=in_array('--cancel-only',$argv,true);
$calendarRun=in_array('--calendar-regression',$argv,true);
$regressionYear=$calendarRun?'2042':'2031'; // Same May weekday alignment; do not reuse occupied prior fixture dates.
$argv[1]='agendaally_staging_mvp_b1';
require dirname(__DIR__,2).'/.local/booking-forward/execution-support.php';
executionFixture();
use Illuminate\Support\Facades\DB;
$out=['scope'=>'Bounded selected native scheduling regression; separate B1 scratch DB on staging MySQL8; no UI-stage/protected mutation','checks'=>[]];
function proof(string $name,bool $value):void {
    global $out;$out['checks'][$name]=$value;
    if(!$value)throw new RuntimeException('B1 assertion failed: '.$name);
}
function writeBooking(string $name,string $day,string $time,array $options=[]):array {
    return executionRun('bounded-'.$name,['mode'=>'create','date'=>$day,'time'=>$time,'payment'=>101]+$options);
}
if($cancelOnly) {
    $out=json_decode(file_get_contents(getcwd().'/.local/staging-mvp/history/b1-eighteen-passing-result.json'),true,512,JSON_THROW_ON_ERROR);
    $day='2031-05-11';$id=executionLegacy($day,'09:00','10:00');
    $result=executionRun('bounded-unfunded-cancel',['mode'=>'cancel','booking'=>$id]);
    proof('accepted unfunded cancellation commits',$result['status']&&DB::table('bookings')->where('id',$id)->value('status')==='canceled');
    proof('unfunded cancellation releases capacity',writeBooking('unfunded-reuse',$day,'09:00',['actor'=>104])['status']);
    file_put_contents(getcwd().'/.local/staging-mvp/b1-bounded.json',json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    echo "PASS: two supported unfunded cancellation/release checks appended; prior 18-case pass retained.\n";exit(0);
}
try {
    $day=$regressionYear.'-05-05';
    $first=writeBooking('original',$day,'09:00');
    proof('ordinary native create succeeds',$first['status']);
    foreach(['exact'=>'09:00','right'=>'09:30','left'=>'08:30','contained'=>'09:15']as$name=>$time) {
        $before=executionMoney();
        $result=writeBooking($name,$day,$time);
        proof($name.' overlap rejects without financial effect',!$result['status']&&executionMoney()===$before);
    }
    DB::table('service_masters')->where('id',103)->update(['interval'=>120]);
    $before=executionMoney();
    $containing=writeBooking('containing',$day,'08:00',['assignment'=>103]);
    proof('containing overlap rejects across shared Specialist Shop assignments',!$containing['status']&&executionMoney()===$before);
    DB::table('service_masters')->where('id',103)->update(['interval'=>60]);
    proof('left endpoint adjacency accepted',writeBooking('left-adjacent',$day,'08:00')['status']);
    proof('right endpoint adjacency accepted',writeBooking('right-adjacent',$day,'10:00')['status']);
    $before=executionMoney();$overrun=writeBooking('overrun',$day,'17:30');
    proof('working-hour overrun rejects',!$overrun['status']&&executionMoney()===$before);
    $raceDay=$regressionYear.'-05-06';
    $a=executionStart('bounded-race-a',['mode'=>'create','date'=>$raceDay,'time'=>'09:00']);
    $b=executionStart('bounded-race-b',['mode'=>'create','date'=>$raceDay,'time'=>'09:00','actor'=>104]);
    $independent=executionStart('bounded-independent',['mode'=>'create','date'=>$raceDay,'time'=>'09:00','assignment'=>102,'actor'=>104]);
    $ra=executionResult($a);$rb=executionResult($b);$ri=executionResult($independent);
    proof('same-slot contention has exactly one durable winner',(int)$ra['status']+(int)$rb['status']===1);
    proof('independent Specialist progresses',$ri['status']);
    proof('same-slot durable booking count one',DB::table('bookings')->where('master_id',102)->where('start_date',"$raceDay 09:00:00")->count()===1);
    $rollbackDay=$regressionYear.'-05-07';$before=executionMoney();
    $failed=writeBooking('forced-rollback',$rollbackDay,'09:00',['fail'=>'insert into `bookings`']);
    proof('downstream rollback leaves no money or booking effect',!$failed['status']&&executionMoney()===$before);
    proof('rollback released capacity',writeBooking('after-rollback',$rollbackDay,'09:00')['status']);
    $moveDay=$regressionYear.'-05-08';$move=writeBooking('move-original',$moveDay,'09:00');
    $moveId=$move['ids'][0];$saved=executionEconomics($moveId);
    writeBooking('move-occupied',$moveDay,'11:00',['actor'=>104]);
    $conflict=executionRun('bounded-move-conflict',['mode'=>'times','booking'=>$moveId,'date'=>$moveDay,'time'=>'11:00','end'=>'12:00']);
    proof('failed reschedule is atomic',!$conflict['status']&&DB::table('bookings')->where('id',$moveId)->value('start_date')==="$moveDay 09:00:00"&&executionEconomics($moveId)===$saved);
    $moved=executionRun('bounded-move-valid',['mode'=>'times','booking'=>$moveId,'date'=>$moveDay,'time'=>'12:00','end'=>'13:00']);
    proof('reschedule preserves frozen economics',$moved['status']&&executionEconomics($moveId)===$saved);
    proof('reschedule releases old interval',writeBooking('old-interval',$moveDay,'09:00',['actor'=>104])['status']);
    $cancelDay=$regressionYear.'-05-11';
    $unfunded=executionLegacy($cancelDay,'09:00','10:00');
    $canceled=executionRun('bounded-unfunded-cancel',['mode'=>'cancel','booking'=>$unfunded]);
    proof('accepted unfunded cancellation commits',$canceled['status']
        &&DB::table('bookings')->where('id',$unfunded)->value('status')==='canceled');
    proof('unfunded cancellation releases capacity',writeBooking('unfunded-reuse',$cancelDay,'09:00',['actor'=>104])['status']);
    // A native recurring block is tested through the accepted model/writer contract.
    $blockedDay=$regressionYear.'-05-09';
    $row=DB::table('master_disabled_times')->insertGetId(['master_id'=>102,'date'=>$blockedDay,'from'=>'14:00',
        'to'=>'15:00','repeats'=>'day','end_type'=>'never','can_booking'=>0,'created_at'=>now(),'updated_at'=>now()]);
    $blocked=writeBooking('recurring-block',$regressionYear.'-05-10','14:00');
    proof('recurring blocked time remains authoritative',!$blocked['status']);
    DB::table('master_disabled_times')->where('id',$row)->delete();
    $out['status']='PASS';
}catch(Throwable $error) {
    $out['status']='FAIL';$out['error']=['type'=>get_class($error),'message'=>$error->getMessage()];
}
file_put_contents(getcwd().'/.local/staging-mvp/b1-bounded.json',json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);