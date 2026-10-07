<?php
declare(strict_types=1);

// Apply ONLY the approved additive ledger to isolated acceptance schemas.
// Runtime DML credentials stay unchanged and do not receive DDL privileges.
require __DIR__ . '/runtime.php';
$app = stagingApplication();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$root = dirname(__DIR__, 2);
foreach ([
    'staging' => [$root . '/.local/staging-mvp/mysql/mysql.sock', 'agendaally_staging_mvp'],
    'acceptance' => [$root . '/.local/booking-forward/mysql-data/mysql.sock', 'agendaally_payment_disposable_booking_execution_2'],
] as $name => [$socket, $database]) {
    $connection = 'client_save_schema_' . $name;
    $app['config']->set("database.connections.$connection", [
        'driver' => 'mysql', 'unix_socket' => $socket, 'database' => $database,
        'username' => 'root', 'password' => '', 'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
    ]);
    $schema = Illuminate\Support\Facades\Schema::connection($connection);
    if (!$schema->hasTable('seller_client_save_intents')) {
        Illuminate\Support\Facades\Schema::swap($schema);
        $migration = require $root . '/.migration-backup/backend/database/migrations/2026_10_04_010000_add_seller_client_save_intents.php';
        $migration->up();
    }
    echo "$name approved additive client-save ledger ready\n";
}

// Existing normal demo receives only this approved additive table. Do not
// replay its broader migration chain or apply other deferred additions.
$base = $root . '/.migration-backup/backend';
$path = $base . '/database/development/agendaally.sqlite';
$guard = App\Console\Commands\DevelopmentDatabaseGuard::class;
if (!$guard::ownsDatabase($path, $base, $guard::reviewedManifest($base))) {
    throw new RuntimeException('Normal SQLite is not the verified owned demo database.');
}
$app['config']->set('database.connections.client_save_normal', [
    'driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => true,
]);
$schema = Illuminate\Support\Facades\Schema::connection('client_save_normal');
if (!$schema->hasTable('seller_client_save_intents')) {
    Illuminate\Support\Facades\Schema::swap($schema);
    $migration = require $base . '/database/migrations/2026_10_04_010000_add_seller_client_save_intents.php';
    $migration->up();
}
$normal = Illuminate\Support\Facades\DB::connection('client_save_normal');
$migrationName = '2026_10_04_010000_add_seller_client_save_intents';
if (!$normal->table('migrations')->where('migration', $migrationName)->exists()) {
    $normal->table('migrations')->insert([
        'migration' => $migrationName,
        'batch' => (int) $normal->table('migrations')->max('batch') + 1,
    ]);
}
echo "normal verified owned demo approved additive client-save ledger ready; no broader migration replay\n";