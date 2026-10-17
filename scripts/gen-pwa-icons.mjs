// Builds every web/app icon from the brand artwork:  node scripts/gen-pwa-icons.mjs
// Source: public/snakes-and-liquor-neon-serpent-512.png  →  public/icons/*  + public/favicon.ico + public/img/logo-96.png
import fs from 'fs';
import sharp from 'sharp';

const SRC = 'public/snakes-and-liquor-neon-serpent-512.png';
const BG = '#0b0a16';
fs.mkdirSync('public/icons', { recursive: true });

const png = (size) => sharp(SRC).resize(size, size, { fit: 'contain' }).png().toBuffer();
// opaque version (no transparent rounded corners) — for iOS home screen and maskable icons
const flat = async (size, inner = 1) => {
  const art = await sharp(SRC).resize(Math.round(size * inner), Math.round(size * inner)).png().toBuffer();
  return sharp({ create: { width: size, height: size, channels: 4, background: BG } })
    .composite([{ input: art, gravity: 'center' }]).png().toBuffer();
};

const out = {
  'public/icons/icon-48.png': await png(48),
  'public/icons/icon-72.png': await png(72),
  'public/icons/icon-96.png': await png(96),
  'public/icons/icon-128.png': await png(128),
  'public/icons/icon-144.png': await png(144),
  'public/icons/icon-192.png': await png(192),
  'public/icons/icon-256.png': await png(256),
  'public/icons/icon-384.png': await png(384),
  'public/icons/icon-512.png': await png(512),
  'public/icons/maskable-192.png': await flat(192, 0.78),
  'public/icons/maskable-512.png': await flat(512, 0.78),
  'public/icons/apple-touch-icon.png': await flat(180, 1),
  'public/icons/favicon-16.png': await png(16),
  'public/icons/favicon-32.png': await png(32),
  'public/img/logo-96.png': await png(96),
};
for (const [f, buf] of Object.entries(out)) fs.writeFileSync(f, buf);

// favicon.ico — PNG-compressed entries (16, 32, 48), supported by every current browser
const sizes = [16, 32, 48];
const bufs = await Promise.all(sizes.map(png));
const header = Buffer.alloc(6); header.writeUInt16LE(0, 0); header.writeUInt16LE(1, 2); header.writeUInt16LE(sizes.length, 4);
let offset = 6 + sizes.length * 16;
const dir = bufs.map((b, i) => {
  const e = Buffer.alloc(16);
  e.writeUInt8(sizes[i] === 256 ? 0 : sizes[i], 0); e.writeUInt8(sizes[i] === 256 ? 0 : sizes[i], 1);
  e.writeUInt8(0, 2); e.writeUInt8(0, 3); e.writeUInt16LE(1, 4); e.writeUInt16LE(32, 6);
  e.writeUInt32LE(b.length, 8); e.writeUInt32LE(offset, 12); offset += b.length;
  return e;
});
fs.writeFileSync('public/favicon.ico', Buffer.concat([header, ...dir, ...bufs]));
console.log('icons written:', Object.keys(out).length, '+ favicon.ico');
