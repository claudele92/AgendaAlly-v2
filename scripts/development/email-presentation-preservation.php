<?php
declare(strict_types=1);

// Read-only hashes/counts only. Never select the stored SMTP credential.
$root = dirname(__DIR__, 2);
$db = new PDO('sqlite:file:' . $root . '/.migration-backup/backend/database/development/agendaally.sqlite?mode=ro');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only=ON');
$db->beginTransaction();
$hashes = [];
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name") as $table) {
    $name = $table['name'];
    $quoted = '"' . str_replace('"', '""', $name) . '"';
    $columns = $name === 'email_settings'
        ? 'id,host,port,from_to,from_site,active,smtp_auth,smtp_debug,ssl,created_at,updated_at' : '*';
    $rows = $db->query("SELECT $columns FROM $quoted")->fetchAll(PDO::FETCH_ASSOC);
    // Freeze serialization/order explicitly, preserving every selected field.
    $encoded = array_map(fn ($row) => json_encode($row, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION), $rows);
    sort($encoded, SORT_STRING);
    $hashes[$name] = ['count' => count($rows), 'sha256' => hash('sha256', implode("\n", $encoded))];
}
$schema = $db->query("SELECT type,name,tbl_name,sql FROM sqlite_master ORDER BY type,name,tbl_name,sql")->fetchAll(PDO::FETCH_ASSOC);
$db->rollBack();
echo json_encode(['tables' => $hashes, 'schemaCount' => count($schema),
    'schemaSha256' => hash('sha256', json_encode($schema, JSON_THROW_ON_ERROR)),
    'codec' => 'JSON_PRESERVE_ZERO_FRACTION associative PDO rows, lexicographically sorted encoded rows, newline joined',
    'smtpCredential' => 'NOT SELECTED'], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
