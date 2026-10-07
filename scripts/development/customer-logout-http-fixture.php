<?php
declare(strict_types=1);
// Isolated, synthetic-only HTTP fixture. Never boots the live application.
$root=dirname(__DIR__,2);
if (getenv('REPLIT_DEPLOYMENT')!==false) { http_response_code(403); exit; }
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
file_put_contents('/tmp/customer-logout-ui/requests.jsonl',json_encode([
    'at'=>gmdate('c'),'method'=>$_SERVER['REQUEST_METHOD'],
    'path'=>preg_match('/^[a-zA-Z0-9\\/_\\.\\-]{1,100}$/',(string)$path)?$path:'redacted',
])."\n",FILE_APPEND|LOCK_EX);
chmod('/tmp/customer-logout-ui/requests.jsonl',0600);
if ($path==='/' || $path==='/fixture.js') {
    header($path==='/'?'Content-Type: text/html':'Content-Type: application/javascript');
    readfile('/tmp/customer-logout-ui/'.($path==='/'?'index.html':'fixture.js'));exit;
}
require $root.'/.migration-backup/backend/vendor/autoload.php';
$test=new Tests\Hardening\CustomerLogoutTest('test_push_cleanup_failure_does_not_prevent_revocation');
$app=$test->startHttpFixture('/tmp/customer-logout-http.sqlite');
$req=App\Http\Requests\FilterParamsRequest::createFrom(Illuminate\Http\Request::capture());
$app->instance('request',$req);
$app['auth']->forgetGuards();
if ($path==='/fixture/session') {
    // Only these synthetic actors exist in this separate four-table database.
    Illuminate\Support\Facades\DB::table('personal_access_tokens')->delete();
    Illuminate\Support\Facades\DB::table('users')->delete();
    $a=App\Models\User::withoutEvents(fn()=>App\Models\User::create([
        'email'=>'logout-a@synthetic.test','firebase_token'=>['synthetic-push','unrelated-push'],
    ]));
    $b=App\Models\User::withoutEvents(fn()=>App\Models\User::create([
        'email'=>'logout-b@synthetic.test','firebase_token'=>['other-account-push'],
    ]));
    $res=new Illuminate\Http\JsonResponse(['current'=>$a->createToken('current')->plainTextToken,
        'sibling'=>$a->createToken('sibling')->plainTextToken,'other'=>$b->createToken('other')->plainTextToken]);
} elseif ($path==='/api/v1/auth/logout') {
    if (is_file('/tmp/customer-logout-ui/fail-logout')) {
        $res=new Illuminate\Http\JsonResponse(['message'=>'Synthetic transport failure'],503);
    } else {
        $res=(new App\Http\Controllers\API\v1\Auth\LoginController())->logout($req);
    }
} elseif ($path==='/protected') {
    try { $res=$app['router']->dispatch($req); }
    catch (Illuminate\Auth\AuthenticationException) { $res=new Illuminate\Http\JsonResponse(['message'=>'Unauthenticated'],401); }
} else {
    $res=new Illuminate\Http\JsonResponse(['message'=>'Fixture route not found'],404);
}
$res->send();
