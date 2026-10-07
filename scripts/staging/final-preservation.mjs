import fs from 'node:fs';
import {execFileSync} from 'node:child_process';
const root=process.cwd(),state=`${root}/.local/staging-mvp`;
const protectedState=JSON.parse(execFileSync('php',[`${root}/.local/booking-forward/protected-state.php`],{encoding:'utf8'}));
const nativeOperations=execFileSync('mysql',['--no-defaults',
  `--socket=${root}/.local/booking-forward/mysql-data/mysql.sock`,'-u','root','--batch',
  'agendaally_payment_disposable_booking_execution_2','-e',
  "SELECT id,state,amount_units FROM payment_financial_operations WHERE state IN('UNKNOWN','PENDING','CANCELED') ORDER BY id;"],{encoding:'utf8'});
const expected=fs.readFileSync(`${state}/retained-operations-before.tsv`,'utf8');
const normalDemo=JSON.parse(execFileSync('php',['-r',`
$pdo=new PDO('sqlite:file:'.getcwd().'/.migration-backup/backend/database/development/agendaally.sqlite?mode=ro');
$pdo->exec('PRAGMA query_only=ON');
$out=[];
foreach(['shops','services','service_masters']as$table)$out[$table]=(int)$pdo->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
$out['specialists']=(int)$pdo->query("SELECT COUNT(DISTINCT m.model_id) FROM model_has_roles m JOIN roles r ON r.id=m.role_id WHERE r.name='master'")->fetchColumn();
echo json_encode($out);
`],{encoding:'utf8'}));
const response=await fetch('http://127.0.0.1:8000/api/v1/rest/shops/paginate?perPage=100&lang=en');
const body=await response.json();
const result={
  scope:'Final read-only protected original, retained native operations and normal demo; no disposable API retarget',
  protectedState,normalDemo,normalApi:{status:response.status,shops:body.data?.length},
  checks:{
    all59FingerprintsMatch:protectedState.tables===59&&protectedState.changed.length===0,
    all465SchemaObjectsMatch:protectedState.schemaCount===465&&protectedState.schemaMatch,
    onlyApprovedAdditiveClientSaveSchema:protectedState.approvedLedgerMatch===true
      &&protectedState.approvedSchemaAdditions?.length===2
      &&protectedState.approvedSchemaAdditions.every(object=>object.tbl_name==='seller_client_save_intents'),
    legacy12RemainUnverified:protectedState.legacyOrders.total===12&&protectedState.legacyOrders.unverified===12,
    retainedNativeOperationsUnchanged:nativeOperations===expected,
    normalDemoCounts:normalDemo.shops===9&&normalDemo.services===51&&normalDemo.service_masters===75&&normalDemo.specialists===14,
    normalApiNotRetargeted:response.status===200&&body.data?.length===9,
  },
};
result.status=Object.values(result.checks).every(Boolean)?'PASS':'FAIL';
fs.writeFileSync(`${state}/final-preservation.json`,JSON.stringify(result,null,2));
console.log(JSON.stringify(result,null,2));
if(result.status==='FAIL')process.exitCode=1;