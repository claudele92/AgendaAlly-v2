<?php
declare(strict_types=1);
// Redacted deterministic native MySQL evidence; no connection credentials emitted.
function stagingRootPdo(string $instance='source'):PDO {
    if(!in_array($instance,['source','restore'],true))throw new RuntimeException('Unapproved isolated instance');
    $base=getcwd().'/.local/staging-mvp';
    $socket=$instance==='source'?$base.'/mysql/mysql.sock':$base.'/restore/mysql/mysql.sock';
    return new PDO('mysql:unix_socket='.$socket.';dbname=agendaally_staging_mvp;charset=utf8mb4','root','',[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_STRINGIFY_FETCHES=>true]);
}
function stagingFingerprints(PDO $pdo):array {
    $rows=[];$schema=[];
    $tables=$pdo->query("SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.tables WHERE table_schema=DATABASE() ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
    foreach($tables as $entry) {
        $table=$entry['TABLE_NAME'];
        $data=$pdo->query('SELECT * FROM `'.str_replace('`','``',$table).'`')->fetchAll(PDO::FETCH_ASSOC);
        $serialized=array_map(static fn($r)=>json_encode($r,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRESERVE_ZERO_FRACTION|JSON_THROW_ON_ERROR),$data);
        sort($serialized,SORT_STRING);
        $rows[$table]=['count'=>count($data),'sha256'=>hash('sha256',implode("\n",$serialized))];
        $create=$pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $schema[$table]=hash('sha256',$create);
    }
    $triggers=$pdo->query("SELECT TRIGGER_NAME,EVENT_MANIPULATION,EVENT_OBJECT_TABLE,ACTION_STATEMENT,ACTION_TIMING FROM information_schema.triggers WHERE TRIGGER_SCHEMA=DATABASE() ORDER BY TRIGGER_NAME")->fetchAll(PDO::FETCH_ASSOC);
    $indexes=$pdo->query("SELECT TABLE_NAME,INDEX_NAME,NON_UNIQUE,SEQ_IN_INDEX,COLUMN_NAME,INDEX_TYPE FROM information_schema.statistics WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX")->fetchAll(PDO::FETCH_ASSOC);
    $constraints=$pdo->query("SELECT TABLE_NAME,CONSTRAINT_NAME,CONSTRAINT_TYPE FROM information_schema.table_constraints WHERE CONSTRAINT_SCHEMA=DATABASE() ORDER BY TABLE_NAME,CONSTRAINT_NAME")->fetchAll(PDO::FETCH_ASSOC);
    return ['tables'=>$rows,'schema'=>$schema,'tableProperties'=>$tables,
        'triggers'=>['count'=>count($triggers),'sha256'=>hash('sha256',json_encode($triggers,JSON_THROW_ON_ERROR))],
        'indexes'=>['count'=>count($indexes),'sha256'=>hash('sha256',json_encode($indexes,JSON_THROW_ON_ERROR))],
        'constraints'=>['count'=>count($constraints),'sha256'=>hash('sha256',json_encode($constraints,JSON_THROW_ON_ERROR))]];
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__)
    echo json_encode(stagingFingerprints(stagingRootPdo($argv[1]??'source')),JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;