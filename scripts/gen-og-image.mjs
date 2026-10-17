// Builds the social-share card (Open Graph / Twitter, 1200x630):  node scripts/gen-og-image.mjs  →  public/img/og-image.png
// Title is drawn from the real Bungee + Figtree outlines (scripts/fonts, SIL OFL) so it looks right without any installed fonts.
import fs from 'fs';
import sharp from 'sharp';
import opentype from 'opentype.js';

const W = 1200, H = 630;
const load = (f) => { const b = fs.readFileSync(f); return opentype.parse(b.buffer.slice(b.byteOffset, b.byteOffset + b.byteLength)); };
const bungee = load('scripts/fonts/bungee-latin-400.woff');
const figtree = load('scripts/fonts/figtree-latin-700.woff');
// own path-data writer: plain fixed-point numbers only (no exponents), so every renderer reads every glyph
const n = (v) => (Math.round(v * 100) / 100).toFixed(2);
const path = (font, text, x, y, size) => font.getPath(text, x, y, size).commands.map((c) => c.type === 'Z' ? 'Z' : c.type === 'C' ? 'C' + [c.x1, c.y1, c.x2, c.y2, c.x, c.y].map(n).join(' ') : c.type === 'Q' ? 'Q' + [c.x1, c.y1, c.x, c.y].map(n).join(' ') : c.type + [c.x, c.y].map(n).join(' ')).join('');
const width = (font, text, size) => font.getAdvanceWidth(text, size);

// background: the board art, blurred and darkened, with a purple glow
const bg = await sharp('public/img/boards/snl-night.webp').resize(W, H, { fit: 'cover' }).blur(14).modulate({ brightness: 0.42, saturation: 1.3 }).png().toBuffer();
const logo = await sharp('public/snakes-and-liquor-neon-serpent-512.png').resize(330, 330).png().toBuffer();

const line1 = 'SNAKES &', line2 = 'LIQUOR', s1 = 118;
const tx = 450;
const pills = ['FREE', 'WORKS OFFLINE', 'FAMILY MODE'];
let px = tx, pillSvg = '';
for (const p of pills) {
  const w = width(figtree, p, 24) + 40;
  pillSvg += `<rect x="${px}" y="470" width="${w}" height="46" rx="23" fill="rgba(255,255,255,.12)" stroke="rgba(255,255,255,.35)" stroke-width="2"/><path d="${path(figtree, p, px + 20, 502, 24)}" fill="#ffffff"/>`;
  px += w + 14;
}
const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<defs>
  <radialGradient id="glow" cx="30%" cy="45%" r="70%"><stop offset="0" stop-color="#7c3aed" stop-opacity=".55"/><stop offset="1" stop-color="#0b0a16" stop-opacity="0"/></radialGradient>
  <linearGradient id="pk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f59e0b"/><stop offset="1" stop-color="#ec4899"/></linearGradient>
  <linearGradient id="wh" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#cbd5e1"/></linearGradient>
  <filter id="sh" x="-10%" y="-10%" width="120%" height="130%"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000" flood-opacity=".55"/></filter>
</defs>
<rect width="${W}" height="${H}" fill="url(#glow)"/>
<rect x="0" y="${H - 10}" width="${W}" height="10" fill="url(#pk)"/>
<g filter="url(#sh)">
  <path d="${path(bungee, 'SNAKES', tx, 215, s1)}" fill="url(#wh)"/>
  <path d="${path(bungee, '&', tx + width(bungee, 'SNAKES ', s1), 215, s1)}" fill="url(#pk)"/>
  <path d="${path(bungee, 'LIQUOR', tx, 335, s1)}" fill="url(#wh)"/>
</g>
<path d="${path(figtree, 'The party board game where', tx, 400, 36)}" fill="#e2e8f0"/>
<path d="${path(figtree, 'every square has a story.', tx, 444, 36)}" fill="#e2e8f0"/>
${pillSvg}
</svg>`;

await sharp(bg).composite([
  { input: Buffer.from(svg), top: 0, left: 0 },
  { input: logo, top: 150, left: 70 },
]).png({ compressionLevel: 9, palette: true, quality: 90 }).toFile('public/img/og-image.png');
console.log('og-image.png written');
