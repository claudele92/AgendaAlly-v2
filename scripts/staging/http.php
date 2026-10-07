<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Illuminate\Http\Middleware\TrustProxies::at(['127.0.0.1']);
date_default_timezone_set('UTC');
if(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)==='/staging/simulation/failure') {
    // Deliberate loopback-only operational probe; do not log supplied headers/body.
    Illuminate\Support\Facades\Log::error('STAGING_SAFE_HTTP_FAILURE',['probe'=>'O1','redacted'=>true]);
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":false,"code":"STAGING_SAFE_HTTP_FAILURE"}';
    exit;
}
// API health is runtime-derived and available only through the proxy.
if(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)==='/staging/health') {
    $pdo=Illuminate\Support\Facades\DB::connection()->getPdo();
    $server=$pdo->query("SELECT @@transaction_isolation isolation_level, @@sql_mode sql_mode, @@session.time_zone time_zone, CURRENT_USER() db_user")->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode(['status'=>'ok','environment'=>$app->environment(),'debug'=>config('app.debug'),
        'transport'=>PHP_SAPI,'database'=>$server,'keyFingerprint'=>hash('sha256',(string)config('app.key')),
        'session'=>['secure'=>config('session.secure'),'httpOnly'=>config('session.http_only'),'sameSite'=>config('session.same_site')]],JSON_THROW_ON_ERROR);
    exit;
}
$request=Illuminate\Http\Request::capture();
$response=$kernel->handle($request);
$response->send();
$kernel->terminate($request,$response);