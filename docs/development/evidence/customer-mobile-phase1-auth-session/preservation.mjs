import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { execFileSync } from 'node:child_process';

const out = path.dirname(new URL(import.meta.url).pathname);
const sha = b => crypto.createHash('sha256').update(b).digest('hex');
const roots = ['customer_app', 'web', 'admin', 'backend'];
function sourceFiles(dir) {
  return fs.readdirSync(dir, { withFileTypes: true }).flatMap(e => {
    if (['node_modules', 'vendor', '.dart_tool', 'build', '.next', 'storage', '.git'].includes(e.name)) return [];
    const file = `${dir}/${e.name}`;
    if (e.isDirectory()) return sourceFiles(file);
    if (!e.isFile() || /\/database\/.*\.sqlite(?:-wal|-shm)?$/.test(file)) return [];
    return [[file, sha(fs.readFileSync(file))]];
  });
}
const sources = Object.fromEntries(roots.flatMap(r => sourceFiles(`.migration-backup/${r}`)));
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
$db->rollBack();
echo json_encode(['schemaSha256'=>hash('sha256', serialize($schema)), 'tables'=>$tables]);
`;
const snapshot = {
  sources,
  rootTrackedChanges: execFileSync('git', ['diff', '--name-only'], { encoding: 'utf8' }).trim().split('\n').filter(Boolean),
  database: JSON.parse(execFileSync('php', ['-r', php], { encoding: 'utf8', maxBuffer: 4e6 })),
};
const mode = process.argv[2];
if (!['before', 'after'].includes(mode)) throw new Error('Use before|after');
const target = `${out}/${mode}.json`;
if (mode === 'before' && fs.existsSync(target)) throw new Error('Never overwrite the before baseline');
fs.writeFileSync(target, JSON.stringify(snapshot, null, 2) + '\n');
if (mode === 'after') {
  const before = JSON.parse(fs.readFileSync(`${out}/before.json`));
  const changes = [...new Set([...Object.keys(before.sources), ...Object.keys(sources)])]
    .filter(p => before.sources[p] !== sources[p]);
  const unexpected = changes.filter(p => !p.startsWith('.migration-backup/customer_app/'));
  const databaseIdentical = JSON.stringify(before.database) === JSON.stringify(snapshot.database);
  const result = { changedFiles: changes, unexpectedNonMobileChanges: unexpected, databaseIdentical,
    tablesCompared: Object.keys(snapshot.database.tables).length };
  fs.writeFileSync(`${out}/preservation-result.json`, JSON.stringify(result, null, 2) + '\n');
  console.log(JSON.stringify(result));
  if (unexpected.length || !databaseIdentical) throw new Error('STOP: preservation failure');
} else console.log(JSON.stringify({ baselineFiles: Object.keys(sources).length, tables: Object.keys(snapshot.database.tables).length }));
