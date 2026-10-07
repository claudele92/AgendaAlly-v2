import fs from 'node:fs';
import https from 'node:https';
const state=`${process.cwd()}/.local/staging-mvp`,id=Number(process.argv[2]);
if(![103,105,102,107].includes(id))throw Error('Unapproved synthetic staging actor');
const body=JSON.stringify({email:`native-${id}@agendaally.test`,password:'AgendaAlly-Dev-Only-2026!'});
const response=await new Promise((resolve,reject)=>{
  const request=https.request('https://localhost:8444/api/v1/auth/login',{method:'POST',
    ca:fs.readFileSync(`${state}/tls/server.crt`),headers:{'content-type':'application/json','content-length':Buffer.byteLength(body)}},res=>{
    let text='';res.on('data',chunk=>text+=chunk);
    res.on('end',()=>resolve({status:res.statusCode,data:JSON.parse(text)}));
  });
  request.on('error',reject);request.end(body);
});
if(response.status!==200||!response.data.data?.access_token)throw Error(`Synthetic staging login rejected: ${response.status}`);
const data=response.data.data,user=data.user;
const nativeUser={fullName:[user.firstname,user.lastname].filter(Boolean).join(' '),role:user.role,
  img:user.img,token:data.access_token,email:user.email,id:user.id,shop_id:user.shop?.id,
  isSuperAdmin:Boolean(user.is_super_admin),countryAdmin:user.country_admin||null};
const privateDirectory=`${state}/private-auth`;fs.mkdirSync(privateDirectory,{recursive:true,mode:0o700});
const file=`${privateDirectory}/${id}.json`;
fs.writeFileSync(file,JSON.stringify({cookies:[],origins:[{origin:'https://localhost:8444',localStorage:[
  {name:'token',value:data.access_token},
  {name:'persist:auth',value:JSON.stringify({user:JSON.stringify(nativeUser),_persist:JSON.stringify({version:-1,rehydrated:true})})},
]}]}),{mode:0o600});
console.log(JSON.stringify({actor:id,role:user.role,status:response.status,privateStorageStatePath:file,
  purpose:'Approved staging-only authenticated test setup; not production UI/CAPTCHA sign-in acceptance'}));