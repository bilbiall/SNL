// Android TV launcher banner (320x180, shown in the TV home row):  node scripts/gen-tv-banner.mjs
// → mobile/android/app/src/main/res/drawable-xhdpi/tv_banner.png
import fs from 'fs';
import sharp from 'sharp';
import opentype from 'opentype.js';

const W = 640, H = 360; // xhdpi: 320x180 dp at 2x
const load = (f) => { const b = fs.readFileSync(f); return opentype.parse(b.buffer.slice(b.byteOffset, b.byteOffset + b.byteLength)); };
const bungee = load('scripts/fonts/bungee-latin-400.woff');
const figtree = load('scripts/fonts/figtree-latin-700.woff');
const n = (v) => (Math.round(v * 100) / 100).toFixed(2);
const path = (font, text, x, y, size) => font.getPath(text, x, y, size).commands.map((c) => c.type === 'Z' ? 'Z' : c.type === 'C' ? 'C' + [c.x1, c.y1, c.x2, c.y2, c.x, c.y].map(n).join(' ') : c.type === 'Q' ? 'Q' + [c.x1, c.y1, c.x, c.y].map(n).join(' ') : c.type + [c.x, c.y].map(n).join(' ')).join('');
const width = (font, text, size) => font.getAdvanceWidth(text, size);

const bg = await sharp('public/img/boards/snl-night.webp').resize(W, H, { fit: 'cover' }).blur(8).modulate({ brightness: 0.4, saturation: 1.3 }).png().toBuffer();
const logo = await sharp('public/snakes-and-liquor-neon-serpent-512.png').resize(230, 230).png().toBuffer();
const tx = 270, s1 = 64;
const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<defs>
  <radialGradient id="glow" cx="25%" cy="50%" r="80%"><stop offset="0" stop-color="#7c3aed" stop-opacity=".55"/><stop offset="1" stop-color="#0b0a16" stop-opacity="0"/></radialGradient>
  <linearGradient id="pk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f59e0b"/><stop offset="1" stop-color="#ec4899"/></linearGradient>
  <linearGradient id="wh" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#cbd5e1"/></linearGradient>
</defs>
<rect width="${W}" height="${H}" fill="url(#glow)"/>
<rect y="${H - 8}" width="${W}" height="8" fill="url(#pk)"/>
<path d="${path(bungee, 'SNAKES', tx, 160, s1)}" fill="url(#wh)"/>
<path d="${path(bungee, '&', tx + width(bungee, 'SNAKES ', s1), 160, s1)}" fill="url(#pk)"/>
<path d="${path(bungee, 'LIQUOR', tx, 230, s1)}" fill="url(#wh)"/>
<path d="${path(figtree, 'PARTY GAME FOR THE BIG SCREEN', tx, 285, 18)}" fill="#cbd5e1"/>
</svg>`;
fs.mkdirSync('mobile/android/app/src/main/res/drawable-xhdpi', { recursive: true });
await sharp(bg).composite([{ input: Buffer.from(svg), left: 0, top: 0 }, { input: logo, left: 24, top: 65 }]).png().toFile('mobile/android/app/src/main/res/drawable-xhdpi/tv_banner.png');
console.log('banner written');
