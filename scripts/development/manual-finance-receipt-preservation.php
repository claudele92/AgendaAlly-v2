<?php
declare(strict_types=1);
// Compare this campaign's exact normal-data fingerprints, independent of old drift.
if (PHP_SAPI!=='cli') throw new RuntimeException('Read-only CLI only.');
$root=dirname(__DIR__,2); $directory=$argv[1]??''; $phase=$argv[2]??'';
if (!preg_match('~^'.preg_quote($root,'~').'/\.local/manual-finance/http-identity-[a-f0-9]{16}$~D',$directory)
    || !is_dir($directory) || !in_array($phase,['before','after'],true)) throw new RuntimeException('Owned campaign and phase required.');
$db=new PDO('sqlite:'.$root.'/.migration-backup/backend/database/development/agendaally.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $db->exec('PRAGMA query_only=ON');
$schema=$db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$tables=[];
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $rows=$db->query('SELECT * FROM "'.str_replace('"','""',$table).'" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
    $tables[$table]=['rows'=>count($rows),'sha256'=>hash('sha256',serialize($rows))];
}
$facts=['recipe'=>'All normal rows in rowid order, PDO associative values serialized without normalization; all original schema SQL',
    'schema_sha256'=>hash('sha256',serialize($schema)),'tables'=>$tables];
if ($phase==='after') {
    $before=json_decode(file_get_contents($directory.'/normal-before.json'),true,512,JSON_THROW_ON_ERROR);
    $facts['unchanged']=$facts===$before;
    $facts['changed_tables']=array_keys(array_filter($tables,fn($value,$key)=>$value!==($before['tables'][$key]??null),ARRAY_FILTER_USE_BOTH));
}
$path=$directory.'/normal-'.$phase.'.json';
if (file_exists($path)) throw new RuntimeException('Do not overwrite preservation evidence.');
file_put_contents($path,json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n",LOCK_EX); chmod($path,0600);
echo json_encode(['tables'=>count($tables),'phase'=>$phase,'unchanged'=>$facts['unchanged']??null,
    'changed_tables'=>$facts['changed_tables']??[]]),"\n";
if ($phase==='after' && !$facts['unchanged']) exit(2);
