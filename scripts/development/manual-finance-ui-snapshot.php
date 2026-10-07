<?php
declare(strict_types=1);
// Read-only checkpoints for the explicitly disposable UI campaign.
if (PHP_SAPI !== 'cli') throw new RuntimeException('CLI-only fixture evidence.');
$root = dirname(__DIR__, 2);
$phase = $argv[1] ?? '';
if (!preg_match('/^[a-z0-9-]{1,80}$/D', $phase)) throw new RuntimeException('Named checkpoint required.');
$database = $root.'/.local/manual-finance/browser-ui-acceptance.sqlite';
if (!is_file($database)) throw new RuntimeException('Disposable fixture is absent.');
$db = new PDO('sqlite:'.$database);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only=ON');
$facts = ['scope'=>'disposable native UI fixture; synthetic auth, not Sanctum acceptance',
    'phase'=>$phase, 'captured_at'=>gmdate('c')];
foreach (['settings','manual_financial_workflows','manual_financial_commands','manual_financial_events',
    'manual_financial_evidence','manual_financial_notifications','payment_financial_operations',
    'platform_fee_ledger_entries'] as $table) {
    $facts[$table] = $db->query('SELECT * FROM "'.$table.'" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
}
$directory = $root.'/.local/manual-finance/ui-acceptance';
if (!is_dir($directory)) mkdir($directory, 0700, true);
$path = $directory.'/'.$phase.'.json';
$file = fopen($path, 'x');
if (!$file) throw new RuntimeException('Checkpoint already exists; never overwrite evidence.');
fwrite($file, json_encode($facts, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
fclose($file);
chmod($path, 0600);
echo $path,"\n";
