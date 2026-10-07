import fs from 'node:fs';
import {spawn} from 'node:child_process';
const root=process.cwd(),state=`${root}/.local/staging-mvp`;
let child,stopping=false;
function tick() {
  child=spawn('php',[`${root}/scripts/staging/console.php`,'mvp:background-tick'],{env:process.env,stdio:'inherit'});
  child.once('exit',code=>{
    fs.writeFileSync(`${state}/scheduler-heartbeat.json`,JSON.stringify({at:new Date().toISOString(),exitCode:code}));
    if(!stopping)setTimeout(tick,60000);
  });
}
tick();
for(const signal of ['SIGTERM','SIGINT'])process.on(signal,()=>{stopping=true;child?.kill('SIGTERM');process.exit(0);});