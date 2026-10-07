<?php
declare(strict_types=1);
require __DIR__.'/db-evidence.php';
function contracts(string $instance):array {
    $pdo=stagingRootPdo($instance);
    $columns=$pdo->query("SELECT TABLE_NAME,COLUMN_NAME,ORDINAL_POSITION,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,
        CHARACTER_SET_NAME,COLLATION_NAME,EXTRA,GENERATION_EXPRESSION FROM information_schema.columns
        WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
    $ddl=[];
    foreach($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN)as$table) {
        $create=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        // MySQL's dump parser may retain redundant per-column CHARACTER SET
        // when it is already implied by that column's explicitly identical COLLATE.
        $create=preg_replace('/CHARACTER SET utf8mb4 (?=COLLATE utf8mb4_[a-z0-9_]+)/','',$create);
        $ddl[$table]=hash('sha256',$create);
    }
    return ['columns'=>$columns,'canonicalDdl'=>$ddl];
}
$source=contracts('source');$restored=contracts('restore');
$selected=json_decode(file_get_contents(getcwd().'/.local/staging-mvp/r1-selected-point.json'),true,512,JSON_THROW_ON_ERROR);
$captured=json_decode(file_get_contents(getcwd().'/.local/staging-mvp/r1-restored.json'),true,512,JSON_THROW_ON_ERROR);
$rawDifferent=array_keys(array_filter($selected['schema'],fn($value,$table)=>$value!==$captured['schema'][$table],ARRAY_FILTER_USE_BOTH));
$equivalent=!$rawDifferent||(count($rawDifferent)===1&&$rawDifferent[0]==='staging_operational_probes'
    &&$source['canonicalDdl']['staging_operational_probes']===$restored['canonicalDdl']['staging_operational_probes']);
$out=['scope'=>'Strict restored column/DDL equivalence; only redundant utf8mb4 CHARACTER SET before identical COLLATE is canonicalized',
    'columnsMatch'=>$source['columns']===$restored['columns'],
    'canonicalDdlMatch'=>$equivalent,'rawDifferentTables'=>$rawDifferent,
    'columns'=>count($source['columns']),'tables'=>count($source['canonicalDdl'])];
$out['status']=$out['columnsMatch']&&$out['canonicalDdlMatch']?'PASS':'FAIL';
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);