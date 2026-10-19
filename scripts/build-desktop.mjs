// Builds the Windows desktop app:  npm run desktop
//   1. php artisan snl:export-app desktop/www   (the offline static game)
//   2. electron-builder  -> desktop/dist/*.exe
//   3. copies the installer to public/downloads/ and writes desktop-version.json
import crypto from "node:crypto";
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const desk = path.join(root, 'desktop');
const run = (cmd, cwd = root, env = {}) => execSync(cmd, { cwd, stdio: 'inherit', env: { ...process.env, ...env } });

const ver = JSON.parse(fs.readFileSync(path.join(desk, 'app-version.json'), 'utf8'));
console.log(`\n== Snakes & Liquor desktop ${ver.versionName} (code ${ver.versionCode}) ==\n`);

// keep desktop/package.json's version in step
const pkgPath = path.join(desk, 'package.json');
const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
if (pkg.version !== ver.versionName) { pkg.version = ver.versionName; fs.writeFileSync(pkgPath, JSON.stringify(pkg, null, 2) + '\n'); }

if (!fs.existsSync(path.join(desk, 'node_modules'))) run('npm install --no-audit --no-fund', desk);

console.log('-- exporting the offline game');
run('php artisan snl:export-app desktop/www');

console.log('-- packaging');
fs.rmSync(path.join(desk, 'dist'), { recursive: true, force: true });
// Use the Electron that npm already installed (desktop/node_modules/electron) instead of letting electron-builder download another copy:
// the build then also works with no internet.
const electronDist = path.join(desk, 'node_modules', 'electron', 'dist');
const electronVer = JSON.parse(fs.readFileSync(path.join(desk, 'node_modules', 'electron', 'package.json'), 'utf8')).version;
const local = fs.existsSync(path.join(electronDist, 'electron.exe')) ? ` -c.electronDist=node_modules/electron/dist -c.electronVersion=${electronVer}` : '';
run('npx electron-builder --win --x64' + local, desk, { CSC_IDENTITY_AUTO_DISCOVERY: 'false' });

const setup = `Snakes-and-Liquor-Setup-${ver.versionName}.exe`;
const portable = `Snakes-and-Liquor-Portable-${ver.versionName}.exe`;
const out = path.join(root, 'public', 'downloads');
fs.mkdirSync(out, { recursive: true });
for (const f of fs.readdirSync(out)) if (/^Snakes-and-Liquor-(Setup|Portable)-.*\.exe$/.test(f)) fs.rmSync(path.join(out, f));
for (const f of [setup, portable]) if (fs.existsSync(path.join(desk, 'dist', f))) fs.copyFileSync(path.join(desk, 'dist', f), path.join(out, f));
const sum = (f) => { const b = fs.readFileSync(path.join(out, f)); return { sizeBytes: b.length, sha256: crypto.createHash('sha256').update(b).digest('hex') }; };
fs.writeFileSync(path.join(out, 'desktop-version.json'), JSON.stringify({ versionName: ver.versionName, versionCode: ver.versionCode, notes: ver.notes || [], builtAt: new Date().toISOString(), file: setup, portable, ...sum(setup) }) + '\n');

console.log(`\nDone. Upload public/downloads/${setup} (and the portable .exe + desktop-version.json) to the server's public/downloads/.`);
