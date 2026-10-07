import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import {spawn} from 'node:child_process';
const root=process.cwd(),state=path.join(root,'.local/staging-mvp');
const label=process.argv[2]||'foundation';
const only=process.argv[3];
const adminHeap=Number(process.argv[4]||4096);
if(!Number.isInteger(adminHeap)||adminHeap<2048||adminHeap>4096)throw Error('Invalid bounded admin build heap');
if(only&&!['web','admin'].includes(only))throw Error('Invalid single-client build');
if(!/^[a-z0-9-]+$/.test(label))throw Error('Invalid release label');
const release=path.join(state,'releases',label);
const excluded=new Set(['node_modules','.git','.next','build','dist','coverage','.npmrc','.yarnrc.yml','tsconfig.tsbuildinfo']);
const common={PATH:process.env.PATH,HOME:process.env.HOME,CI:'true',NODE_ENV:'production',
  NODE_OPTIONS:`--max-old-space-size=2400 --require=${root}/scripts/block-original-client-network.cjs`,
  NEXT_TELEMETRY_DISABLED:'1'};
const publicEnv={
  web:{NEXT_PUBLIC_APP_ENV:'staging',NEXT_PUBLIC_DEVELOPMENT_MODE:'false',
    NEXT_PUBLIC_BASE_URL:'https://localhost:8443/api/',NEXT_PUBLIC_WEBSITE_URL:'https://localhost:8443/',
    NEXT_PUBLIC_ADMIN_PANEL_URL:'https://localhost:8444/',NEXT_PUBLIC_IMAGE_URL:'https://localhost:8443/storage/',
    NEXT_PUBLIC_DEFAULT_LANGUAGE_CODE:'en',NEXT_PUBLIC_DEFAULT_TOKEN_TYPE:'Bearer',NEXT_PUBLIC_CACHE_TIME:'60',
    NEXT_PUBLIC_FIREBASE_ENABLED:'false',NEXT_PUBLIC_MAPS_ENABLED:'false'},
  admin:{VITE_APP_ENV:'staging',VITE_DEVELOPMENT_MODE:'false',VITE_API_ORIGIN:'https://localhost:8444',
    VITE_STOREFRONT_URL:'https://localhost:8443',VITE_ADMIN_PANEL_URL:'https://localhost:8444',
    VITE_RECAPTCHA_DISABLED:'false',VITE_RECAPTCHA_SITE_KEY:'BUILD_ONLY_NO_PROVIDER_KEY',
    VITE_FIREBASE_ENABLED:'false',VITE_MAPS_ENABLED:'false'},
};
fs.mkdirSync(`${release}/scripts/development`,{recursive:true});
fs.copyFileSync(`${root}/scripts/development/dev-api-target.cjs`,`${release}/scripts/development/dev-api-target.cjs`);
const receipt={release:label,mode:'production builds in secret-free snapshots',captcha:'External approved site key unavailable; placeholder is NOT login/delivery acceptance',clients:{}};
for(const client of only?[only]:['web','admin']) {
  const source=`${root}/.migration-backup/${client}`,destination=`${release}/.migration-backup/${client}`;
  fs.mkdirSync(path.dirname(destination),{recursive:true});
  const hashes=[];
  fs.cpSync(source,destination,{recursive:true,filter(file) {
    const name=path.basename(file);
    if(excluded.has(name)||name.startsWith('.env')||/\.(pem|key|sqlite|db)$/i.test(name))return false;
    if(fs.statSync(file).isFile())hashes.push([path.relative(source,file),crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex')]);
    return true;
  }});
  fs.symlinkSync(`${source}/node_modules`,`${destination}/node_modules`,'dir');
  if(client==='web') {
    fs.appendFileSync(`${destination}/next.config.js`,'\nmodule.exports.experimental={...module.exports.experimental,cpus:1};\n');
    // Loopback staging media is delivered directly by the browser, avoiding
    // Next's private-IP optimizer rejection. Production source allowlist stays unchanged.
    fs.appendFileSync(`${destination}/next.config.js`,'\nmodule.exports.images={...module.exports.images,unoptimized:true};\n');
    if(fs.existsSync(`${state}/current/.migration-backup/web/.next/cache`))
      fs.cpSync(`${state}/current/.migration-backup/web/.next/cache`,`${destination}/.next/cache`,{recursive:true});
  }
  fs.writeFileSync(`${release}/${client}-sources.json`,JSON.stringify(hashes));
  const log=fs.openSync(`${state}/private-logs/build-${label}-${client}.log`,'w');
  const start=Date.now();
  const code=await new Promise((resolve,reject)=>{
    const cmd=client==='web'?['node_modules/next/dist/bin/next','build','--webpack']:['node_modules/vite/bin/vite.js','build'];
    const child=spawn(process.execPath,cmd,{cwd:destination,env:{
      ...common,...publicEnv[client],
      NODE_OPTIONS:`--max-old-space-size=${client==='admin'?adminHeap:2400} --require=${root}/scripts/block-original-client-network.cjs`,
    },stdio:['ignore',log,log]});
    child.on('error',reject);child.on('exit',code=>resolve(code??1));
  });
  fs.closeSync(log);
  receipt.clients[client]={exitCode:code,durationSeconds:(Date.now()-start)/1000,sourceFiles:hashes.length};
  fs.writeFileSync(`${state}/build-${label}.json`,JSON.stringify(receipt,null,2));
  console.log(`${label} ${client} production build ${code===0?'PASS':'FAIL'}`);
  if(code)process.exit(code);
}
if(only) {
  const reused=only==='web'?'admin':'web';
  const previous=fs.realpathSync(`${state}/current`);
  const manifest=JSON.parse(fs.readFileSync(`${previous}/${reused}-sources.json`,'utf8'));
  for(const [file,hash]of manifest) {
    const source=`${root}/.migration-backup/${reused}/${file}`;
    if(!fs.existsSync(source)||crypto.createHash('sha256').update(fs.readFileSync(source)).digest('hex')!==hash)
      throw Error(`${reused} source changed; cannot reuse its production build`);
  }
  fs.symlinkSync(`${previous}/.migration-backup/${reused}`,`${release}/.migration-backup/${reused}`,'dir');
  fs.copyFileSync(`${previous}/${reused}-sources.json`,`${release}/${reused}-sources.json`);
  receipt.clients[reused]={...JSON.parse(fs.readFileSync(`${state}/build-${path.basename(previous)}.json`,'utf8')).clients[reused],
    reusedFrom:path.basename(previous),sourceHashesRechecked:true};
  fs.writeFileSync(`${state}/build-${label}.json`,JSON.stringify(receipt,null,2));
}
// Release switch is atomic; database/media/key are outside the release.
const link=`${state}/current-next`;
fs.symlinkSync(release,link,'dir');fs.renameSync(link,`${state}/current`);
console.log('Both production builds completed; atomic isolated release selected.');