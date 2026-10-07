import { spawn } from 'node:child_process';
import { createRequire } from 'node:module';
import dotenv from 'dotenv';

// Native environment configuration, independent of Replit or preview adapters.
// Existing shell/deployment values take precedence over private dotenv files.
const mode = process.argv[2] || 'dev';
if (!['dev', 'start'].includes(mode)) throw new Error('Server mode must be dev or start.');
const environment = mode === 'dev' ? 'development' : 'production';
for (const filename of [`.env.${environment}.local`, '.env.local', `.env.${environment}`, '.env']) {
  dotenv.config({ path: filename, override: false });
}
const port = Number(process.env.PORT || 3002);
if (!Number.isInteger(port) || port < 1 || port > 65535) throw new Error('Invalid PORT.');
const host = process.env.HOST || '0.0.0.0';
// Dev webpack retains modules as pages are visited. Keep this preview bounded;
// production startup is intentionally unaffected. Larger local machines can
// explicitly raise the development budget without changing the source.
const developmentHeapMb = Number(process.env.AGENDAALLY_DEV_HEAP_MB || 2048);
if (mode === 'dev' && (!Number.isInteger(developmentHeapMb) || developmentHeapMb < 1024)) {
  throw new Error('AGENDAALLY_DEV_HEAP_MB must be an integer of at least 1024.');
}
const require = createRequire(import.meta.url);
const child = spawn(process.execPath, [
  ...(mode === 'dev' ? [`--max-old-space-size=${developmentHeapMb}`] : []),
  require.resolve('next/dist/bin/next'), mode, '--hostname', host, '--port', String(port),
  ...process.argv.slice(3),
], { stdio: 'inherit', env: process.env });
process.on('SIGINT', () => child.kill('SIGINT'));
process.on('SIGTERM', () => child.kill('SIGTERM'));
child.once('error', error => {
  console.error(`Next server could not start: ${error.message}`);
  process.exitCode = 1;
});
child.once('exit', code => { process.exitCode = code ?? 1; });