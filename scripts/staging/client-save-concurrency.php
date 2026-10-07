<?php
declare(strict_types=1);
require __DIR__ . '/runtime.php';
require __DIR__ . '/db-evidence.php';
use App\Models\SellerBookingClient;
use App\Support\SellerClientSaveIntent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = stagingApplication();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root = dirname(__DIR__, 2);
$before = stagingFingerprints(stagingRootPdo());
$key = (string) Str::uuid();
$name = 'Concurrent synthetic ' . $key;
$payload = ['name' => $name, 'phone' => null, 'email' => null, 'shop_location_id' => null];
DB::purge();
$pids = [];
for ($worker = 0; $worker < 2; $worker++) {
    $pid = pcntl_fork();
    if ($pid === -1) throw new RuntimeException('Unable to fork bounded concurrent workers.');
    if ($pid === 0) {
        try {
            DB::purge();
            $response = SellerClientSaveIntent::execute(103, 101, $key, $payload,
                function () use ($name) {
                    usleep(200000); // Force overlapping attempts while the intent lock is held.
                    $client = SellerBookingClient::create(['shop_id' => 101, 'dedupe_scope' => 'shop', 'name' => $name]);
                    return response()->json(['data' => ['kind' => 'local', 'id' => $client->id], 'reused_existing' => false], 201);
                },
                fn ($intent) => response()->json(['data' => ['kind' => $intent->result_kind, 'id' => (int) $intent->result_id],
                    'reused_existing' => (bool) $intent->reused_existing], (int) $intent->response_status)
            );
            file_put_contents("$root/.local/staging-mvp/client-save-worker-$worker.json", json_encode($response->getData(true), JSON_THROW_ON_ERROR));
            exit(0);
        } catch (Throwable $error) {
            file_put_contents("$root/.local/staging-mvp/client-save-worker-$worker.json", json_encode(['failure' => get_class($error)]));
            exit(1);
        }
    }
    $pids[] = $pid;
}
$workersPass = true;
foreach ($pids as $pid) {
    pcntl_waitpid($pid, $status);
    $workersPass = $workersPass && pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0;
}
DB::purge();
$responses = [];
for ($worker = 0; $worker < 2; $worker++) {
    $responses[] = json_decode(file_get_contents("$root/.local/staging-mvp/client-save-worker-$worker.json"), true, flags: JSON_THROW_ON_ERROR);
}
$ids = array_map(fn ($body) => $body['data']['id'] ?? null, $responses);
$checks = [
    'both_native_mysql_workers_succeeded' => $workersPass,
    'same_intent_returns_same_result' => $ids[0] !== null && $ids[0] === $ids[1],
    'exactly_one_directory_person' => SellerBookingClient::where('shop_id', 101)->where('name', $name)->count() === 1,
    'exactly_one_durable_receipt' => DB::table(SellerClientSaveIntent::TABLE)->where(SellerClientSaveIntent::authority(103, 101, $key))->count() === 1,
];
try {
    // A native export/import of the receipt simulates restore without changing its identity.
    $row = (array) DB::table(SellerClientSaveIntent::TABLE)->where(SellerClientSaveIntent::authority(103, 101, $key))->first();
    DB::transaction(function () use ($row) {
        DB::table(SellerClientSaveIntent::TABLE)->where('id', $row['id'])->delete();
        DB::table(SellerClientSaveIntent::TABLE)->insert($row);
    });
    $replay = SellerClientSaveIntent::execute(103, 101, $key, $payload,
        function () { throw new RuntimeException('Restored receipt must not create a client.'); },
        fn ($intent) => (int) $intent->result_id);
    $checks['restored_receipt_replays_without_creation'] = $replay === $ids[0];
} finally {
    DB::table(SellerClientSaveIntent::TABLE)->where(SellerClientSaveIntent::authority(103, 101, $key))->delete();
    SellerBookingClient::where('shop_id', 101)->where('name', $name)->delete();
}
$checks['all_synthetic_committed_rows_removed'] = $before['tables'] === stagingFingerprints(stagingRootPdo())['tables'];
$out = ['scope' => 'Two overlapping native MySQL RR workers plus receipt restore/replay; synthetic fixture rows removed', 'checks' => $checks,
    'status' => in_array(false, $checks, true) ? 'FAIL' : 'PASS'];
file_put_contents("$root/.local/staging-mvp/client-save-concurrency.json", json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
echo json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
exit($out['status'] === 'PASS' ? 0 : 1);