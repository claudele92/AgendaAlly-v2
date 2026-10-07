<?php
declare(strict_types=1);

// Read-only verification against the normal owned SQLite file. Evidence holds
// counts and hashes only: never decrypted credentials, recipients or challenges.
$root = dirname(__DIR__, 2);
$path = realpath($root.'/.migration-backup/backend/database/development/agendaally.sqlite');
if (!$path) throw new RuntimeException('Normal owned database unavailable.');
$db = new PDO('sqlite:file:'.$path.'?mode=ro');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only = ON');
$quote = static fn (string $name): string => '"'.str_replace('"', '""', $name).'"';
$schema = $db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$result = ['database' => 'normal-owned-sqlite', 'schema_sha256' => hash('sha256', serialize($schema)), 'tables' => []];
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $columns = $db->query('PRAGMA table_info('.$quote($table).')')->fetchAll(PDO::FETCH_ASSOC);
    $primary = array_filter($columns, static fn (array $column): bool => $column['pk'] > 0);
    usort($primary, static fn (array $a, array $b): int => $a['pk'] <=> $b['pk']);
    $order = $primary ? implode(',', array_map(static fn (array $column): string => $quote($column['name']), $primary)) : 'rowid';
    $hash = hash_init('sha256'); $count = 0;
    foreach ($db->query('SELECT * FROM '.$quote($table).' ORDER BY '.$order) as $row) {
        // PDO defaults to BOTH: retain one named copy and native scalar types.
        $named = array_filter($row, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
        hash_update($hash, serialize($named)); $count++;
    }
    $result['tables'][$table] = ['rows' => $count, 'sha256' => hash_final($hash)];
}
$result['delivery_states'] = $db->query('SELECT state,COUNT(*) count FROM selected_email_deliveries GROUP BY state ORDER BY state')->fetchAll(PDO::FETCH_ASSOC);
$evidence = $root.'/.local/staging-mvp/account-template-before-fingerprints.json';
if (($argv[1] ?? '') === 'before') {
    if (is_file($evidence)) throw new RuntimeException('Before evidence already exists; do not overwrite.');
    file_put_contents($evidence, json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
    chmod($evidence, 0600);
    echo json_encode(['before_captured' => true, 'schema_sha256' => $result['schema_sha256'],
        'template_rows' => $result['tables']['email_templates']['rows'],
        'jobs' => $result['tables']['jobs']['rows'], 'failed_jobs' => $result['tables']['failed_jobs']['rows']]), "\n";
} elseif (($argv[1] ?? '') === 'after') {
    $before = json_decode(file_get_contents($evidence), true, 512, JSON_THROW_ON_ERROR);
    $changed = [];
    foreach ($result['tables'] as $table => $fingerprint) {
        if ($fingerprint !== ($before['tables'][$table] ?? null)) $changed[] = $table;
    }
    $expected = ['email_templates', 'migrations']; sort($changed);
    $pass = $before['schema_sha256'] === $result['schema_sha256']
        && $changed === $expected && $result['tables']['email_templates']['rows'] === 2
        && $result['tables']['migrations']['rows'] === $before['tables']['migrations']['rows'] + 1
        && $result['tables']['jobs']['rows'] === 0 && $result['tables']['failed_jobs']['rows'] === 0
        && $before['delivery_states'] === $result['delivery_states'];
    $proof = ['pass' => $pass, 'schema_unchanged' => $before['schema_sha256'] === $result['schema_sha256'],
        'changed_tables' => $changed, 'after' => $result];
    file_put_contents($root.'/.local/staging-mvp/account-template-after-fingerprints.json',
        json_encode($proof, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
    echo json_encode(['pass' => $pass, 'schema_unchanged' => $proof['schema_unchanged'],
        'changed_tables' => $changed, 'template_rows' => $result['tables']['email_templates']['rows'],
        'jobs' => $result['tables']['jobs']['rows'], 'failed_jobs' => $result['tables']['failed_jobs']['rows'],
        'delivery_states' => $result['delivery_states']]), "\n";
    if (!$pass) exit(1);
} else {
    throw new RuntimeException('Choose before or after.');
}
