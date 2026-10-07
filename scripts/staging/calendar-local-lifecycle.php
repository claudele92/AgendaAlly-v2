<?php
declare(strict_types=1);
require __DIR__ . '/runtime.php';
use Illuminate\Support\Facades\DB;
use App\Models\User;

$app = stagingApplication();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
// Reviewed scratch clone only; never point either preview at this connection.
$database = 'agendaally_staging_mvp_calendar_local';
$app['config']->set('database.connections.staging.database', $database);
$app['config']->set('database.connections.staging.unix_socket', dirname(__DIR__, 2) . '/.local/staging-mvp/mysql/mysql.sock');
$app['config']->set('database.connections.staging.username', 'root');
$app['config']->set('database.connections.staging.password', '');
DB::purge('staging');
Illuminate\Support\Facades\Bus::fake();
Illuminate\Support\Facades\Http::fake();
$out = ['scope' => 'Committed native local-unpaid lifecycle in dedicated disposable scratch clone; no preview retarget or provider transport', 'checks' => []];
$token = User::findOrFail(103)->createToken('calendar-local-scratch')->plainTextToken;
$request = function (string $method, string $path, array $body = []) use ($app, $kernel, $token): array {
    Illuminate\Support\Facades\Auth::forgetGuards();
    $app['session']->driver()->flush();
    foreach ($app['router']->getRoutes() as $route) $route->flushController();
    $req = Illuminate\Http\Request::create('https://localhost:8444/api/v1/dashboard/seller/' . $path, $method, $body, [], [],
        ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => 'on']);
    $app->instance('request', $req);
    $response = $kernel->handle($req);
    $kernel->terminate($req, $response);
    return [$response->getStatusCode(), json_decode($response->getContent(), true)];
};
[$clientCode, $client] = $request('POST', 'booking-clients', ['name' => 'Scratch local lifecycle',
    'client_save_intent' => (string) Illuminate\Support\Str::uuid()]);
$clientId = $client['data']['id'] ?? null;
$out['checks']['local_directory_save'] = $clientCode === 201 && $clientId !== null;
$input = ['local_client_id' => $clientId, 'start_date' => '2032-11-15 09:00',
    'data' => [['service_master_id' => 101, 'start_date' => '2032-11-15 09:00']]];
[$previewCode, $preview] = $request('POST', 'bookings/calculate', $input);
$out['checks']['native_unpaid_review_calculation'] = $previewCode === 200 && !empty($preview['data']['items']);
[$createCode, $created] = $request('POST', 'bookings', $input);
$id = DB::table('bookings')->where('local_client_id', $clientId)->where('start_date', '2032-11-15 09:00:00')->value('id');
$out['checks']['native_local_unpaid_create'] = in_array($createCode, [200, 201], true) && $id !== null;
if ($id !== null) {
    [$readCode, $saved] = $request('GET', 'bookings/' . $id);
    $out['checks']['exact_saved_detail_reference'] = $readCode === 200 && (int) ($saved['data']['id'] ?? 0) === (int) $id;
    $prior = (array) DB::table('bookings')->where('id', $id)->first();
    [$moveCode] = $request('POST', 'bookings/' . $id . '/times/update',
        ['start_date' => '2032-11-15 11:00', 'end_date' => '2032-11-15 12:00', 'next_times_update' => false]);
    $moved = (array) DB::table('bookings')->where('id', $id)->first();
    $out['checks']['native_atomic_reschedule'] = $moveCode === 200 && $moved['start_date'] === '2032-11-15 11:00:00';
    $money = array_flip(['total_price', 'price', 'commission_fee', 'currency_id', 'rate', 'local_client_id']);
    $out['checks']['reschedule_retains_financial_evidence'] = array_intersect_key($prior, $money) === array_intersect_key($moved, $money);
    [$cancelCode] = $request('POST', 'bookings/' . $id . '/status/update', ['status' => 'canceled']);
    $out['checks']['native_cancel'] = $cancelCode === 200 && DB::table('bookings')->where('id', $id)->value('status') === 'canceled';
    $input['start_date'] = '2032-11-15 11:00';
    $input['data'][0]['start_date'] = $input['start_date'];
    [$reuseCode] = $request('POST', 'bookings', $input);
    $out['checks']['cancel_releases_capacity'] = in_array($reuseCode, [200, 201], true);
}
$out['http'] = ['client' => $clientCode, 'preview' => $previewCode, 'create' => $createCode];
$out['status'] = in_array(false, $out['checks'], true) ? 'FAIL' : 'PASS';
file_put_contents(dirname(__DIR__, 2) . '/.local/staging-mvp/calendar-local-lifecycle.json', json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
exit($out['status'] === 'PASS' ? 0 : 1);