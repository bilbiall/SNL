// Measures how loud every reaction clip is and writes public/audio/reaction-levels.json:
//   { "file.mp3": { "g": playbackGain, "d": seconds } }   (g evens the clips out: loud ones are turned down, quiet ones up)
// Run after adding new clips:  node scripts/measure-reactions.mjs
import { MPEGDecoder } from 'mpg123-decoder';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const dir = path.join(root, 'public/audio/reactions');
const TARGET = 0.11;      // the loudness every meme is brought to (RMS, 0..1)
const MUSIC_TARGET = 0.045; // the background loop sits well underneath
const dec = new MPEGDecoder();
await dec.ready;

async function measure(file) {
  await dec.reset();
  const r = dec.decode(new Uint8Array(fs.readFileSync(file)));
  const ch = r.channelData, n = r.samplesDecoded, sr = r.sampleRate;
  if (!n) return null;
  const win = Math.floor(sr * 0.05), wr = [];
  let peak = 0;
  for (let i = 0; i + win <= n; i += win) {
    let s = 0;
    for (const d of ch) for (let k = i; k < i + win; k++) { const v = d[k]; s += v * v; const a = Math.abs(v); if (a > peak) peak = a; }
    wr.push(s / (win * ch.length));
  }
  const sorted = wr.filter((x) => x > 1e-6).sort((a, b) => a - b);
  if (!sorted.length) return null;
  const top = sorted[Math.floor(sorted.length * 0.95)] || sorted[sorted.length - 1];
  const active = sorted.filter((x) => x >= top * 0.003);          // ignore silence / tails
  const ms = active.reduce((a, b) => a + b, 0) / active.length;
  return { rms: Math.sqrt(ms), peak, dur: n / sr };
}

const out = {};
for (const f of fs.readdirSync(dir).filter((f) => f.toLowerCase().endsWith('.mp3')).sort()) {
  try {
    const m = await measure(path.join(dir, f));
    if (!m) continue;
    const g = Math.min(TARGET / m.rms, 0.97 / Math.max(m.peak, 0.01), 6);
    out[f] = { g: +g.toFixed(2), d: +m.dur.toFixed(1) };
  } catch (e) { console.warn('skipped', f, e.message); }
}
const mus = await measure(path.join(root, 'public/audio/playback.mp3'));
const meta = { music: mus ? +Math.min(1, MUSIC_TARGET / mus.rms).toFixed(2) : 0.4 };
fs.writeFileSync(path.join(root, 'public/audio/reaction-levels.json'), JSON.stringify({ meta, files: out }));
const gs = Object.values(out).map((x) => x.g);
console.log(`${gs.length} clips · gain ${Math.min(...gs)}–${Math.max(...gs)} · suggested music volume ${meta.music}`);
