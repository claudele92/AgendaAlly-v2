import fs from "node:fs";
import path from "node:path";
import {createRequire} from "node:module";
const root=process.cwd(), web=path.join(root,".migration-backup/web");
const require=createRequire(import.meta.url);
const esbuild=require(path.join(root,"node_modules/.pnpm/esbuild@0.28.2/node_modules/esbuild"));
const out="/tmp/customer-logout-ui";
fs.mkdirSync(out,{recursive:true,mode:0o700});
await esbuild.build({
  entryPoints:[path.join(root,"scripts/development/customer-logout-ui-fixture.tsx")],
  bundle:true,outfile:path.join(out,"fixture.js"),platform:"browser",format:"iife",jsx:"automatic",
  nodePaths:[path.join(web,"node_modules")],
  define:{"process.env":JSON.stringify({NODE_ENV:"development",NEXT_PUBLIC_APP_ENV:"local",
    NEXT_PUBLIC_DEVELOPMENT_MODE:"true",NEXT_PUBLIC_BASE_URL:"/api/"})},
  plugins:[{
    name:"isolated-fixture-adapters",
    setup(build){
      build.onResolve({filter:/^next\/(navigation|headers)$/},args=>({path:args.path,namespace:"fixture"}));
      build.onResolve({filter:/^@\/hook\/use-fcm-token$/},()=>({path:"push",namespace:"fixture"}));
      build.onResolve({filter:/^@\//},args=>{
        const file=path.join(web,args.path.slice(2));
        return {path:path.extname(file) ? file : file+".ts"};
      });
      build.onLoad({filter:/.*/,namespace:"fixture"},args=>({
        contents:args.path==="push"
          ? 'export const useFcmToken=()=>({fcmToken:new URLSearchParams(location.search).get("push")==="yes"?"synthetic-push":null});'
          : args.path==="next/headers"
          ? 'export const cookies=()=>{throw new Error("Server-only API in browser fixture")};'
          : 'export const useRouter=()=>({replace:()=>{window.nativeLogoutFixture.navigationCount++},refresh:()=>{window.nativeLogoutFixture.refreshCount++}}); export const redirect=()=>{throw new Error("Unexpected authentication redirect")}; export const notFound=()=>{throw new Error("Unexpected notFound")};',
        loader:"js",
      }));
    }
  }],
});
fs.writeFileSync(path.join(out,"index.html"),'<!doctype html><html><head><meta charset="utf-8"><title>Native logout isolation check</title></head><body><div id="root"></div><script src="/fixture.js"></script></body></html>',{mode:0o600});
console.log("Isolated native hook bundle ready (no live accounts or credentials).");
