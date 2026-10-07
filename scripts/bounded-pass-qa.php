<?php
declare(strict_types=1);

// Local-only verification support. No API surface, credentials or financial writes.
use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\ShopPermission;
use Illuminate\Support\Facades\DB;

$base = realpath(__DIR__ . '/../.migration-backup/backend');
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
DevelopmentDatabaseGuard::requireOptIn(
    (string) config('app.env'), env('AGENDAALLY_DEVELOPMENT_DATABASE'), env('DEVELOPMENT_MODE')
);
$manifest = DevelopmentDatabaseGuard::reviewedManifest($base);
$path = DevelopmentDatabaseGuard::resolveSqlitePath(
    $base, (string) config('database.default'), (array) config('database.connections.sqlite')
);
if (!DevelopmentDatabaseGuard::ownsDatabase($path, $base, $manifest)) {
    throw new RuntimeException('Verification requires the owned development SQLite database.');
}
$lock = fopen($path, 'c+b');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    throw new RuntimeException('Cannot lock the owned development database.');
}
try {
    if (!DevelopmentDatabaseGuard::ownsDatabase($path, $base, $manifest)) {
        throw new RuntimeException('Database ownership changed.');
    }
    $mode = $argv[1] ?? 'snapshot';
    if ($mode === 'permissions') {
        foreach ([
            'stories.view' => 'View shop Stories',
            'stories.manage' => 'Create, edit, upload and delete shop Stories',
        ] as $key => $label) {
            ShopPermission::firstOrCreate(['key' => $key], ['group' => 'stories', 'label' => $label]);
        }
        echo "Story permission definitions are available; no role grants were assigned.\n";
    } elseif ($mode === 'cleanup-client') {
        $id = filter_var($argv[2] ?? null, FILTER_VALIDATE_INT);
        $client = $id ? DB::table('seller_booking_clients')->where('id', $id)->first() : null;
        if (!$client || !str_starts_with($client->name, 'QA Calendar ')
            || DB::table('bookings')->where('local_client_id', $id)->exists()) {
            throw new RuntimeException('Cleanup permits only an unreferenced, named QA Calendar client.');
        }
        DB::table('seller_booking_clients')->where('id', $id)->delete();
        echo "Removed the exact unreferenced QA client.\n";
    } elseif ($mode === 'snapshot') {
        $summary = [];
        foreach (['users', 'bookings', 'stories', 'transactions', 'wallets', 'payouts'] as $table) {
            $summary[$table] = DB::table($table)->count();
        }
        $summary['wallet_total'] = (string) DB::table('wallets')->sum('price');
        echo json_encode($summary, JSON_THROW_ON_ERROR) . "\n";
    } else {
        throw new RuntimeException('Unknown local verification operation.');
    }
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}