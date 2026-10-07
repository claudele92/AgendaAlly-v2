<?php
declare(strict_types=1);
// Test-owned loopback HTTP entry point. No normal bootstrap, tokens in logs,
// auth override endpoint, email, provider transport or production configuration.
if (PHP_SAPI!=='cli-server' || !in_array($_SERVER['REMOTE_ADDR']??'', ['127.0.0.1','::1'],true)) {
    http_response_code(403); exit;
}
require __DIR__.'/manual-finance-regression-bootstrap.php';
final class ManualFinanceIdentityServer extends \Tests\Hardening\ManualFinanceHttpFixture {}
try {
    $fixture=new ManualFinanceIdentityServer('http');
    $app=$fixture->openHttpDatabase(defined('MANUAL_FINANCE_HTTP_DIRECTORY')?MANUAL_FINANCE_HTTP_DIRECTORY:'');
    $request=\Illuminate\Http\Request::capture();
    $app->instance('request',$request);
    $response=$app['router']->dispatch($request);
} catch (\Illuminate\Auth\AuthenticationException $e) {
    $response=new \Illuminate\Http\JsonResponse(['message'=>'Unauthenticated.'],401);
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    $response=new \Illuminate\Http\JsonResponse(['message'=>$e->getMessage()],$e->getStatusCode());
} catch (\Illuminate\Validation\ValidationException $e) {
    $response=new \Illuminate\Http\JsonResponse(['message'=>'Validation failed','errors'=>$e->errors()],422);
} catch (\Throwable $e) {
    // Details stay in the private test log; an unexpected bootstrap failure is not a denial pass.
    error_log(get_class($e).': '.$e->getMessage());
    $response=new \Illuminate\Http\JsonResponse(['message'=>'Isolated HTTP fixture failed.'],500);
}
$response->send();
