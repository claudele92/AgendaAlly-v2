<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Illuminate\Http\Middleware\TrustProxies::at(['127.0.0.1']);
function callRestored(string $method,string $path,array $body=[],?string $token=null):array {
    global $kernel,$app;
    // CLI fixture isolation must clear both guards and shared session state.
    Illuminate\Support\Facades\Auth::forgetGuards();
    $app['session']->driver()->flush();
    $server=['HTTP_ACCEPT'=>'application/json','HTTPS'=>'on','REMOTE_ADDR'=>'127.0.0.1'];
    if($token)$server['HTTP_AUTHORIZATION']='Bearer '.$token;
    $request=Illuminate\Http\Request::create('https://localhost:8443'.$path,$method,$body,[],[],$server);
    $app->instance('request',$request);
    $response=$kernel->handle($request);
    $data=json_decode($response->getContent(),true);
    $kernel->terminate($request,$response);
    return [$response->getStatusCode(),$data];
}
$out=['scope'=>(getenv('AGENDAALLY_STAGING_INSTANCE')==='restore'?'Restored':'Source').
    ' production-mode runtime; real authentication and selected role read controllers, no fabricated provider/CAPTCHA acceptance','roles'=>[]];
foreach([101=>'user',103=>'seller',105=>'master',107=>'admin']as$id=>$role) {
    [$loginCode,$login]=callRestored('POST','/api/v1/auth/login',[
        'email'=>"native-$id@agendaally.test",'password'=>'AgendaAlly-Dev-Only-2026!']);
    $token=$login['data']['access_token']??$login['data']['token']??null;
    if(!is_string($token)) {$out['roles'][$role]=['loginStatus'=>$loginCode,'readStatus'=>null,'pass'=>false];continue;}
    [$code,$body]=callRestored('GET',"/api/v1/dashboard/$role/bookings?lang=en&shop_id=101&perPage=10",[],$token);
    $out['roles'][$role]=['loginStatus'=>$loginCode,'readStatus'=>$code,
        'rows'=>is_array($body['data']??null)?count($body['data']):null,'pass'=>$loginCode===200&&$code===200];
}
$out['status']=!in_array(false,array_column($out['roles'],'pass'),true)?'PASS':'FAIL';
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);