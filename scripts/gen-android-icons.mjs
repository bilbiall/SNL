// Generates the Android launcher icons, adaptive icon and splash screens from the neon-serpent artwork.
//   node scripts/gen-android-icons.mjs
import fs from 'fs';
import path from 'path';
import { createRequire } from 'module';
const require = createRequire(import.meta.url);
const sharp = require(path.resolve('node_modules/sharp'));

const SRC = path.resolve('public/snakes-and-liquor-neon-serpent-512.png');
const RES = path.resolve('mobile/android/app/src/main/res');
const BG = '#0b0a16';
const w = (rel, buf) => { const f = path.join(RES, rel); fs.mkdirSync(path.dirname(f), { recursive: true }); fs.writeFileSync(f, buf); };
const art = (size) => sharp(SRC).resize(size, size, { fit: 'contain' }).png().toBuffer();
const roundMask = (s) => Buffer.from(`<svg width="${s}" height="${s}"><circle cx="${s / 2}" cy="${s / 2}" r="${s / 2}"/></svg>`);

const LAUNCH = { mdpi: 48, hdpi: 72, xhdpi: 96, xxhdpi: 144, xxxhdpi: 192 };
for (const [d, s] of Object.entries(LAUNCH)) {
  const sq = await art(s);
  w(`mipmap-${d}/ic_launcher.png`, sq);
  w(`mipmap-${d}/ic_launcher_round.png`, await sharp(sq).composite([{ input: roundMask(s), blend: 'dest-in' }]).png().toBuffer());
  // adaptive foreground: artwork in the centre 66% safe zone of a 108dp canvas, transparent elsewhere
  const fg = Math.round(s * 108 / 48), inner = Math.round(fg * 0.66);
  w(`mipmap-${d}/ic_launcher_foreground.png`, await sharp({ create: { width: fg, height: fg, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } } })
    .composite([{ input: await art(inner), gravity: 'center' }]).png().toBuffer());
}
w('values/ic_launcher_background.xml', `<?xml version="1.0" encoding="utf-8"?>\n<resources>\n    <color name="ic_launcher_background">${BG}</color>\n</resources>\n`);
const adaptive = `<?xml version="1.0" encoding="utf-8"?>\n<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">\n    <background android:drawable="@color/ic_launcher_background"/>\n    <foreground android:drawable="@mipmap/ic_launcher_foreground"/>\n</adaptive-icon>\n`;
w('mipmap-anydpi-v26/ic_launcher.xml', adaptive);
w('mipmap-anydpi-v26/ic_launcher_round.xml', adaptive);
for (const f of ['drawable/ic_launcher_background.xml', 'drawable-v24/ic_launcher_foreground.xml']) fs.rmSync(path.join(RES, f), { force: true });

// splash: artwork centred on the app background
const splash = async (W, H) => sharp({ create: { width: W, height: H, channels: 3, background: BG } })
  .composite([{ input: await art(Math.round(Math.min(W, H) * 0.42)), gravity: 'center' }]).png().toBuffer();
w('drawable/splash.png', await splash(480, 480));
const PORT = { mdpi: [320, 480], hdpi: [480, 800], xhdpi: [720, 1280], xxhdpi: [960, 1600], xxxhdpi: [1280, 1920] };
for (const [d, [W, H]] of Object.entries(PORT)) {
  w(`drawable-port-${d}/splash.png`, await splash(W, H));
  w(`drawable-land-${d}/splash.png`, await splash(H, W));
}
// status / navigation bars + theme colours
w('values/colors.xml', `<?xml version="1.0" encoding="utf-8"?>\n<resources>\n    <color name="colorPrimary">${BG}</color>\n    <color name="colorPrimaryDark">${BG}</color>\n    <color name="colorAccent">#ec4899</color>\n</resources>\n`);
console.log('Android icons + splash written to', RES);
