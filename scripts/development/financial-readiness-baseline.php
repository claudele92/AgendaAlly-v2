<?php
declare(strict_types=1);

// Standalone read-only freeze: no Laravel bootstrap, transport or business action.
$root = dirname(__DIR__, 2);
$directory = $root.'/.local/financial-readiness';
if (isset($argv[2])) {
    if ($argv[2] !== '.local/manual-finance') throw new RuntimeException('Only the owned manual campaign evidence directory is permitted.');
    $directory = $root.'/'.$argv[2];
}
$mode = $argv[1] ?? '';
if (!in_array($mode, ['freeze','compare'], true)) throw new RuntimeException('Use freeze or compare.');
$dbPath = realpath($root.'/.migration-backup/backend/database/development/agendaally.sqlite');
if (!$dbPath || is_link($root.'/.migration-backup/backend/database/development/agendaally.sqlite')) {
    throw new RuntimeException('Owned baseline database unavailable.');
}
$db = new PDO('sqlite:file:'.$dbPath.'?mode=ro', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA query_only=ON');
$db->beginTransaction();
$schema = $db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$tables = [];
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $name) {
    $rows = $db->query('SELECT * FROM "'.str_replace('"','""',$name).'" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
    $tables[$name] = ['rows' => count($rows), 'sha256' => hash('sha256', serialize($rows))];
    unset($rows); // Only opaque digests retained; never decrypt/display credentials.
}
$mail = $db->query('SELECT kind,state,COUNT(*) AS count FROM selected_email_deliveries GROUP BY kind,state ORDER BY kind,state')->fetchAll(PDO::FETCH_ASSOC);
$templates = $db->query('SELECT type,COUNT(*) AS count FROM email_templates GROUP BY type ORDER BY type')->fetchAll(PDO::FETCH_ASSOC);
$db->commit();
$source = [];
foreach (['app','routes','config','database/migrations'] as $part) {
    $base = $root.'/.migration-backup/backend/'.$part;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)) as $file) {
        if (!$file->isFile() || $file->isLink() || $file->getExtension() !== 'php') continue;
        $relative = substr($file->getPathname(), strlen($root) + 1);
        $source[$relative] = hash_file('sha256',$file->getPathname());
    }
}
ksort($source);
$facts = [
    'at' => gmdate('c'), 'database' => '.migration-backup/backend/database/development/agendaally.sqlite',
    'read_only' => true, 'recipe' => 'PDO FETCH_ASSOC; table rows ORDER BY rowid; PHP serialize; SHA256. Schema excludes sqlite_% and orders type,name.',
    'schema_objects' => count($schema), 'schema_sha256' => hash('sha256',serialize($schema)),
    'tables' => $tables, 'application_source_sha256' => $source,
    'score' => ['C1'=>0.5,'O4'=>0.5,'total'=>16,'maximum'=>20,'percent'=>80],
    'production' => 'NO-GO; no approval', 'account_email' => 'Accepted; closed; no repeated login/reset/verification/SMTP test authorized.',
    'outbox' => $mail, 'managed_template_type_counts' => $templates,
    'send_permission_absent' => !file_exists($root.'/.local/staging-mvp/account-email-approval.json'),
    'accepted_reset_evidence' => '.local/staging-mvp/password-reset-phase-b-login-acceptance.json',
    'accepted_logout_evidence' => '.local/staging-mvp/account-login-logout-checkpoint.json; docs/development/customer-logout-repair.md',
    'financial_configuration_policy' => 'Keep existing environment/provider/collection gates. No credential retrieval, provider enablement or monetary action.',
    'known_deferred' => ['C1 server-logout assurance','O4 selected operational supervision/recovery','optional Admin CRUD UNVERIFIED','Product repeat-credit known P0 status review','Refund/Payout implementation and provider automation'],
];
if ($mode === 'compare') {
    $before = json_decode(file_get_contents($directory.'/baseline.json'),true,512,JSON_THROW_ON_ERROR);
    $changed = [];
    foreach ($before['tables'] as $name=>$fingerprint) if (($tables[$name] ?? null) !== $fingerprint) $changed[]=$name;
    $new = array_values(array_diff(array_keys($tables),array_keys($before['tables'])));
    $appChanged = [];
    foreach ($before['application_source_sha256'] as $path=>$fingerprint) {
        if (($source[$path] ?? null) !== $fingerprint) $appChanged[]=$path;
    }
    $newSource = array_values(array_diff(array_keys($source),array_keys($before['application_source_sha256'])));
    $facts['comparison'] = ['schema_unchanged'=>$facts['schema_sha256']===$before['schema_sha256'],
        'changed_tables'=>$changed,'new_tables'=>$new,'application_source_changes'=>$appChanged,'new_application_sources'=>$newSource];
    $facts['comparison']['preserved'] = $facts['comparison']['schema_unchanged'] && !$changed && !$new && !$appChanged && !$newSource;
}
if (!is_dir($directory) && !mkdir($directory,0700,true)) throw new RuntimeException('Evidence directory unavailable.');
$path = $directory.'/'.($mode==='freeze'?'baseline.json':'preservation-'.gmdate('Ymd-His').'.json');
if (file_exists($path)||is_link($path)) throw new RuntimeException('Frozen evidence already exists; never overwrite.');
$handle=fopen($path,'xb');
if (!$handle||!chmod($path,0600)) throw new RuntimeException('Private evidence unavailable.');
$content=json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n";
if (fwrite($handle,$content)!==strlen($content)||!fflush($handle)||!fsync($handle)) throw new RuntimeException('Evidence persistence failed.');
fclose($handle);
echo json_encode(['mode'=>$mode,'schema_objects'=>$facts['schema_objects'],'tables'=>count($tables),
    'outbox'=>$mail,'jobs'=>$tables['jobs']['rows'],'failed_jobs'=>$tables['failed_jobs']['rows'],
    'template_types'=>$templates,'send_permission_absent'=>$facts['send_permission_absent'],
    'score'=>'16/20=80%','comparison'=>$facts['comparison']??null]),"\n";
