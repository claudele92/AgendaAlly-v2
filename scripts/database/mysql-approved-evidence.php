<?php
declare(strict_types=1);
// Exact read-only native evidence; only --prepare-definer writes a local SQL
// cleanup file. It never executes financial statements or changes business rows.
$root = dirname(__DIR__, 2);
$state = $root . '/.local/mysql-approved-bootstrap-rehearsal';
$schema = 'agendaally_approved_empty_lab';
$prepare = ($argv[1] ?? '') === '--prepare-definer';
if (count($argv) !== ($prepare ? 2 : 1) || is_link($state) || realpath($state) !== $state
    || (fileperms($state) & 0777) !== 0700 || fileowner($state) !== posix_geteuid()) {
    throw new RuntimeException('Only the private owned approved lab is permitted.');
}
$owner = json_decode(file_get_contents($state . '/owner.json'), true, 512, JSON_THROW_ON_ERROR);
if (($owner['root'] ?? '') !== $root || ($owner['schemas'] ?? []) !== [$schema]
    || ($owner['policy'] ?? '') !== 'owner-approved-local-temporary-super-locked-definer') {
    throw new RuntimeException('Lab ownership/approval mismatch.');
}
$db = new PDO('mysql:unix_socket=' . $state . '/mysql.sock;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_CASE => PDO::CASE_LOWER,
]);
if ($db->query('SELECT @@datadir')->fetchColumn() !== $state . '/data/'
    || (int)$db->query('SELECT @@skip_networking')->fetchColumn() !== 1) {
    throw new RuntimeException('Wrong server; stop before any evidence work.');
}
$metadata = static function (string $sql) use ($db, $schema): array {
    $q = $db->prepare($sql); $q->execute([$schema]); return $q->fetchAll();
};
$triggers = $metadata('SELECT * FROM information_schema.triggers WHERE trigger_schema=?
    ORDER BY trigger_name');
$definerPrivileges = [];
foreach ($triggers as $trigger) {
    if ($trigger['definer'] !== 'lab_bootstrap@localhost') {
        throw new RuntimeException('Unexpected trigger definer.');
    }
    $table = $trigger['event_object_table'];
    if (!preg_match('/^[a-z0-9_]+$/D', $table)) throw new RuntimeException('Unsafe subject identifier.');
    $definerPrivileges[$table]['TRIGGER'] = true;
    $body = $trigger['action_statement'];
    // All approved native bodies only inspect OLD/NEW/SELECT and SIGNAL.
    if (preg_match('/\bSET\s+NEW\s*\.|\b(INSERT\s+INTO|DELETE\s+FROM|UPDATE\s+\w+\s+SET|CALL)\b/i', $body)) {
        throw new RuntimeException('Unreviewed definer write privilege required; stop.');
    }
    if (preg_match('/\b(OLD|NEW)\s*\./i', $body)) $definerPrivileges[$table]['SELECT'] = true;
    preg_match_all('/\b(?:FROM|JOIN)\s+([a-z0-9_]+)/i', $body, $reads);
    foreach ($reads[1] as $read) $definerPrivileges[$read]['SELECT'] = true;
}
ksort($definerPrivileges, SORT_STRING);
if ($prepare) {
    $sql = "REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost';\n"
        . "ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;\n";
    foreach ($definerPrivileges as $table => $privileges) {
        $sql .= 'GRANT ' . implode(',', array_keys($privileges))
            . " ON `$schema`.`$table` TO 'lab_bootstrap'@'localhost';\n";
    }
    file_put_contents($state . '/revoke.sql.tmp', $sql);
    rename($state . '/revoke.sql.tmp', $state . '/revoke.sql');
    file_put_contents($state . '/definer-contract.json',
        json_encode($definerPrivileges, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
    echo "Prepared exact table-level definer privileges and mandatory SUPER revocation/account lock.\n";
    exit(0);
}
$out = [
    'method' => 'Read-only repeatable-read native transaction; PDO lower-case associative keys; byte-sort JSON serialized rows, LF join SHA256; raw DDL and ordered native metadata retained',
    'server' => $db->query('SELECT @@version version,@@datadir datadir,@@skip_networking skip_networking,
        @@log_bin binary_logging,@@log_bin_trust_function_creators trust_function_creators,
        @@event_scheduler event_scheduler,@@transaction_isolation isolation_level,@@global.sql_mode sql_mode,
        @@global.time_zone timezone,@@binlog_format binlog_format')->fetch(),
    'tables' => [], 'triggers' => $triggers,
    'definerRequiredPrivileges' => $definerPrivileges,
    'priorEvidenceAvailable' => false,
];
$out['mysqlxActive'] = (int)$db->query("SELECT COUNT(*) FROM information_schema.plugins
    WHERE plugin_name='mysqlx' AND plugin_status='ACTIVE'")->fetchColumn();
$db->exec('SET TRANSACTION READ ONLY');
$db->beginTransaction();
foreach ($metadata('SELECT table_name,engine,table_collation FROM information_schema.tables
    WHERE table_schema=? ORDER BY table_name') as $table) {
    $name = $table['table_name'];
    $qualified = "`$schema`.`" . str_replace('`', '``', $name) . '`';
    $ddl = $db->query('SHOW CREATE TABLE ' . $qualified)->fetch();
    $strings = array_map(static fn($r) => json_encode($r, JSON_THROW_ON_ERROR),
        $db->query('SELECT * FROM ' . $qualified)->fetchAll());
    sort($strings, SORT_STRING);
    $out['tables'][$name] = $table + [
        'ddl' => array_values($ddl)[1], 'rows' => count($strings),
        'rowsSha256' => hash('sha256', implode("\n", $strings)),
    ];
}
$out['columns'] = $metadata('SELECT * FROM information_schema.columns WHERE table_schema=?
    ORDER BY table_name,ordinal_position');
$out['indexes'] = $metadata('SELECT * FROM information_schema.statistics WHERE table_schema=?
    ORDER BY table_name,index_name,seq_in_index');
$out['constraints'] = $metadata('SELECT * FROM information_schema.table_constraints WHERE table_schema=?
    ORDER BY table_name,constraint_name');
$out['foreignKeys'] = $metadata('SELECT * FROM information_schema.key_column_usage WHERE table_schema=?
    ORDER BY table_name,constraint_name,ordinal_position');
$out['checks'] = $metadata('SELECT * FROM information_schema.check_constraints WHERE constraint_schema=?
    ORDER BY constraint_name');
$out['ledger'] = isset($out['tables']['migrations'])
    ? $db->query("SELECT migration,batch FROM `$schema`.migrations ORDER BY id")->fetchAll() : [];
$out['triggerDdl'] = [];
foreach ($triggers as $trigger) {
    $out['triggerDdl'][$trigger['trigger_name']] = $db->query("SHOW CREATE TRIGGER `$schema`.`"
        . $trigger['trigger_name'] . '`')->fetch();
}
$db->rollBack();
foreach (['lab_app', 'lab_bootstrap'] as $user) {
    $out['grants'][$user] = $db->query("SHOW GRANTS FOR '$user'@'localhost'")->fetchAll(PDO::FETCH_COLUMN);
}
$out['accounts'] = $db->query("SELECT user,host,account_locked,super_priv,grant_priv FROM mysql.user
    WHERE user IN ('lab_app','lab_bootstrap') ORDER BY user")->fetchAll();
$out['binaryLogs'] = $db->query('SHOW BINARY LOGS')->fetchAll();
$out['lockedDefinerLoginRejected'] = false;
try {
    $unexpectedLogin = new PDO('mysql:unix_socket=' . $state . '/mysql.sock;charset=utf8mb4',
        'lab_bootstrap', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $unexpectedLogin = null;
} catch (PDOException $error) {
    $out['lockedDefinerLoginRejected'] = ($error->errorInfo[1] ?? null) === 3118;
}
$runtime = new PDO('mysql:unix_socket=' . $state . '/mysql.sock;dbname=' . $schema . ';charset=utf8mb4',
    'lab_app', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$runtime->exec('SET TRANSACTION READ ONLY');
$runtime->beginTransaction();
$out['runtimeIdentity'] = $runtime->query('SELECT CURRENT_USER()')->fetchColumn();
$out['runtimeLedgerCount'] = (int)$runtime->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
$runtime->rollBack();
$runtime = null;
file_put_contents($state . '/native-evidence.json', json_encode($out, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
$failures = [];
$check = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) $failures[] = $message;
};
$receipt = json_decode(file_get_contents($state . '/receipt.json'), true, 512, JSON_THROW_ON_ERROR);
$child = json_decode(file_get_contents($state . '/child-exit.json'), true, 512, JSON_THROW_ON_ERROR);
$files = glob($root . '/.local/agendaally-clean-repository/.migration-backup/backend/database/migrations/*.php');
sort($files, SORT_STRING);
$expectedLedger = array_map(static fn($file) => basename($file, '.php'), $files);
$check(count($files) === 229 && array_column($out['ledger'], 'migration') === $expectedLedger,
    'Full ordered 229-migration native ledger required.');
$check(($receipt['status'] ?? '') === 'DDL_ONLY_PASS' && ($receipt['failure'] ?? null) === null
    && !isset($receipt['collectionFailure']) && ($child['migrationChildExit'] ?? -1) === 0,
    'Receipt and actual migration child exit must both pass.');
$check($out['server']['version'] === '8.0.42' && (int)$out['server']['binary_logging'] === 1
    && (int)$out['server']['trust_function_creators'] === 0 && $out['server']['event_scheduler'] === 'OFF'
    && $out['server']['isolation_level'] === 'REPEATABLE-READ' && $out['server']['timezone'] === '+00:00'
    && $out['server']['binlog_format'] === 'ROW' && $out['mysqlxActive'] === 0
    && count($out['binaryLogs']) > 0, 'Frozen native server/binary-log contract required.');
foreach ($out['accounts'] as $account) {
    $check($account['super_priv'] === 'N' && $account['grant_priv'] === 'N', 'No retained SUPER/GRANT OPTION.');
    $check($account['host'] === 'localhost', 'Local identities only.');
    $check($account['account_locked'] === ($account['user'] === 'lab_bootstrap' ? 'Y' : 'N'),
        'Definer must be locked, application unlocked.');
}
$check(count($out['accounts']) === 2, 'Both approved identities must exist.');
$check($out['lockedDefinerLoginRejected'] && $out['runtimeIdentity'] === 'lab_app@localhost'
    && $out['runtimeLedgerCount'] === 229, 'Locked direct definer login and working read-only runtime access required.');
$appGrants = [
    "GRANT USAGE ON *.* TO `lab_app`@`localhost`",
    "GRANT SELECT, INSERT, UPDATE, DELETE ON `$schema`.* TO `lab_app`@`localhost`",
];
$check($out['grants']['lab_app'] === $appGrants, 'Exact least-privilege application grants required.');
$expectedGrants = ["GRANT USAGE ON *.* TO `lab_bootstrap`@`localhost`"];
foreach ($definerPrivileges as $table => $privileges) {
    $ordered = array_values(array_filter(['SELECT', 'TRIGGER'], static fn($p) => isset($privileges[$p])));
    $expectedGrants[] = 'GRANT ' . implode(', ', $ordered) . " ON `$schema`.`$table` TO `lab_bootstrap`@`localhost`";
}
$actualGrants = $out['grants']['lab_bootstrap'];
sort($expectedGrants, SORT_STRING); sort($actualGrants, SORT_STRING);
$check($actualGrants === $expectedGrants, 'Exact table-scoped retained definer grants required.');
// Compare installed bodies to the SQL actually emitted by unchanged source.
// Whitespace is normalized outside quoted literals only; content is not removed.
$canonical = static function (string $sql): string {
    preg_match_all('/\'(?:\'\'|\\\\.|[^\'\\\\])*\'|"(?:\"\"|\\\\.|[^"\\\\])*"|`(?:``|[^`])*`|[^\s]+/s', trim($sql), $tokens);
    return implode(' ', $tokens[0]);
};
$expectedTriggers = [];
foreach (file($state . '/native-ddl-statements.jsonl', FILE_IGNORE_NEW_LINES) as $line) {
    $sql = json_decode($line, true, 512, JSON_THROW_ON_ERROR)['sql'];
    if (preg_match('/^\s*CREATE TRIGGER\s+(\w+)\s+(BEFORE|AFTER)\s+(INSERT|UPDATE|DELETE)\s+ON\s+(\w+)\s+FOR EACH ROW\s+(.+)$/is', $sql, $match)) {
        $expectedTriggers[$match[1]] = [
            'action_timing' => strtoupper($match[2]), 'event_manipulation' => strtoupper($match[3]),
            'event_object_table' => $match[4], 'action_statement' => $canonical($match[5]),
        ];
    }
}
$check(count($expectedTriggers) > 0 && count($expectedTriggers) === count($triggers),
    'Complete emitted/installed native trigger inventory required.');
foreach ($triggers as $trigger) {
    $expected = $expectedTriggers[$trigger['trigger_name']] ?? null;
    $actual = array_intersect_key($trigger, array_flip(['action_timing', 'event_manipulation', 'event_object_table', 'action_statement']));
    $actual['action_statement'] = $canonical($actual['action_statement']);
    $check($expected !== null && array_diff_assoc($expected, $actual) === [],
        'Unchanged trigger body/timing/table required: ' . $trigger['trigger_name']);
}
foreach (['pcc_receipt_anchor_insert', 'pcc_receipt_anchor_update', 'mtn_attempt_coherent_insert',
    'mtn_attempt_coherent_update', 'mtn_attempt_identity_immutable', 'mtn_attempt_lifecycle', 'mtn_attempt_retain',
    'generic_attempt_binding', 'generic_payment_identity_immutable', 'manual_workflow_binding', 'manual_workflow_delete'] as $name) {
    $check(isset($expectedTriggers[$name]), 'Required native guard missing: ' . $name);
}
foreach ($out['constraints'] as $constraint) {
    if ($constraint['constraint_type'] === 'CHECK') {
        $check($constraint['enforced'] === 'YES', 'Native CHECK must be enforced: ' . $constraint['constraint_name']);
    }
}
foreach ($out['columns'] as $column) {
    if (str_ends_with($column['column_name'], '_units')) {
        $check($column['data_type'] === 'bigint' && !str_contains($column['column_type'], 'unsigned'),
            'Signed native BIGINT monetary units required: ' . $column['table_name'] . '.' . $column['column_name']);
    }
}
$columns = [];
foreach ($out['columns'] as $column) $columns[$column['table_name']][$column['column_name']] = $column;
$moneyColumns = [
    'commerce_payment_allocations' => ['gross_amount', 'commission_amount', 'vendor_entitlement_amount',
        'adjustment_amount', 'original_platform_amount', 'original_vendor_direct_amount',
        'original_commission_satisfied', 'original_commission_receivable', 'original_platform_adjustment',
        'original_vendor_adjustment', 'original_vendor_payable'],
    'payment_collection_contexts' => ['amount', 'receipt_total_amount', 'original_commission_share',
        'original_receivable_share', 'original_adjustment_share', 'original_vendor_entitlement_share'],
    'platform_fee_ledger_entries' => ['exact_amount'],
];
foreach ($moneyColumns as $table => $names) foreach ($names as $name) {
    $column = $columns[$table][$name] ?? [];
    $check(($column['data_type'] ?? '') === 'bigint' && !str_contains($column['column_type'] ?? '', 'unsigned'),
        'Exact signed native monetary column required: ' . $table . '.' . $name);
}
foreach (['commerce_payment_allocations', 'payment_collection_contexts'] as $table) {
    $id = $columns[$table]['id'] ?? [];
    $check(($id['column_type'] ?? '') === 'bigint unsigned'
        && ($id['extra'] ?? '') === 'auto_increment', 'Native unsigned AUTO_INCREMENT identity required: ' . $table);
}
$check(($columns['commerce_payment_allocations']['native_components']['data_type'] ?? '') === 'json'
    && ($columns['platform_fee_ledger_entries']['effect_data']['data_type'] ?? '') === 'json',
    'Native JSON evidence types must remain unchanged.');
$indexes = [];
foreach ($out['indexes'] as $index) {
    $indexes[$index['table_name']][$index['index_name']]['columns'][] = $index['column_name'];
    $indexes[$index['table_name']][$index['index_name']]['unique'] = (int)$index['non_unique'] === 0;
}
$uniques = [
    'commerce_payment_allocations' => [
        'cpa_origin_unique' => ['checkout_key','origin_type','origin_id','shop_id','purpose','obligation_key'],
        'cpa_payable_unique' => ['payable_type','payable_id','purpose','obligation_key'],
    ],
    'payment_collection_contexts' => [
        'pcc_funding_unique' => ['allocation_id','funding_key'], 'pcc_event_unique' => ['funding_event_key','allocation_id'],
        'pcc_slot_unique' => ['allocation_id','confirmed_slot'], 'pcc_receipt_unique' => ['receipt_claim_key'],
    ],
    'platform_fee_ledger_entries' => ['pfle_effect_unique' => ['allocation_id','effect_key']],
    'payment_process' => ['payment_process_identity_unique' => ['id'], 'mtn_attempt_event_unique' => ['mtn_funding_event_key']],
    'payment_payloads' => ['global_profile_payment_unique' => ['payment_id']],
    'electronic_collection_attempts' => ['eca_provider_reference_unique' => ['provider','provider_reference']],
    'payment_financial_operations' => [
        'financial_operation_request_unique' => ['allocation_id','kind','request_key'],
        'financial_operation_external_unique' => ['provider','external_reference'],
    ],
    'manual_financial_commands' => ['manual_command_identity' => ['actor_id','command_key']],
    'manual_financial_events' => ['manual_event_version' => ['workflow_id','version']],
    'manual_financial_evidence' => ['manual_attempt_evidence' => ['workflow_id','attempt']],
    'manual_financial_notifications' => ['manual_notification_intent' => ['event_id','recipient_id','template_type']],
];
foreach ($uniques as $table => $definitions) foreach ($definitions as $name => $names) {
    $index = $indexes[$table][$name] ?? [];
    $check(($index['unique'] ?? false) && ($index['columns'] ?? []) === $names,
        'Exact leading native uniqueness required: ' . $table . '.' . $name);
}
$foreignGroups = [];
foreach ($out['foreignKeys'] as $key) {
    if ($key['referenced_table_name'] === null) continue;
    $foreignGroups[$key['table_name']][$key['constraint_name']][] = $key['column_name'];
    $check(isset($columns[$key['referenced_table_name']][$key['referenced_column_name']]),
        'Native FK target must exist: ' . $key['constraint_name']);
}
foreach ($foreignGroups as $table => $groups) foreach ($groups as $name => $names) {
    $support = array_filter($indexes[$table] ?? [], static fn($index) =>
        array_slice($index['columns'], 0, count($names)) === $names);
    $check($support !== [], 'Leading InnoDB FK-supporting index required: ' . $table . '.' . $name);
}
foreach ($out['tables'] as $table) {
    $check($table['engine'] === 'InnoDB', 'Native InnoDB engine required: ' . $table['table_name']);
}
foreach (['users', 'shops', 'orders', 'bookings', 'wallets', 'countries', 'currencies', 'languages',
    'payments', 'payment_process', 'commerce_payment_allocations', 'payment_collection_contexts',
    'payment_merchant_revisions', 'electronic_collection_attempts', 'payment_financial_operations',
    'payment_receipt_evidence', 'manual_financial_workflows', 'selected_email_deliveries',
    'manual_financial_commands', 'manual_financial_events', 'manual_financial_attachments',
    'manual_financial_evidence', 'manual_financial_notifications', 'manual_financial_evidence_access',
    'platform_fee_ledger_entries', 'transactions', 'wallet_histories', 'payment_payloads',
    'model_has_roles', 'model_has_permissions'] as $table) {
    $check(isset($out['tables'][$table]) && $out['tables'][$table]['rows'] === 0,
        'No business/finance/reference/send/automatic user grant rows permitted: ' . $table);
}
$assessment = [
    'status' => $failures === [] ? 'DDL_ONLY_PASS' : 'BLOCKED',
    'failures' => $failures, 'migrations' => count($out['ledger']),
    'tables' => count($out['tables']), 'triggers' => count($triggers),
    'checks' => count($out['checks']), 'actualMigrationChildExit' => $child['migrationChildExit'],
    'foreignKeyConstraints' => array_sum(array_map('count', $foreignGroups)),
    'requiredNamedUniqueIndexes' => array_sum(array_map('count', $uniques)),
    'binaryLogging' => 'ON', 'trustedCreators' => 'OFF',
    'elevatedPrivilegesRevoked' => $actualGrants === $expectedGrants,
    'triggerGuardVerification' => 'Native definitions compared with emitted unchanged source; no financial operations executed',
    'priorEvidence' => 'UNAVAILABLE; historical files/partial schemas not reconstructed or replayed',
    'referenceAdminKeyInitialization' => 'NOT EXECUTED',
    'populatedUpgradeRestore' => 'NOT EXECUTED', 'production' => 'NO-GO',
];
file_put_contents($state . '/assessment.json', json_encode($assessment, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
echo json_encode($assessment, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
exit($failures === [] ? 0 : 2);
