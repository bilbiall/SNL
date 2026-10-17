// Builds + verifies the signed release APK and publishes it to public/downloads/.
//   node scripts/build-apk.mjs      (or: npm run apk)
// Web files bundled into the app come from mobile/www. Keystore: C:\snl-tools\keys (see scripts/APK-README.md).
import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import crypto from 'crypto';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1')), '..');
const TOOLS = process.env.SNL_TOOLS || 'C:/snl-tools';
const env = {
  ...process.env,
  JAVA_HOME: `${TOOLS}/jdk`, ANDROID_HOME: `${TOOLS}/android-sdk`, ANDROID_SDK_ROOT: `${TOOLS}/android-sdk`,
  PATH: `${TOOLS}/jdk/bin;${process.env.PATH}`,
};
const run = (cmd, cwd) => { console.log(`\n> ${cmd}`); return execSync(cmd, { cwd, env, stdio: 'inherit' }); };
const out = (cmd, cwd) => execSync(cmd, { cwd, env, encoding: 'utf8' });

const mobile = path.join(ROOT, 'mobile');
const bt = `${TOOLS}/android-sdk/build-tools/34.0.0`;
const props = process.env.SNL_KEYSTORE_PROPS || `${TOOLS}/keys/keystore.properties`;
if (!fs.existsSync(props)) throw new Error(`Keystore properties not found: ${props}`);
if (!fs.existsSync(path.join(mobile, 'www', 'index.html'))) throw new Error('mobile/www/index.html is missing — put the web build in mobile/www first.');
if (!fs.existsSync(path.join(mobile, 'node_modules'))) run('npm install --no-audit --no-fund', mobile);
if (!fs.existsSync(path.join(mobile, 'android', 'local.properties'))) fs.writeFileSync(path.join(mobile, 'android', 'local.properties'), `sdk.dir=${TOOLS}/android-sdk\n`);

run('npx cap sync android', mobile);
const androidDir = path.join(mobile, 'android');
run(`"${path.join(androidDir, 'gradlew.bat')}" --no-daemon assembleRelease`, androidDir);

const apk = path.join(mobile, 'android', 'app', 'build', 'outputs', 'apk', 'release', 'app-release.apk');
if (!fs.existsSync(apk)) throw new Error('assembleRelease finished but app-release.apk was not found.');

const verify = out(`"${bt}/apksigner.bat" verify --verbose --print-certs "${apk}"`);
console.log(verify);
if (!/Verified using v2 scheme \(APK Signature Scheme v2\): true/.test(verify)) throw new Error('APK is not v2-signed.');
const certSha256 = (verify.match(/certificate SHA-256 digest: ([0-9a-f]+)/i) || [])[1] || '';
run(`"${bt}/zipalign.exe" -c -p 4 "${apk}"`, ROOT);

const dest = path.join(ROOT, 'public', 'downloads');
fs.mkdirSync(dest, { recursive: true });
fs.copyFileSync(apk, path.join(dest, 'snakes-and-liquor.apk'));
const buf = fs.readFileSync(apk);
const sha256 = crypto.createHash('sha256').update(buf).digest('hex');
const v = JSON.parse(fs.readFileSync(path.join(mobile, 'app-version.json'), 'utf8'));
fs.writeFileSync(path.join(dest, 'snakes-and-liquor.apk.sha256'), `${sha256}  snakes-and-liquor.apk\n`);
fs.writeFileSync(path.join(dest, 'version.json'), JSON.stringify({ versionName: v.versionName, versionCode: v.versionCode, sizeBytes: buf.length, notes: v.notes || [], sha256, builtAt: new Date().toISOString(), certSha256 }, null, 2) + '\n');
console.log(`\nSigned APK ready: public/downloads/snakes-and-liquor.apk (${(buf.length / 1048576).toFixed(2)} MB)\nSHA-256: ${sha256}\nSigning cert SHA-256: ${certSha256}`);
