import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {build} from '../../../../scripts/build-mobile.mjs';
const repository = fileURLToPath(new URL('../../../../', import.meta.url));
const temp = '/tmp/agendaally-mobile-android-qualification';
const tools = path.join(repository, '.local/customer-mobile-android-qualification-tools');
const env = {
  PATH: process.env.PATH,
  LANG: 'C.UTF-8',
  LC_ALL: 'C.UTF-8',
  HOME: '/tmp/agendaally-mobile-phase1/home',
  PUB_CACHE: '/tmp/agendaally-mobile-phase1/pub-cache',
  JAVA_HOME: `${tools}/jdk`,
  ANDROID_HOME: `${tools}/android-sdk`,
  ANDROID_SDK_ROOT: `${tools}/android-sdk`,
  GRADLE_USER_HOME: `${temp}/gradle`,
  TMPDIR: `${temp}/jvm-tmp`,
  JAVA_TOOL_OPTIONS: `-XX:+PerfDisableSharedMem -XX:ErrorFile=${temp}/jvm-tmp/hs_err_pid%p.log -Djava.io.tmpdir=${temp}/jvm-tmp`,
  GRADLE_OPTS: '-Dorg.gradle.jvmargs="-Xmx1536m -XX:MaxMetaspaceSize=512m" -Dorg.gradle.workers.max=2 -Dorg.gradle.vfs.watch=false',
  // Non-live bootstrap values only. No secret-store values or real provider
  // credentials are inherited. Maps remain off through the existing wrapper.
  APP_NAME: 'AgendaAlly',
  APP_ID: 'com.ibeauty.app',
  BASE_URL: 'http://127.0.0.1:9',
  WEB_URL: 'https://qualification.invalid',
  ADMIN_URL: 'https://qualification.invalid',
  DEEP_LINK_URL: 'qualification.invalid',
  FACEBOOK_APP_ID: '',
  FACEBOOK_CLIENT_TOKEN: '',
};
const code = await build(['development','apk','--maps=disabled','--debug','--no-pub','--target-platform=android-x64'],
  env, `${temp}/app`, '/tmp/agendaally-mobile-phase1/flutter/bin/flutter');
fs.writeFileSync(`${temp}/android-debug-build.exit`, String(code) + '\n');
process.exitCode = code;
