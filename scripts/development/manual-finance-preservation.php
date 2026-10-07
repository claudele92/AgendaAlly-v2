<?php
declare(strict_types=1);
// Read-only, exact baseline recipe; recognizes only the approved append-only delta.
$root=dirname(__DIR__,2);
$before=json_decode(file_get_contents($root.'/.local/manual-finance/baseline.json'),true,512,JSON_THROW_ON_ERROR);
$db=new PDO('sqlite:'.$root.'/.migration-backup/backend/database/development/agendaally.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only=ON');
$allowed=['country_permissions'=>12,'permissions'=>12,'email_templates'=>8,'migrations'=>1];
$results=[]; $unexpected=[];
foreach ($before['tables'] as $table=>$expected) {
    $rows=$db->query('SELECT * FROM "'.str_replace('"','""',$table).'" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
    $prefix=array_slice($rows,0,$expected['rows']);
    $same=hash('sha256',serialize($prefix))===$expected['sha256'];
    $delta=count($rows)-$expected['rows'];
    if (!$same || $delta!==($allowed[$table]??0)) $unexpected[]=$table;
    $results[$table]=['existing_rows_unchanged'=>$same,'added_rows'=>$delta];
}
$schema=$db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' AND tbl_name NOT LIKE 'manual_financial_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$schemaSame=hash('sha256',serialize($schema))===$before['schema_sha256'];
if (!$schemaSame) $unexpected[]='original_schema';
$new=$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE 'manual_financial_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$empty=[];
foreach ($new as $table) $empty[$table]=(int)$db->query('SELECT count(*) FROM "'.$table.'"')->fetchColumn();
if (count($new)!==7 || array_sum($empty)!==0) $unexpected[]='manual_table_boundary';
$jobs=(int)$db->query('SELECT count(*) FROM jobs')->fetchColumn();
$failed=(int)$db->query('SELECT count(*) FROM failed_jobs')->fetchColumn();
$facts=['recipe'=>$before['recipe'],'protected_original_schema_unchanged'=>$schemaSame,
    'original_tables'=>$results,'normal_manual_table_counts'=>$empty,'jobs'=>$jobs,'failed_jobs'=>$failed,
    'send_permission_absent'=>!file_exists($root.'/.local/staging-mvp/account-email-approval.json'),
    'unexpected_delta'=>$unexpected,'preserved'=>!$unexpected];
$name=$argv[1]??'final-preservation';
if (!preg_match('/^[a-z0-9-]{1,80}$/D',$name)) throw new RuntimeException('Invalid evidence checkpoint name.');
$path=$root.'/.local/manual-finance/'.$name.'.json';
if (file_exists($path)) throw new RuntimeException('Preservation evidence is immutable; do not overwrite.');
file_put_contents($path,json_encode($facts,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR)."\n",LOCK_EX);
chmod($path,0600);
echo json_encode(['original_tables_checked'=>count($results),'original_schema_unchanged'=>$schemaSame,
    'expected_appended_rows'=>$allowed,'new_tables'=>$empty,'jobs'=>$jobs,'failed_jobs'=>$failed,
    'unexpected_delta'=>$unexpected,'preserved'=>!$unexpected]),"\n";
if ($unexpected) exit(2);
