<?php
declare(strict_types=1);

function recoveryAssert(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
}
function recoveryWrite(string $path, mixed $value): void {
    recoveryAssert(!file_exists($path), 'Evidence overwrite refused: '.basename($path));
    file_put_contents($path, json_encode($value, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n", LOCK_EX);
    chmod($path, 0600);
}
function recoveryRoot(string $state, string $side): PDO {
    recoveryAssert(in_array($side, ['source','restore','restore-retry'], true), 'Unknown lab side');
    $dir = "$state/$side";
    recoveryAssert(realpath($dir) === $dir && !is_link($dir)
        && (fileperms($dir) & 0777) === 0700 && fileowner($dir) === posix_geteuid(), 'Private lab ownership required');
    $p = new PDO("mysql:unix_socket=$dir/mysql.sock;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_CASE=>PDO::CASE_LOWER, PDO::ATTR_STRINGIFY_FETCHES=>true,
    ]);
    $s = $p->query('SELECT @@version v,@@datadir d,@@skip_networking n,@@log_bin b,
        @@log_bin_trust_function_creators t,@@event_scheduler e,@@binlog_format f,
        @@transaction_isolation i,@@time_zone z')->fetch();
    recoveryAssert($s === ['v'=>'8.0.42','d'=>"$dir/data/",'n'=>'1','b'=>'1','t'=>'0',
        'e'=>'OFF','f'=>'ROW','i'=>'REPEATABLE-READ','z'=>'+00:00'], 'Owned native engine contract mismatch');
    recoveryAssert((int)$p->query("SELECT COUNT(*) FROM information_schema.plugins WHERE plugin_name='mysqlx' AND plugin_status='ACTIVE'")->fetchColumn() === 0, 'mysqlx must be off');
    $p->exec('USE agendaally_synthetic_recovery');
    return $p;
}
function recoveryFailedTree(string $state):array {
    $dir="$state/restore";$out=[];
    recoveryAssert(realpath($dir)===$dir&&!is_link($dir),'Original failed target custody mismatch');
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));
    foreach($it as $f) {
        recoveryAssert(!$f->isLink(),'Unexpected failed-target symlink');
        if($f->isFile())$out[substr($f->getPathname(),strlen($dir)+1)]=[
            'sha256'=>hash_file('sha256',$f->getPathname()),'bytes'=>$f->getSize(),
            'mode'=>fileperms($f->getPathname())&0777,'owner'=>fileowner($f->getPathname())];
    }
    ksort($out,SORT_STRING);return $out;
}
function recoverySource(string $root): array {
    $out=[];
    foreach (['original'=>"$root/.migration-backup",
        'frozen'=>"$root/.local/agendaally-clean-repository/.migration-backup/backend",
        'execution'=>"$root/.local/clean-publication-validation/.migration-backup/backend"] as $label=>$base) {
        recoveryAssert(is_dir($base), 'Required protected source absent');
        $it=new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
            static fn($f)=>!$f->isLink() && !in_array($f->getFilename(),
                ['vendor','node_modules','.next','.git','storage','cache','build','.dart_tool'],true)
                && !str_starts_with($f->getFilename(),'.env')));
        foreach ($it as $f) if ($f->isFile() && preg_match('/\.(php|json|lock|ts|tsx|js|jsx|dart|yaml|yml|css|scss|html|md)$/',$f->getFilename()))
            $out[$label][substr($f->getPathname(),strlen($base)+1)]=hash_file('sha256',$f->getPathname());
        ksort($out[$label],SORT_STRING);
    }
    foreach ($out['frozen'] as $name=>$hash) if (preg_match('#^(app/|config/|bootstrap/|database/|routes/|composer\.)#',$name))
        recoveryAssert(($out['execution'][$name]??null)===$hash, "Execution source differs: $name");
    return $out;
}
function recoveryManifest(string $base): array {
    $files=glob("$base/database/migrations/*.php"); sort($files,SORT_STRING);
    $rows=[];
    foreach ($files as $f) $rows[basename($f,'.php')]=hash_file('sha256',$f);
    $lines=[];
    foreach ($rows as $name=>$hash) $lines[]="$name.php $hash";
    recoveryAssert(count($rows)===229 && hash('sha256',implode("\n",$lines))==='37954929be028d7e487a7720f2108d3f300b2862af85f46b724b3c9f63d3f896', 'Frozen 229-file hash mismatch');
    return $rows;
}
function recoverySnapshot(PDO $p): array {
    $schema='agendaally_synthetic_recovery';
    $meta=static function(string $table,string $filter,string $order) use($p,$schema):array {
        $q=$p->prepare("SELECT * FROM information_schema.$table WHERE $filter=? ORDER BY $order");
        $q->execute([$schema]);return $q->fetchAll();
    };
    $p->exec('SET TRANSACTION READ ONLY');$p->beginTransaction();
    try {
        $out=['serialization'=>'PDO CASE_LOWER associative rows; all non-null scalar values fetched as strings; json_encode THROW only; byte-sort complete encoded rows (SORT_STRING), LF join without trailing LF, SHA256; repeatable-read read-only transaction with all lab writers quiescent','tables'=>[]];
        foreach ($meta('tables','table_schema','table_name') as $t) {
            recoveryAssert($t['engine']==='InnoDB','Only InnoDB consistent backups qualified');
            $name=$t['table_name'];recoveryAssert((bool)preg_match('/^[a-z0-9_]+$/D',$name),'Unsafe native identifier');
            $rows=$p->query("SELECT * FROM `$name`")->fetchAll();
            $strings=array_map(static fn($r)=>json_encode($r,JSON_THROW_ON_ERROR),$rows);sort($strings,SORT_STRING);
            $out['tables'][$name]=['ddl'=>array_values($p->query("SHOW CREATE TABLE `$name`")->fetch())[1],
                'rows'=>$rows,'count'=>count($rows),'sha256'=>hash('sha256',implode("\n",$strings))];
        }
        foreach ([
            'columns'=>['columns','table_schema','table_name,ordinal_position'],
            'indexes'=>['statistics','table_schema','table_name,index_name,seq_in_index'],
            'constraints'=>['table_constraints','table_schema','table_name,constraint_name'],
            'foreignKeys'=>['key_column_usage','table_schema','table_name,constraint_name,ordinal_position'],
            'referential'=>['referential_constraints','constraint_schema','table_name,constraint_name'],
            'checks'=>['check_constraints','constraint_schema','constraint_name'],
            'triggers'=>['triggers','trigger_schema','trigger_name'],
        ] as $key=>[$table,$filter,$order]) $out[$key]=$meta($table,$filter,$order);
        $out['triggerDdl']=[];
        foreach($out['triggers'] as $t) $out['triggerDdl'][$t['trigger_name']]=$p->query('SHOW CREATE TRIGGER `'.$t['trigger_name'].'`')->fetch();
        $out['ledger']=$p->query('SELECT * FROM migrations ORDER BY id')->fetchAll();
        $out['session']=$p->query('SELECT @@sql_mode sql_mode,@@time_zone timezone,@@transaction_isolation isolation_level,
            @@character_set_connection charset,@@collation_connection collation')->fetch();
        $out['grants']=[];
        foreach(['lab_bootstrap','lab_app'] as $u) $out['grants'][$u]=$p->query("SHOW GRANTS FOR '$u'@'localhost'")->fetchAll(PDO::FETCH_COLUMN);
        $out['accounts']=$p->query("SELECT user,host,account_locked,super_priv,grant_priv FROM mysql.user WHERE user IN ('lab_bootstrap','lab_app') ORDER BY user")->fetchAll();
        return $out;
    } finally {$p->rollBack();}
}
function recoveryDefiner(PDO $p,string $dir): void {
    $map=[];
    foreach ($p->query("SELECT * FROM information_schema.triggers WHERE trigger_schema='agendaally_synthetic_recovery' ORDER BY trigger_name")->fetchAll() as $t) {
        recoveryAssert($t['definer']==='lab_bootstrap@localhost','Unexpected definer');
        $body=$t['action_statement'];
        recoveryAssert(!preg_match('/\bSET\s+NEW\s*\.|\b(INSERT\s+INTO|DELETE\s+FROM|UPDATE\s+\w+\s+SET|CALL)\b/i',$body),'Unexpected trigger write authority');
        $map[$t['event_object_table']]['TRIGGER']=true;
        if(preg_match('/\b(OLD|NEW)\s*\./i',$body))$map[$t['event_object_table']]['SELECT']=true;
        preg_match_all('/\b(?:FROM|JOIN)\s+([a-z0-9_]+)/i',$body,$reads);
        foreach($reads[1] as $table)$map[$table]['SELECT']=true;
    }
    ksort($map,SORT_STRING);
    $sql="REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost';\nALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;\n";
    foreach($map as $table=>$privs)$sql.='GRANT '.implode(',',array_keys($privs))." ON `agendaally_synthetic_recovery`.`$table` TO 'lab_bootstrap'@'localhost';\n";
    // This is an operational cleanup instruction, not an evidence receipt.
    file_put_contents("$dir/revoke.sql",$sql);chmod("$dir/revoke.sql",0600);
}
function recoveryMetadataContract(array $s): array {
    $out=[];
    foreach(['columns','indexes','constraints','foreignKeys','referential','checks','triggers'] as $key) {
        $out[$key]=$s[$key];
        foreach($out[$key] as &$row) {
            if($key==='indexes')unset($row['cardinality']); // sampled optimizer statistics, not index definition
            if($key==='triggers')unset($row['created']); // import execution time, not trigger authority
        }unset($row);
    }
    $out['triggerDdl']=$s['triggerDdl'];
    foreach($out['triggerDdl'] as &$row)unset($row['created']);unset($row);
    return $out;
}
function recoveryPrivilegeContract(PDO $p,array $s,string $state,string $side):void {
    $schema='agendaally_synthetic_recovery';
    $expectedApp=['GRANT USAGE ON *.* TO `lab_app`@`localhost`',
        "GRANT SELECT, INSERT, UPDATE, DELETE ON `$schema`.* TO `lab_app`@`localhost`"];
    $actual=$s['grants']['lab_app'];sort($actual,SORT_STRING);sort($expectedApp,SORT_STRING);
    recoveryAssert($actual===$expectedApp,'Runtime grants must be exactly schema DML, not DDL/TRIGGER/SUPER');
    $map=[];
    foreach($s['triggers'] as $t) {
        recoveryAssert($t['definer']==='lab_bootstrap@localhost','Native trigger definer drift');
        $map[$t['event_object_table']]['TRIGGER']=true;
        if(preg_match('/\b(OLD|NEW)\s*\./i',$t['action_statement']))$map[$t['event_object_table']]['SELECT']=true;
        preg_match_all('/\b(?:FROM|JOIN)\s+([a-z0-9_]+)/i',$t['action_statement'],$reads);
        foreach($reads[1] as $r)$map[$r]['SELECT']=true;
    }
    $expected=['GRANT USAGE ON *.* TO `lab_bootstrap`@`localhost`'];
    foreach($map as $table=>$privs) {
        $list=array_values(array_filter(['SELECT','TRIGGER'],static fn($v)=>isset($privs[$v])));
        $expected[]='GRANT '.implode(', ',$list)." ON `$schema`.`$table` TO `lab_bootstrap`@`localhost`";
    }
    $actual=$s['grants']['lab_bootstrap'];sort($actual,SORT_STRING);sort($expected,SORT_STRING);
    recoveryAssert($actual===$expected,'Retained definer grants are not the exact table-scoped read/trigger authority');
    foreach($s['accounts'] as $r)recoveryAssert($r['super_priv']==='N'&&$r['grant_priv']==='N'
        &&$r['account_locked']===($r['user']==='lab_bootstrap'?'Y':'N'),'Retained account elevation/unlock');
    $rejected=false;
    try {new PDO("mysql:unix_socket=$state/$side/mysql.sock;charset=utf8mb4",'lab_bootstrap','',
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
    catch(PDOException $e){$rejected=($e->errorInfo[1]??null)===3118;}
    recoveryAssert($rejected,'Locked definer direct login must be rejected natively');
}
function recoveryEmittedTriggerContract(array $s,string $dir,bool $upgraded):void {
    $canonical=static function(string $sql):string {
        preg_match_all('/\'(?:\'\'|\\\\.|[^\'\\\\])*\'|"(?:\"\"|\\\\.|[^"\\\\])*"|`(?:``|[^`])*`|[^\s]+/s',trim($sql),$tokens);
        return implode(' ',$tokens[0]);
    };
    $expected=[];
    foreach($upgraded?['bootstrap','upgrade']:['bootstrap'] as $stage)
        foreach(file("$dir/$stage-ddl.jsonl",FILE_IGNORE_NEW_LINES) as $line) {
            $sql=json_decode($line,true,512,JSON_THROW_ON_ERROR)['sql'];
            if(preg_match('/^\s*CREATE TRIGGER\s+(\w+)\s+(BEFORE|AFTER)\s+(INSERT|UPDATE|DELETE)\s+ON\s+(\w+)\s+FOR EACH ROW\s+(.+)$/is',$sql,$m))
                $expected[$m[1]]=['action_timing'=>strtoupper($m[2]),'event_manipulation'=>strtoupper($m[3]),
                    'event_object_table'=>$m[4],'action_statement'=>$canonical($m[5])];
        }
    recoveryAssert(count($expected)===count($s['triggers'])&&count($expected)>0,'Installed/emitted trigger inventory differs');
    foreach($s['triggers'] as $t) {
        $actual=array_intersect_key($t,array_flip(['action_timing','event_manipulation','event_object_table','action_statement']));
        $actual['action_statement']=$canonical($actual['action_statement']);
        recoveryAssert(isset($expected[$t['trigger_name']])&&array_diff_assoc($expected[$t['trigger_name']],$actual)===[],
            'Installed trigger body differs from unchanged source SQL');
    }
    foreach($s['constraints'] as $c)if($c['constraint_type']==='CHECK')
        recoveryAssert($c['enforced']==='YES','Native CHECK is not enforced');
    $groups=[];
    foreach($s['foreignKeys'] as $fk)if($fk['referenced_table_name']!==null)$groups[$fk['table_name'].'|'.$fk['constraint_name']][]=$fk;
    foreach($groups as $fks) {
        $t=$fks[0]['table_name'];$want=array_column($fks,'column_name');$indexes=[];
        foreach($s['indexes'] as $index)if($index['table_name']===$t)$indexes[$index['index_name']][]=$index['column_name'];
        $supported=false;
        foreach($indexes as $cols)if(array_slice($cols,0,count($want))===$want)$supported=true;
        recoveryAssert($supported,"Native FK lacks a leading supporting index: $t");
    }
}
function recoveryInvariants(PDO $p): array {
    $get=static fn($sql)=>$p->query($sql)->fetchAll();
    return [
        'allocations'=>$get('SELECT id,gross_amount,commission_amount,adjustment_amount,vendor_entitlement_amount,
            original_platform_amount,original_vendor_direct_amount,original_commission_satisfied,
            original_commission_receivable,original_platform_adjustment,original_vendor_adjustment,original_vendor_payable
            FROM commerce_payment_allocations ORDER BY id'),
        'principalViolations'=>$get('SELECT id FROM commerce_payment_allocations WHERE gross_amount-commission_amount-adjustment_amount<>vendor_entitlement_amount'),
        'reservations'=>$get("SELECT allocation_id,kind,state,SUM(amount_units) units,COUNT(*) records FROM payment_financial_operations GROUP BY allocation_id,kind,state ORDER BY allocation_id,kind,state"),
        'operations'=>$get('SELECT * FROM payment_financial_operations ORDER BY id'),
        'contexts'=>$get('SELECT * FROM payment_collection_contexts ORDER BY id'),
        'receipts'=>$get('SELECT * FROM payment_receipt_evidence ORDER BY id'),
        'accounting'=>$get('SELECT * FROM platform_fee_ledger_entries ORDER BY id'),
        'wallets'=>$get('SELECT * FROM wallets ORDER BY id'),
        'walletHistory'=>$get('SELECT * FROM wallet_histories ORDER BY id'),
        'transactions'=>$get('SELECT * FROM transactions ORDER BY id'),
        'attempts'=>$get('SELECT * FROM electronic_collection_attempts ORDER BY id'),
        'mtn'=>$get('SELECT * FROM payment_process ORDER BY id'),
        'emails'=>$get('SELECT * FROM selected_email_deliveries ORDER BY id'),
        'grants'=>array_map($get,[
            'SELECT * FROM model_has_roles ORDER BY model_id,role_id',
            'SELECT * FROM model_has_permissions ORDER BY model_id,permission_id',
            'SELECT * FROM role_has_permissions ORDER BY role_id,permission_id',
            'SELECT * FROM country_role_permissions ORDER BY country_role_id,country_permission_id',
            'SELECT * FROM country_invitations ORDER BY id',
            'SELECT * FROM shop_role_permissions ORDER BY shop_role_id,shop_permission_id',
        ]),
    ];
}
