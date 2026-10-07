<?php
declare(strict_types=1);
// Read-only evidence for precisely the two owned synthetic schemas.
$root = dirname(__DIR__, 2);
$state = $root . '/.local/mysql-bootstrap-rehearsal';
if (count($argv) !== 1 || is_link($state) || realpath($state) !== $state) {
    throw new RuntimeException('Only the owned local lab may be inspected.');
}
$owner = json_decode(file_get_contents($state . '/owner.json'), true, 512, JSON_THROW_ON_ERROR);
if (($owner['root'] ?? '') !== $root) throw new RuntimeException('Ownership mismatch.');
$db = new PDO('mysql:unix_socket=' . $state . '/mysql.sock;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
if ($db->query('SELECT @@datadir')->fetchColumn() !== $state . '/data/'
    || (int)$db->query('SELECT @@skip_networking')->fetchColumn() !== 1) {
    throw new RuntimeException('Wrong server.');
}
$metadata = static function (string $sql, string $schema) use ($db): array {
    $q = $db->prepare($sql); $q->execute([$schema]); return $q->fetchAll();
};
$out = [
    'method' => 'Consistent read-only transaction; PDO FETCH_ASSOC; JSON THROW only; byte-sort serialized rows; LF join SHA256; raw ordered native metadata and SHOW CREATE retained',
    'server' => $db->query('SELECT @@version version, @@skip_networking skip_networking,
        @@log_bin binary_logging, @@log_bin_trust_function_creators trust_function_creators,
        @@event_scheduler event_scheduler, @@transaction_isolation isolation_level,
        @@global.sql_mode sql_mode, @@global.time_zone timezone,
        @@innodb_ft_cache_size ft_cache_bytes, @@innodb_ft_total_cache_size ft_total_cache_bytes')->fetch(),
    'schemas' => [],
];
foreach (['agendaally_empty_bootstrap_lab', 'agendaally_empty_bootstrap_retry_lab'] as $schema) {
    if (!in_array($schema, $owner['schemas'] ?? [], true)) throw new RuntimeException('Unowned schema.');
    $db->exec('SET TRANSACTION READ ONLY');
    $db->beginTransaction();
    $tables = $metadata('SELECT table_name, engine, table_collation FROM information_schema.tables
        WHERE table_schema=? ORDER BY table_name', $schema);
    $snapshot = ['tables' => [], 'columns' => [], 'indexes' => [], 'constraints' => [], 'triggers' => []];
    foreach ($tables as $table) {
        $name = $table['TABLE_NAME'];
        $qualified = "`$schema`.`" . str_replace('`', '``', $name) . '`';
        $ddl = $db->query('SHOW CREATE TABLE ' . $qualified)->fetch();
        $strings = array_map(static fn($r) => json_encode($r, JSON_THROW_ON_ERROR),
            $db->query('SELECT * FROM ' . $qualified)->fetchAll());
        sort($strings, SORT_STRING);
        $snapshot['tables'][$name] = [
            'engine' => $table['ENGINE'], 'collation' => $table['TABLE_COLLATION'],
            'ddl' => array_values($ddl)[1], 'count' => count($strings),
            'rowsSha256' => hash('sha256', implode("\n", $strings)),
        ];
    }
    $snapshot['columns'] = $metadata('SELECT * FROM information_schema.columns WHERE table_schema=?
        ORDER BY table_name, ordinal_position', $schema);
    $snapshot['indexes'] = $metadata('SELECT * FROM information_schema.statistics WHERE table_schema=?
        ORDER BY table_name, index_name, seq_in_index', $schema);
    $snapshot['constraints'] = $metadata('SELECT * FROM information_schema.table_constraints WHERE table_schema=?
        ORDER BY table_name, constraint_name', $schema);
    $snapshot['foreignKeys'] = $metadata('SELECT * FROM information_schema.key_column_usage WHERE table_schema=?
        ORDER BY table_name, constraint_name, ordinal_position', $schema);
    $snapshot['checks'] = $metadata('SELECT * FROM information_schema.check_constraints WHERE constraint_schema=?
        ORDER BY constraint_name', $schema);
    $snapshot['triggers'] = $metadata('SELECT * FROM information_schema.triggers WHERE trigger_schema=?
        ORDER BY event_object_table, trigger_name', $schema);
    $snapshot['ledger'] = $db->query("SELECT migration,batch FROM `$schema`.migrations ORDER BY id")->fetchAll();
    $db->rollBack();
    $out['schemas'][$schema] = $snapshot;
}
foreach (['lab_app', 'lab_migrator'] as $user) {
    $out['grants'][$user] = $db->query("SHOW GRANTS FOR '$user'@'localhost'")->fetchAll(PDO::FETCH_COLUMN);
}
file_put_contents($state . '/native-evidence.json', json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
echo "Retained exact native metadata and row fingerprints for two synthetic lab schemas.\n";
