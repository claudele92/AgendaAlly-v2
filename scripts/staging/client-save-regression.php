<?php
declare(strict_types=1);
require __DIR__ . '/runtime.php';
require __DIR__ . '/db-evidence.php';
use App\Models\User;
use App\Models\SellerBookingClient;
use App\Support\SellerClientSaveIntent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = stagingApplication();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Illuminate\Support\Facades\Bus::fake();
Illuminate\Support\Facades\Http::fake();
$out = ['scope' => 'Native MySQL directory-save HTTP kernel; all synthetic rows rolled back; no financial operations', 'checks' => []];
function clientCheck(string $key, bool $pass): void {
    global $out;
    $out['checks'][$key] = $pass;
}
function clientRequest(string $method, string $path, array $data, string $token): array {
    global $app, $kernel;
    Illuminate\Support\Facades\Auth::forgetGuards();
    $app['session']->driver()->flush();
    foreach ($app['router']->getRoutes() as $route) $route->flushController();
    $request = Illuminate\Http\Request::create('https://localhost:8444/api/v1/dashboard/seller/' . $path,
        $method, $data, [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => 'on']);
    $app->instance('request', $request);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return [$response->getStatusCode(), json_decode($response->getContent(), true)];
}
$before = stagingFingerprints(stagingRootPdo());
DB::beginTransaction();
try {
    $token = User::findOrFail(103)->createToken('client-save-rolled-back')->plainTextToken;
    $otherToken = User::findOrFail(106)->createToken('client-save-foreign-rolled-back')->plainTextToken;
    $key = (string) Str::uuid();
    $input = ['name' => 'Same name synthetic client', 'client_save_intent' => $key];
    $initialCount = SellerBookingClient::count();
    [$code, $first] = clientRequest('POST', 'booking-clients', $input, $token);
    $id = $first['data']['id'] ?? null;
    clientCheck('name_only_save_created', $code === 201 && $id !== null);
    // Simulate lost acknowledgement: discard the response and submit original intent.
    [$retryCode, $retry] = clientRequest('POST', 'booking-clients', $input, $token);
    clientCheck('lost_acknowledgement_retry_same_reference', $retryCode === 201 && ($retry['data']['id'] ?? null) === $id);
    clientCheck('retry_does_not_insert_second_person', SellerBookingClient::count() === $initialCount + 1);
    [$status, $resolved] = clientRequest('GET', 'booking-client-save-intents/' . $key, [], $token);
    clientCheck('reload_recovery_resolves_authorized_reference', $status === 200 && ($resolved['data']['id'] ?? null) === $id);
    [$changed] = clientRequest('POST', 'booking-clients', $input + ['phone' => '+15550109099'], $token);
    clientCheck('same_key_changed_payload_conflicts', $changed === 409);
    $ownBranch = DB::table('shop_locations')->where('shop_id', 101)->where('type', 2)->value('id');
    $foreignBranch = DB::table('shop_locations')->where('shop_id', 102)->where('type', 2)->value('id');
    [$changedBranch] = clientRequest('POST', 'booking-clients', $input + ['shop_location_id' => $ownBranch], $token);
    clientCheck('same_intent_cannot_change_authorized_branch', $ownBranch !== null && $changedBranch === 409);
    [$foreignBranchCode] = clientRequest('POST', 'booking-clients', $input + ['shop_location_id' => $foreignBranch], $token);
    clientCheck('foreign_branch_denied_before_receipt_or_insert', $foreignBranch !== null && in_array($foreignBranchCode, [400, 422], true));
    [$foreign] = clientRequest('GET', 'booking-client-save-intents/' . $key, [], $otherToken);
    clientCheck('foreign_shop_cannot_recover_reference', $foreign === 404);
    [$unknown] = clientRequest('GET', 'booking-client-save-intents/' . Str::uuid(), [], $token);
    clientCheck('unknown_reference_is_not_claimed_resolved', $unknown === 404);
    $input['client_save_intent'] = (string) Str::uuid();
    [$newCode, $new] = clientRequest('POST', 'booking-clients', $input, $token);
    clientCheck('explicit_new_intent_same_name_distinct_person', $newCode === 201 && ($new['data']['id'] ?? null) !== $id);
    $contact = ['name' => 'Synthetic contact', 'email' => 'client-save-fixture@example.invalid', 'client_save_intent' => (string) Str::uuid()];
    [$contactCode, $contactFirst] = clientRequest('POST', 'booking-clients', $contact, $token);
    $contact['client_save_intent'] = (string) Str::uuid();
    [$reuseCode, $reuse] = clientRequest('POST', 'booking-clients', $contact, $token);
    clientCheck('authorized_contact_reuse_retained', $contactCode === 201 && $reuseCode === 200
        && ($reuse['data']['id'] ?? null) === ($contactFirst['data']['id'] ?? null));
    [$registeredCode, $registered] = clientRequest('POST', 'booking-clients', [
        'name' => 'Authorized registered customer', 'email' => 'native-101@agendaally.test',
        'client_save_intent' => (string) Str::uuid(),
    ], $token);
    clientCheck('registered_customer_reuse_without_account_creation', $registeredCode === 200
        && ($registered['data']['kind'] ?? null) === 'registered');
    // Scheduling lifecycle tests run in their own committed scratch DB, not
    // under this outer rollback transaction that retains native capacity locks.
    SellerBookingClient::whereKey($id)->delete();
    [$deleted] = clientRequest('POST', 'booking-clients', [
        'name' => 'Same name synthetic client', 'client_save_intent' => $key,
    ], $token);
    clientCheck('deleted_result_never_recreated', $deleted === 409);
    clientCheck('ledger_has_only_minimal_result_not_payload_PII',
        !in_array('name', Illuminate\Support\Facades\Schema::getColumnListing(SellerClientSaveIntent::TABLE), true)
        && !in_array('email', Illuminate\Support\Facades\Schema::getColumnListing(SellerClientSaveIntent::TABLE), true));
} finally {
    while (DB::transactionLevel() > 0) DB::rollBack();
}
$after = stagingFingerprints(stagingRootPdo());
clientCheck('all_isolated_fixture_rows_rolled_back', $before['tables'] === $after['tables']);
$out['status'] = in_array(false, $out['checks'], true) ? 'FAIL' : 'PASS';
file_put_contents(dirname(__DIR__, 2) . '/.local/staging-mvp/client-save-regression.json', json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
exit($out['status'] === 'PASS' ? 0 : 1);