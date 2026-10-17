// Dreadlocks for the Illustrated (DiceBear Lorelei) avatars. Lorelei has no locs, so they are drawn in code on top of a
// near-bald Lorelei head: strands behind the head, a crown of short locs on top, and long locs hanging at the sides.
// Everything is in Lorelei's 980×980 space. Output stays CC0-compatible (our drawing + CC0 art).
const OUT = '#14100e';
const strand = (x, y, len, w, lean, color, ring) => {
  // one loc: a rounded stick that leans a little, with darker rings along it
  const x2 = x + lean;
  const body = `<path d="M${x} ${y} Q${(x + x2) / 2 + lean * 0.2} ${y + len / 2} ${x2} ${y + len}" stroke="${OUT}" stroke-width="${w + 12}" stroke-linecap="round" fill="none"/>`
    + `<path d="M${x} ${y} Q${(x + x2) / 2 + lean * 0.2} ${y + len / 2} ${x2} ${y + len}" stroke="${color}" stroke-width="${w}" stroke-linecap="round" fill="none"/>`;
  let rings = '';
  for (let t = 0.18; t < 0.95; t += 0.2) {
    const cx = x + (x2 - x) * t, cy = y + len * t;
    rings += `<path d="M${cx - w / 2 + 3} ${cy} h${w - 6}" stroke="${ring}" stroke-width="5" stroke-linecap="round" opacity=".55"/>`;
  }
  return body + rings;
};

export function locsLayers(color = '#2b1b14', ring = '#000') {
  // strands hidden behind the head and shoulders
  const back = [330, 400, 470, 540, 610, 680].map((x, i) => strand(x, 330, 360 + (i % 3) * 30, 52, (i - 2.5) * 10, color, ring)).join('');
  // hanging locs beside the face (left and right)
  const sideL = [262, 214, 172].map((x, i) => strand(x, 250 + i * 18, 400 - i * 30, 50, -14 - i * 8, color, ring)).join('');
  const sideR = [718, 766, 808].map((x, i) => strand(x, 250 + i * 18, 400 - i * 30, 50, 14 + i * 8, color, ring)).join('');
  // a crown of short locs standing up and out from the scalp
  const crown = [-72, -50, -28, -8, 12, 32, 54, 74].map((deg) => {
    const r = (deg * Math.PI) / 180, bx = 490 + 232 * Math.sin(r), by = 340 - 160 * Math.cos(r), len = 120 + 30 * Math.cos(r * 1.4);
    return `<g transform="rotate(${deg} ${bx.toFixed(0)} ${by.toFixed(0)})">${strand(bx.toFixed(0) * 1, by.toFixed(0) * 1, -len, 50, 0, color, ring)}</g>`;
  }).join('');
  return { back: `<g>${back}</g>`, front: `<g>${sideL}${sideR}</g><g>${crown}</g>` };
}
