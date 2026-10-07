import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {execFileSync} from 'node:child_process';
const out = path.dirname(new URL(import.meta.url).pathname);
const sha = b => crypto.createHash('sha256').update(b).digest('hex');
function files(dir) {
  return fs.readdirSync(dir, {withFileTypes: true}).flatMap(e => {
    if (['node_modules','vendor','.dart_tool','build','.next','storage','.git'].includes(e.name)) return [];
    const p = `${dir}/${e.name}`;
    if (e.isDirectory()) return files(p);
    if (!e.isFile() || /\/database\/.*\.sqlite(?:-wal|-shm)?$/.test(p)) return [];
    return [[p, sha(fs.readFileSync(p))]];
  });
}
const sources = Object.fromEntries(['customer_app','web','admin','backend'].flatMap(r => files(`.migration-backup/${r}`)));
const php = String.raw`
$db = new PDO('sqlite:file:' . getcwd() . '/.migration-backup/backend/database/development/agendaally.sqlite?mode=ro');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA query_only=ON'); $db->beginTransaction();
$schema = $db->query("SELECT type,name,tbl_name,sql FROM sqlite_master WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name")->fetchAll(PDO::FETCH_ASSOC);
$tables = [];
foreach ($schema as $row) {
  if ($row['type'] !== 'table') continue;
  $name = $row['name']; $escaped = str_replace('"', '""', $name);
  $rows = $db->query('SELECT * FROM "' . $escaped . '" ORDER BY rowid')->fetchAll(PDO::FETCH_ASSOC);
  $tables[$name] = ['rows'=>count($rows), 'sha256'=>hash('sha256', serialize($rows))];
}
$db->rollBack(); echo json_encode(['schemaSha256'=>hash('sha256', serialize($schema)), 'tables'=>$tables]);
`;
const mode = process.argv[2];
if (!['before','after'].includes(mode)) throw Error('Use before|after');
const target = `${out}/${mode}.json`;
if (mode === 'before' && fs.existsSync(target)) throw Error('Never overwrite the fresh baseline');
const snapshot = {sources, database: JSON.parse(execFileSync('php', ['-r', php], {encoding:'utf8', maxBuffer:4e6}))};
fs.writeFileSync(target, JSON.stringify(snapshot, null, 2) + '\n');
if (mode === 'after') {
  const before = JSON.parse(fs.readFileSync(`${out}/before.json`));
  const changedFiles = [...new Set([...Object.keys(before.sources),...Object.keys(sources)])]
    .filter(p => before.sources[p] !== sources[p]);
  const result = {changedFiles, unexpectedNonMobileChanges: changedFiles.filter(p => !p.startsWith('.migration-backup/customer_app/')),
    databaseIdentical: JSON.stringify(before.database) === JSON.stringify(snapshot.database),
    tablesCompared: Object.keys(snapshot.database.tables).length};
  fs.writeFileSync(`${out}/preservation-result.json`, JSON.stringify(result, null, 2) + '\n');
  console.log(JSON.stringify(result));
  if (result.unexpectedNonMobileChanges.length || !result.databaseIdentical) throw Error('STOP: preservation failure');
} else console.log(JSON.stringify({baselineFiles:Object.keys(sources).length,tables:Object.keys(snapshot.database.tables).length}));
