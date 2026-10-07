<?php
declare(strict_types=1);

// Lab-only runner, not an installer. It cannot select a server or database.
$root = dirname(__DIR__, 2);
$approved = ($argv[1] ?? '') === '--confirm-approved-owned-empty-lab';
$state = $root . '/.local/' . ($approved ? 'mysql-approved-bootstrap-rehearsal' : 'mysql-bootstrap-rehearsal');
$base = $root . '/.local/clean-publication-validation/.migration-backup/backend';
$frozen = $root . '/.local/agendaally-clean-repository/.migration-backup/backend';
$retry = ($argv[1] ?? '') === '--confirm-owned-empty-retry-lab';
$schema = $approved ? 'agendaally_approved_empty_lab' : ($retry ? 'agendaally_empty_bootstrap_retry_lab' : 'agendaally_empty_bootstrap_lab');
$identity = $approved ? 'lab_bootstrap' : 'lab_migrator';
$socket = $state . '/mysql.sock';
if (count($argv) !== 2 || realpath($argv[0]) !== __FILE__
    || !in_array($argv[1], ['--confirm-owned-empty-lab', '--confirm-owned-empty-retry-lab', '--confirm-approved-owned-empty-lab'], true)
    || !is_file($state . '/owner.json')
    || is_link($state)
    || realpath($state) !== $state
    || !is_file($base . '/vendor/autoload.php')) {
    throw new RuntimeException('Only the independently owned local empty lab is permitted.');
}
$owner = json_decode(file_get_contents($state . '/owner.json'), true, 512, JSON_THROW_ON_ERROR);
if (($owner['root'] ?? '') !== $root
    || !in_array($schema, $owner['schemas'] ?? [$owner['schema'] ?? ''], true)) {
    throw new RuntimeException('Lab ownership does not match.');
}
if ($approved && (($owner['policy'] ?? '') !== 'owner-approved-local-temporary-super-locked-definer'
    || ($owner['priorEvidenceAvailable'] ?? true) !== false
    || (fileperms($state) & 0777) !== 0700
    || fileowner($state) !== posix_geteuid())) {
    throw new RuntimeException('Approved lab authority or private ownership does not match.');
}
foreach (['curl_exec', 'curl_multi_exec', 'fsockopen', 'pfsockopen', 'stream_socket_client',
    'socket_connect', 'mail', 'exec', 'shell_exec', 'proc_open', 'popen', 'system', 'passthru'] as $function) {
    if (function_exists($function)) {
        throw new RuntimeException('Lab requires process and transport functions disabled.');
    }
}
// Refuse inherited application URLs, cached configuration and dotenv authority.
foreach (array_unique(array_merge(array_keys($_SERVER), array_keys($_ENV))) as $key) {
    if (preg_match('/^(APP_|DB_|DATABASE_URL$|AGENDAALLY_|DEVELOPMENT_|MAIL_|AWS_|SESSION_|CACHE_|QUEUE_|REDIS_)/', $key)) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}
putenv('APP_ENV=testing');
$_ENV['APP_BASE_PATH'] = $base;
$files = glob($frozen . '/database/migrations/*.php');
sort($files, SORT_STRING);
$inventory = [];
foreach ($files as $file) {
    $relative = 'database/migrations/' . basename($file);
    $hash = hash_file('sha256', $file);
    if (!is_file($base . '/' . $relative) || hash_file('sha256', $base . '/' . $relative) !== $hash) {
        throw new RuntimeException('Validation migration differs from frozen source: ' . basename($file));
    }
    $inventory[] = basename($file) . ' ' . $hash;
}
unset($file);
$manifest = require $frozen . '/database/development/manifest.php';
if (count($inventory) !== $manifest['migration_count']
    || hash('sha256', implode("\n", $inventory)) !== $manifest['migration_set_sha256']) {
    throw new RuntimeException('Frozen migration manifest does not match.');
}
$rootDb = new PDO("mysql:unix_socket=$socket;charset=utf8mb4", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
if ($rootDb->query('SELECT @@datadir')->fetchColumn() !== $state . '/data/'
    || (int)$rootDb->query('SELECT @@skip_networking')->fetchColumn() !== 1
    || $rootDb->query('SELECT @@event_scheduler')->fetchColumn() !== 'OFF') {
    throw new RuntimeException('Server is not the socket-only owned lab.');
}
if ($approved && ($rootDb->query('SELECT @@version')->fetchColumn() !== '8.0.42'
    || (int)$rootDb->query('SELECT @@log_bin')->fetchColumn() !== 1
    || (int)$rootDb->query('SELECT @@log_bin_trust_function_creators')->fetchColumn() !== 0
    || (int)$rootDb->query("SELECT COUNT(*) FROM information_schema.plugins WHERE plugin_name='mysqlx' AND plugin_status='ACTIVE'")->fetchColumn() !== 0)) {
    throw new RuntimeException('Approved engine/binary-log/transport contract does not match.');
}
$empty = $rootDb->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
$empty->execute([$schema]);
if ((int)$empty->fetchColumn() !== 0) {
    throw new RuntimeException('Nonempty lab refused; never retry partially applied DDL automatically.');
}
$rootDb = null;
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->useEnvironmentPath($state);
$app->loadEnvironmentFrom('absent.env');
$app->useStoragePath($state . '/storage');
$app->useBootstrapPath($state . '/bootstrap');
$app->beforeBootstrapping(Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    function ($app) use ($state, $schema, $socket, $base, $identity): void {
        $app['env'] = 'testing';
        $app['config']->set([
            'app.env' => 'testing', 'app.debug' => false, 'app.key' => null, 'app.timezone' => 'UTC',
            'database.default' => 'lab',
            'database.connections' => ['lab' => [
                'driver' => 'mysql', 'unix_socket' => $socket, 'database' => $schema,
                'username' => $identity, 'password' => '', 'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
                'engine' => 'InnoDB', 'timezone' => '+00:00', 'isolation_level' => 'REPEATABLE READ',
            ]],
            'permission' => require $base . '/vendor/spatie/laravel-permission/config/permission.php',
            'cache.default' => 'array', 'session.driver' => 'array', 'mail.default' => 'array',
            'queue.default' => 'null', 'broadcasting.default' => 'null',
            'logging.default' => 'single', 'logging.channels.single.path' => $state . '/application.log',
            'development.enabled' => false, 'development.database.owned_sqlite_enabled' => false,
            'development.payments.mode' => 'disabled', 'development.sms.mode' => 'disabled',
            'development.email.mode' => 'log', 'development.email.admin_test_enabled' => false,
            'development.maps.enabled' => false, 'development.firebase.enabled' => false,
        ]);
    });
$receipt = [
    'scope' => 'Local socket-only empty MySQL; unchanged sanitized source; no seed or financial operation',
    'migrationSetSha256' => hash('sha256', implode("\n", $inventory)),
    'sourceMigrationCount' => count($inventory), 'completed' => [], 'failure' => null,
    'referenceInitialization' => 'NOT EXECUTED', 'adminKeyCustody' => 'NOT EXERCISED',
    'upgradeRestore' => 'NOT EXECUTED',
];
$prefix = $retry ? 'retry-' : '';
$persist = static function () use (&$receipt, $state, $prefix): void {
    file_put_contents($state . '/' . $prefix . 'receipt.json', json_encode($receipt, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
};
try {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $db = Illuminate\Support\Facades\DB::connection();
    if ($approved) {
        if ($db->selectOne('SELECT CURRENT_USER() AS identity')->identity !== 'lab_bootstrap@localhost') {
            throw new RuntimeException('Dedicated bootstrap identity required.');
        }
        Illuminate\Support\Facades\DB::listen(static function ($query) use ($state): void {
            if (preg_match('/^\s*(CREATE|ALTER|DROP)\b/i', $query->sql)) {
                file_put_contents($state . '/native-ddl-statements.jsonl',
                    json_encode(['sql' => $query->sql], JSON_THROW_ON_ERROR) . "\n", FILE_APPEND | LOCK_EX);
            }
        });
    }
    $receipt['engine'] = (array)$db->selectOne(
        'SELECT @@version version, @@sql_mode sql_mode, @@transaction_isolation isolation_level,
        @@session.time_zone timezone, @@character_set_database charset, @@collation_database collation_name'
    );
    $persist();
    // Use the native migrator and ledger, not direct up() or invented applied rows.
    $migrator = $app->make('migrator');
    $migrator->getRepository()->createRepository();
    foreach ($files as $file) {
        echo 'BEGIN ' . basename($file) . "\n";
        $migrator->run([$base . '/database/migrations/' . basename($file)], ['step' => true]);
        $receipt['completed'][] = basename($file);
        $persist();
        echo 'PASS ' . basename($file) . "\n";
    }
    $ledger = array_map(static fn($row) => $row->migration,
        $db->select('SELECT migration FROM migrations ORDER BY id'));
    $expected = array_map(static fn($file) => basename($file, '.php'), $files);
    if ($ledger !== $expected) {
        throw new RuntimeException('Actual native ledger differs from the complete frozen migration order.');
    }
    $receipt['status'] = 'DDL_ONLY_PASS';
} catch (Throwable $error) {
    $receipt['status'] = 'BLOCKED';
    // All lab data is synthetic. No connection password, dotenv or imported data in logs.
    $receipt['failure'] = [
        'migration' => isset($file) ? basename($file) : 'application bootstrap',
        'class' => get_class($error), 'message' => $error->getMessage(),
    ];
    // Preserve the primary native failure even if later evidence collection fails.
    $persist();
    echo 'STOP ' . $receipt['failure']['migration'] . ': ' . $error->getMessage() . "\n";
} finally {
    try {
    if (isset($db)) {
        $tables = $db->select('SELECT table_name AS lab_table_name FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name', [$schema]);
        $snapshot = [];
        foreach ($tables as $table) {
            $name = $table->lab_table_name;
            $quoted = '`' . str_replace('`', '``', $name) . '`';
            $ddl = (array)$db->selectOne('SHOW CREATE TABLE ' . $quoted);
            $rows = array_map(static fn($row) => json_encode((array)$row, JSON_THROW_ON_ERROR), $db->select('SELECT * FROM ' . $quoted));
            sort($rows, SORT_STRING);
            $snapshot[$name] = [
                'ddl' => array_values($ddl)[1], 'rows' => count($rows),
                'rowsSha256' => hash('sha256', implode("\n", $rows)),
            ];
        }
        $receipt['snapshotMethod'] = 'PDO/Laravel object to associative array; JSON no flags except THROW; byte-sort serialized rows; LF join, SHA256; raw SHOW CREATE TABLE retained';
        $receipt['snapshot'] = $snapshot;
        $receipt['ledger'] = $db->select('SELECT migration, batch FROM migrations ORDER BY id');
        file_put_contents($state . '/' . $prefix . 'columns.json', json_encode($db->select(
            'SELECT * FROM information_schema.columns WHERE table_schema = ? ORDER BY table_name, ordinal_position', [$schema]
        ), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        file_put_contents($state . '/' . $prefix . 'constraints.json', json_encode($db->select(
            'SELECT * FROM information_schema.table_constraints WHERE table_schema = ? ORDER BY table_name, constraint_name', [$schema]
        ), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        file_put_contents($state . '/' . $prefix . 'indexes.json', json_encode($db->select(
            'SELECT * FROM information_schema.statistics WHERE table_schema = ? ORDER BY table_name, index_name, seq_in_index', [$schema]
        ), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }
    } catch (Throwable $collectionError) {
        $receipt['status'] = 'BLOCKED';
        $receipt['collectionFailure'] = [
            'class' => get_class($collectionError), 'message' => $collectionError->getMessage(),
        ];
        echo 'STOP evidence collection: ' . $collectionError->getMessage() . "\n";
    }
    $persist();
}
exit($receipt['status'] === 'BLOCKED' ? 2 : 0);
