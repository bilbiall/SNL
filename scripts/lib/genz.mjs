// Original "Gen Z" avatars — streetwear, headphones, bucket hats, hijab, locs, buns… drawn in code (no third-party art).
// Layered SVG like the heroes: backdrop → back hair → hoodie/torso → neck → head → face → front hair → accessory,
// so every look renders in all seven skin shades.
const O = '#1b1830';
const clamp = (n) => Math.max(0, Math.min(255, Math.round(n)));
const shade = (hex, amt) => {
  const n = parseInt(hex.replace('#', ''), 16);
  return '#' + [(n >> 16) + amt, ((n >> 8) & 255) + amt, (n & 255) + amt].map((v) => clamp(v).toString(16).padStart(2, '0')).join('');
};

// hair: afro | locs | braids | buns | fade | curly | bob | hijab | buzz | pigtails | mohawk | wavy
// acc:  phones | bucket | beanie | cap | y2k | band | round | none · top: hoodie colour · tee: 'hoodie' | 'tee' | 'bomber'
export const GENZ = [
  { id: 'g1',  gender: 'f', label: 'Main Character', bg: ['#ff9ad5', '#4a1456'], hair: 'buns',     hc: '#2a1a14', acc: 'y2k',    ac: '#ff4fa3', top: '#ff4fa3', tee: 'tee',    face: 'smirk' },
  { id: 'g2',  gender: 'm', label: 'Streetwear',     bg: ['#ffd166', '#7a2a0a'], hair: 'fade',     hc: '#1b1410', acc: 'phones', ac: '#ffd43b', top: '#2b2f3a', tee: 'hoodie', face: 'grin' },
  { id: 'g3',  gender: 'f', label: 'Soft Girl',      bg: ['#ffd1ec', '#7a3d8a'], hair: 'pigtails', hc: '#ff8fcf', acc: 'none',   ac: '#fff',    top: '#b9a6ff', tee: 'hoodie', face: 'smile', blush: true },
  { id: 'g4',  gender: 'm', label: 'Gamer',          bg: ['#7be4ff', '#1d1a6b'], hair: 'curly',    hc: '#1b1410', acc: 'phones', ac: '#34d399', top: '#1f2937', tee: 'hoodie', face: 'grin' },
  { id: 'g5',  gender: 'f', label: 'Hijabi Chic',    bg: ['#6ee7d4', '#0b4f4a'], hair: 'hijab',    hc: '#7c3aed', acc: 'round',  ac: '#facc15', top: '#7c3aed', tee: 'tee',    face: 'smile' },
  { id: 'g6',  gender: 'm', label: 'Bucket Hat',     bg: ['#a5f3a0', '#14532d'], hair: 'buzz',     hc: '#1b1410', acc: 'bucket', ac: '#f59e0b', top: '#16a34a', tee: 'tee',    face: 'smirk' },
  { id: 'g7',  gender: 'f', label: 'Locs & Vibes',   bg: ['#fbbf24', '#7c2d12'], hair: 'locs',     hc: '#2a1a14', acc: 'band',   ac: '#ef4444', top: '#ef4444', tee: 'tee',    face: 'grin' },
  { id: 'g8',  gender: 'm', label: 'Skater',         bg: ['#93c5fd', '#1e3a8a'], hair: 'wavy',     hc: '#c08a4a', acc: 'beanie', ac: '#ef4444', top: '#0ea5e9', tee: 'bomber', face: 'smirk' },
  { id: 'g9',  gender: 'f', label: 'Afro Puff',      bg: ['#ffb4a2', '#6b1d3a'], hair: 'afro',     hc: '#14100d', acc: 'phones', ac: '#ff4fa3', top: '#f59e0b', tee: 'hoodie', face: 'grin' },
  { id: 'g10', gender: 'm', label: 'Dyed Mohawk',    bg: ['#c4b5fd', '#3b0764'], hair: 'mohawk',   hc: '#22d3ee', acc: 'none',   ac: '#fff',    top: '#111827', tee: 'bomber', face: 'smirk' },
  { id: 'g11', gender: 'f', label: 'Cap & Braids',   bg: ['#fde68a', '#78350f'], hair: 'braids',   hc: '#1b1410', acc: 'cap',    ac: '#2563eb', top: '#2563eb', tee: 'hoodie', face: 'smile' },
  { id: 'g12', gender: 'f', label: 'Y2K Bob',        bg: ['#f0abfc', '#581c87'], hair: 'bob',      hc: '#3b82f6', acc: 'y2k',    ac: '#0ea5e9', top: '#f0abfc', tee: 'tee',    face: 'smirk', blush: true },
];

const bg = (g) => `
<defs><radialGradient id="g" cx="50%" cy="36%" r="78%"><stop offset="0" stop-color="${g.bg[0]}"/><stop offset="1" stop-color="${g.bg[1]}"/></radialGradient>
<pattern id="d" width="9" height="9" patternUnits="userSpaceOnUse"><path d="M4.5 1.5v6M1.5 4.5h6" stroke="#fff" stroke-width="1" opacity=".14" stroke-linecap="round"/></pattern></defs>
<rect width="128" height="128" fill="url(#g)"/><rect width="128" height="128" fill="url(#d)"/>
<circle cx="104" cy="22" r="6" fill="#fff" opacity=".18"/><circle cx="20" cy="30" r="3.5" fill="#fff" opacity=".2"/>`;

// hair drawn behind the head
const BACK = {
  afro: (c) => `<circle cx="64" cy="44" r="36" fill="${c}" stroke="${O}" stroke-width="2.6"/>`,
  locs: (c) => [34, 40, 46, 82, 88, 94].map((x, i) => `<rect x="${x - 3}" y="42" width="7" height="${50 + (i % 3) * 8}" rx="3.5" fill="${c}" stroke="${O}" stroke-width="2"/>`).join(''),
  braids: (c) => `<g fill="${c}" stroke="${O}" stroke-width="2">${[0, 1, 2, 3, 4].map((i) => `<ellipse cx="38" cy="${70 + i * 9}" rx="5" ry="5.5"/><ellipse cx="90" cy="${70 + i * 9}" rx="5" ry="5.5"/>`).join('')}</g>`,
  buns: (c) => `<circle cx="38" cy="26" r="11" fill="${c}" stroke="${O}" stroke-width="2.4"/><circle cx="90" cy="26" r="11" fill="${c}" stroke="${O}" stroke-width="2.4"/>`,
  pigtails: (c) => `<path d="M36 52 C20 56 16 78 24 98 C30 90 34 76 38 66Z M92 52 C108 56 112 78 104 98 C98 90 94 76 90 66Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  bob: (c) => `<path d="M34 44 C30 70 32 92 40 98 L50 80 L78 80 L88 98 C96 92 98 70 94 44Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  wavy: (c) => `<path d="M34 46 C26 70 30 92 22 108 C40 106 44 94 44 80Z M94 46 C102 70 98 92 106 108 C88 106 84 94 84 80Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  hijab: (c) => `<path d="M26 104 C22 56 36 22 64 22 C92 22 106 56 102 104 C92 112 36 112 26 104Z" fill="${c}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`,
};
// hair drawn over the head
const FRONT = {
  afro: (c) => `<path d="M40 52 C42 36 52 30 64 30 C76 30 86 36 88 52 C82 44 74 40 64 40 C54 40 46 44 40 52Z" fill="${c}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>`,
  locs: (c) => `<path d="M38 58 C36 34 48 26 64 26 C80 26 92 34 90 58 C86 44 76 38 64 38 C52 38 42 44 38 58Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  braids: (c) => `<path d="M38 58 C36 34 48 26 64 26 C80 26 92 34 90 58 C86 44 76 36 64 36 C52 36 42 44 38 58Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M64 28 V40 M52 31 L55 41 M76 31 L73 41" stroke="${shade(c, 30)}" stroke-width="1.5"/>`,
  buns: (c) => `<path d="M38 56 C36 32 50 26 64 26 C78 26 92 32 90 56 C84 42 76 38 64 38 C52 38 44 42 38 56Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  pigtails: (c) => `<path d="M38 56 C36 32 50 25 64 25 C78 25 92 32 90 56 C80 46 74 38 62 36 C54 42 46 46 38 56Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><circle cx="31" cy="64" r="4" fill="#fff" stroke="${O}" stroke-width="1.6"/><circle cx="97" cy="64" r="4" fill="#fff" stroke="${O}" stroke-width="1.6"/>`,
  fade: (c) => `<path d="M39 56 C37 36 48 29 64 29 C80 29 91 36 89 56 C86 46 78 42 64 42 C50 42 42 46 39 56Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  curly: (c) => `<g fill="${c}" stroke="${O}" stroke-width="2.2">${[[44, 36], [54, 28], [66, 26], [77, 29], [85, 38], [50, 40], [64, 36], [78, 40]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="9"/>`).join('')}</g>`,
  bob: (c) => `<path d="M38 60 C36 34 48 26 64 26 C80 26 92 34 90 60 C84 46 74 40 62 38 C54 46 46 52 38 60Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  buzz: (c) => `<path d="M40 52 C40 36 50 30 64 30 C78 30 88 36 88 52 C84 42 76 38 64 38 C52 38 44 42 40 52Z" fill="${c}" opacity=".85" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  mohawk: (c) => `<path d="M56 44 L54 18 L60 24 L64 12 L68 24 L74 18 L72 44Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 52 C42 42 50 40 56 42 L72 42 C78 40 86 42 88 52 C84 46 76 44 64 44 C52 44 44 46 40 52Z" fill="${shade(c, -90)}" opacity=".5"/>`,
  wavy: (c) => `<path d="M38 60 C34 32 50 24 66 26 C82 28 94 36 90 60 C84 44 74 38 62 40 C50 42 44 50 38 60Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  hijab: (c, g, sk) => `<path d="M38 62 C34 34 48 24 64 24 C80 24 94 34 90 62 C86 46 78 40 64 40 C50 40 42 46 38 62Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
};

const ACC = {
  phones: (c) => `<path d="M36 58 C32 24 96 24 92 58" fill="none" stroke="${O}" stroke-width="7" stroke-linecap="round"/><path d="M36 58 C32 24 96 24 92 58" fill="none" stroke="${c}" stroke-width="4" stroke-linecap="round"/>
    <rect x="29" y="52" width="11" height="20" rx="5" fill="${c}" stroke="${O}" stroke-width="2.2"/><rect x="88" y="52" width="11" height="20" rx="5" fill="${c}" stroke="${O}" stroke-width="2.2"/>`,
  bucket: (c) => `<path d="M30 46 C34 38 48 36 64 36 C80 36 94 38 98 46 C90 50 38 50 30 46Z" fill="${shade(c, -25)}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M38 42 C38 24 50 18 64 18 C78 18 90 24 90 42 C80 46 48 46 38 42Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 38 C52 41 76 41 88 38" fill="none" stroke="${O}" stroke-width="1.6" opacity=".6"/>`,
  beanie: (c) => `<path d="M37 48 C36 24 50 18 64 18 C78 18 92 24 91 48 C80 52 48 52 37 48Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><rect x="36" y="42" width="56" height="9" rx="4" fill="${shade(c, -35)}" stroke="${O}" stroke-width="2"/><circle cx="64" cy="14" r="6" fill="#fff" stroke="${O}" stroke-width="2"/>`,
  cap: (c) => `<path d="M38 46 C38 26 50 20 64 20 C78 20 90 26 90 46 C80 42 48 42 38 46Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M42 44 C36 48 20 48 14 44 C22 38 36 38 44 40Z" fill="${shade(c, -30)}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><circle cx="64" cy="20" r="2.4" fill="#fff"/>`,
  y2k: (c) => `<rect x="38" y="52" width="21" height="13" rx="6.5" fill="${c}" stroke="${O}" stroke-width="2.2"/><rect x="69" y="52" width="21" height="13" rx="6.5" fill="${c}" stroke="${O}" stroke-width="2.2"/><path d="M59 57 H69" stroke="${O}" stroke-width="2.4"/><path d="M42 55 L48 55 M73 55 L79 55" stroke="#fff" stroke-width="2" stroke-linecap="round" opacity=".7"/>`,
  band: (c) => `<path d="M38 44 C46 38 82 38 90 44 L90 51 C82 45 46 45 38 51Z" fill="${c}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>`,
  round: (c) => `<circle cx="54" cy="58" r="9" fill="none" stroke="${c}" stroke-width="3"/><circle cx="74" cy="58" r="9" fill="none" stroke="${c}" stroke-width="3"/><path d="M63 58 H65" stroke="${c}" stroke-width="3"/><circle cx="54" cy="58" r="9" fill="#fff" opacity=".12"/><circle cx="74" cy="58" r="9" fill="#fff" opacity=".12"/>`,
  none: () => '',
};

const mouth = (f) => f === 'grin' ? `<path d="M54 72 Q64 82 74 72 Q64 76 54 72Z" fill="#fff" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`
  : f === 'smile' ? `<path d="M55 72 Q64 80 73 72" stroke="${O}" stroke-width="2.6" fill="none" stroke-linecap="round"/>`
    : `<path d="M56 73 Q64 77 74 70" stroke="${O}" stroke-width="2.6" fill="none" stroke-linecap="round"/>`;

const torso = (g, sk) => {
  const t = g.top, d = shade(t, -35), dark = shade(sk, -26);
  const body = `<path d="M12 128 C14 102 36 92 64 92 C92 92 114 102 116 128Z" fill="${t}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`;
  const neck = `<rect x="52" y="78" width="24" height="20" rx="6" fill="${dark}" stroke="${O}" stroke-width="2.4"/>`;
  if (g.tee === 'hoodie') return `<path d="M30 100 C34 86 50 82 64 98 C78 82 94 86 98 100 L98 112 L30 112Z" fill="${d}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>` + body + neck
    + `<path d="M50 92 C56 102 72 102 78 92" fill="${d}" stroke="${O}" stroke-width="2.2"/><path d="M57 98 V114 M71 98 V114" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/><rect x="48" y="116" width="32" height="12" rx="4" fill="${d}" opacity=".6"/>`;
  if (g.tee === 'bomber') return body + neck + `<path d="M50 92 C56 100 72 100 78 92 L82 102 C72 110 56 110 46 102Z" fill="${d}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M64 102 V128" stroke="${O}" stroke-width="2.4"/><rect x="12" y="116" width="104" height="5" fill="#fff" opacity=".35"/>`;
  return body + neck + `<path d="M48 93 C56 104 72 104 80 93" fill="${shade(sk, -20)}" stroke="${O}" stroke-width="2.2"/><path d="M40 112 L88 112" stroke="#fff" stroke-width="2" opacity=".35"/>`;
};

export function genzSvg(g, skin) {
  const sk = skin.hex, dark = shade(sk, -26);
  const hijab = g.hair === 'hijab';
  const covered = g.acc === 'y2k';
  const eyes = covered ? '' : `<ellipse cx="54" cy="58" rx="4.6" ry="5" fill="#fff" stroke="${O}" stroke-width="1.6"/><ellipse cx="74" cy="58" rx="4.6" ry="5" fill="#fff" stroke="${O}" stroke-width="1.6"/><circle cx="55" cy="59" r="2.6" fill="${O}"/><circle cx="75" cy="59" r="2.6" fill="${O}"/><circle cx="56" cy="58" r=".9" fill="#fff"/><circle cx="76" cy="58" r=".9" fill="#fff"/>`;
  const brows = `<path d="M47 49 Q53 46 60 49 M68 49 Q75 46 81 49" stroke="${O}" stroke-width="2.4" stroke-linecap="round" fill="none"/>`;
  const blush = g.blush ? `<ellipse cx="45" cy="68" rx="5" ry="3" fill="#ff6fa0" opacity=".45"/><ellipse cx="83" cy="68" rx="5" ry="3" fill="#ff6fa0" opacity=".45"/>` : '';
  const hijabDrape = hijab ? `<path d="M30 128 C28 108 40 98 64 100 C88 98 100 108 98 128Z" fill="${g.hc}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>` : '';
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128" shape-rendering="geometricPrecision">
${bg(g)}
${BACK[g.hair] ? BACK[g.hair](g.hc) : ''}
${torso(g, sk)}
${hijabDrape}
<ellipse cx="40.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/><ellipse cx="87.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/>
<ellipse cx="64" cy="58" rx="24" ry="27.5" fill="${sk}" stroke="${O}" stroke-width="2.6"/>
<path d="M61 63 L59 69 L64 70" stroke="${dark}" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
${eyes}${covered ? '' : brows}${blush}${mouth(g.face)}
${FRONT[g.hair](g.hc, g, sk)}
${ACC[g.acc](g.ac)}
</svg>`;
}
