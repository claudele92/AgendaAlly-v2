<?php
declare(strict_types=1);
require __DIR__.'/runtime.php';
$app=stagingApplication();$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$checks=[];
try {DB::statement('CREATE TABLE forbidden_app_ddl_probe (id int)');$checks['applicationDdlDenied']=false;}
catch(Illuminate\Database\QueryException $e) {$checks['applicationDdlDenied']=in_array($e->errorInfo[1]??null,[1044,1142],true);}
$connection=DB::selectOne('SELECT CURRENT_USER() principal,@@character_set_connection charset,@@collation_connection collation,@@transaction_isolation isolation_level,@@session.time_zone time_zone,@@sql_mode sql_mode');
$checks['nonRootApplicationUser']=str_starts_with($connection->principal,'agendaally_app@');
$checks['repeatableRead']=$connection->isolation_level==='REPEATABLE-READ';
$checks['strictSql']=str_contains($connection->sql_mode,'STRICT_TRANS_TABLES');
$checks['charset']=$connection->charset==='utf8mb4';
$checks['collation']=$connection->collation==='utf8mb4_unicode_ci';
$checks['timezone']=$connection->time_zone==='+00:00';
$checks['debugDisabled']=config('app.debug')===false;
$checks['secureSessionFlags']=config('session.secure')===true&&config('session.http_only')===true&&config('session.same_site')==='lax';
$extensions=['bcmath','ctype','curl','dom','fileinfo','gd','intl','json','mbstring','openssl','pdo_mysql','tokenizer','xml','zip'];
$checks['requiredPhpExtensions']=!array_filter($extensions,fn($extension)=>!extension_loaded($extension));
$engines=DB::select("SELECT DISTINCT ENGINE FROM information_schema.tables WHERE table_schema=DATABASE()");
$checks['innodbOnly']=count($engines)===1&&$engines[0]->ENGINE==='InnoDB';
$out=['checks'=>$checks,'connection'=>$connection,'phpVersion'=>PHP_VERSION,'environment'=>$app->environment(),
    'privilegeModel'=>'Isolated Unix-socket bootstrap/root authority for schema import; runtime DML only, no root login',
    'sessionBoundary'=>'Native selected API is bearer-authenticated; public session config evidence does not certify unexercised cookie/CSRF endpoints',
    'status'=>!in_array(false,$checks,true)?'PASS':'FAIL'];
echo json_encode($out,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL;
exit($out['status']==='PASS'?0:1);