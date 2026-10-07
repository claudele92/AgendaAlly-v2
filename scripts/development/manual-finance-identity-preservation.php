<?php
declare(strict_types=1);
// Read-only fingerprints of CURRENT normal data, independent of older baselines.
if (PHP_SAPI!=='cli') throw new RuntimeException('CLI only.');
$root=dirname(__DIR__,2);
$phase=$argv[1]??'';
if (!preg_match('/^[a-z0-9-]{1,60}$/D',$phase)) throw new RuntimeException('Unique phase required.');
$path=$root.'/.local/manual-finance/identity-preservation-'.$phase.'.json';
if (file_exists($path)) throw new RuntimeException('Immutable checkpoint exists.');
$db=new PDO('sqlite:'.$root.'/.migration-backup/backend/database/development/agendaally.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only=ON');
$db->beginTransaction();
$schema=$db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$tables=[];
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $rows=$db->query('SELECT * FROM "'.str_replace('"','""',$table).'" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
    $tables[$table]=['rows'=>count($rows),'sha256'=>hash('sha256',serialize($rows))];
}
$db->commit();
$facts=['recipe'=>'PDO assoc / rowid order / PHP serialize SHA256 / all current tables and schema',
    'schema_sha256'=>hash('sha256',serialize($schema)),'tables'=>$tables];
$file=fopen($path,'xb'); fwrite($file,json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)); fclose($file); chmod($path,0600);
echo json_encode(['checkpoint'=>basename($path),'tables'=>count($tables)]),"\n";
