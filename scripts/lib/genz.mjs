// Original "Gen Z" avatars — bold, glossy, sticker-style characters drawn in code (no third-party art).
// Layers: pattern backdrop → [sticker group: back hair → hoodie/torso → head → face → front hair → accessory → jewellery]
// The sticker group gets a thick white outline, so each character pops like a die-cut sticker.
// Every look renders in all seven skin shades.
const O = '#1b1830';
const clamp = (n) => Math.max(0, Math.min(255, Math.round(n)));
const shade = (hex, amt) => {
  const n = parseInt(hex.replace('#', ''), 16);
  return '#' + [(n >> 16) + amt, ((n >> 8) & 255) + amt, (n & 255) + amt].map((v) => clamp(v).toString(16).padStart(2, '0')).join('');
};

// hair: afro | locs | braids | buns | fade | curly | bob | hijab | buzz | pigtails | mohawk | wavy
// acc:  phones | bucket | beanie | cap | y2k | band | round | none · tee: hoodie | tee | bomber
// fx:   hoops | chain | freckles | gem | stud (list) · gfx: bolt | star | stripe | heart · pat: rays | dots | stars | zig
export const GENZ = [
  { id: 'g1',  gender: 'f', label: 'Main Character', bg: ['#ffb3e1', '#6d28d9'], pat: 'stars', hair: 'buns',     hc: '#ff3d9a', acc: 'y2k',    ac: '#ff4fa3', top: '#7c3aed', tee: 'tee',    gfx: 'star',   face: 'smirk', iris: '#7c3aed', fx: ['hoops'], lip: '#ff2d6f' },
  { id: 'g2',  gender: 'm', label: 'Streetwear',     bg: ['#fde047', '#ea580c'], pat: 'rays',  hair: 'fade',     hc: '#15101a', acc: 'phones', ac: '#fde047', top: '#f97316', tee: 'hoodie', gfx: 'bolt',   face: 'grin',  iris: '#5b3a1e', fx: ['chain'] },
  { id: 'g3',  gender: 'f', label: 'Soft Girl',      bg: ['#e0f2fe', '#a78bfa'], pat: 'dots',  hair: 'pigtails', hc: '#ff9bd2', acc: 'none',   ac: '#fff',    top: '#ffd1f0', tee: 'hoodie', gfx: 'heart',  face: 'smile', iris: '#ec4899', fx: ['gem'], lip: '#ff6fa0', blush: true },
  { id: 'g4',  gender: 'm', label: 'Gamer',          bg: ['#22d3ee', '#312e81'], pat: 'zig',   hair: 'curly',    hc: '#1e1b4b', acc: 'phones', ac: '#34d399', top: '#0f172a', tee: 'hoodie', gfx: 'stripe', face: 'grin',  iris: '#0ea5e9', fx: [] },
  { id: 'g5',  gender: 'f', label: 'Hijabi Chic',    bg: ['#99f6e4', '#0f766e'], pat: 'stars', hair: 'hijab',    hc: '#14b8a6', acc: 'round',  ac: '#facc15', top: '#0ea5e9', tee: 'tee',    gfx: 'star',   face: 'smile', iris: '#0f766e', fx: ['gem'], lip: '#f43f5e' },
  { id: 'g6',  gender: 'm', label: 'Bucket Hat',     bg: ['#bef264', '#15803d'], pat: 'dots',  hair: 'buzz',     hc: '#15101a', acc: 'bucket', ac: '#facc15', top: '#22c55e', tee: 'tee',    gfx: 'smile',  face: 'smirk', iris: '#3f2a14', fx: ['stud'] },
  { id: 'g7',  gender: 'f', label: 'Locs & Vibes',   bg: ['#fca5a5', '#9f1239'], pat: 'rays',  hair: 'locs',     hc: '#3b1d0f', acc: 'band',   ac: '#ef4444', top: '#fb923c', tee: 'tee',    gfx: 'bolt',   face: 'grin',  iris: '#4a2512', fx: ['hoops'], lip: '#be123c' },
  { id: 'g8',  gender: 'm', label: 'Skater',         bg: ['#93c5fd', '#1d4ed8'], pat: 'zig',   hair: 'wavy',     hc: '#f59e0b', acc: 'beanie', ac: '#ef4444', top: '#0ea5e9', tee: 'bomber', gfx: 'stripe', face: 'smirk', iris: '#2563eb', fx: ['freckles'] },
  { id: 'g9',  gender: 'f', label: 'Afro Puff',      bg: ['#fda4af', '#9d174d'], pat: 'dots',  hair: 'afro',     hc: '#15101a', acc: 'phones', ac: '#ff4fa3', top: '#facc15', tee: 'hoodie', gfx: 'heart',  face: 'grin',  iris: '#3f2a14', fx: ['hoops'], lip: '#e11d48' },
  { id: 'g10', gender: 'm', label: 'Dyed Mohawk',    bg: ['#c4b5fd', '#4c1d95'], pat: 'rays',  hair: 'mohawk',   hc: '#22d3ee', acc: 'none',   ac: '#fff',    top: '#111827', tee: 'bomber', gfx: 'stripe', face: 'smirk', iris: '#a855f7', fx: ['stud', 'chain'] },
  { id: 'g11', gender: 'f', label: 'Cap & Braids',   bg: ['#fde68a', '#b45309'], pat: 'stars', hair: 'braids',   hc: '#1b1410', acc: 'cap',    ac: '#2563eb', top: '#f43f5e', tee: 'hoodie', gfx: 'star',   face: 'smile', iris: '#4a2512', fx: ['hoops'], lip: '#e11d48' },
  { id: 'g12', gender: 'f', label: 'Y2K Bob',        bg: ['#f5d0fe', '#7e22ce'], pat: 'stars', hair: 'bob',      hc: '#3b82f6', acc: 'y2k',    ac: '#22d3ee', top: '#f0abfc', tee: 'tee',    gfx: 'star',   face: 'smirk', iris: '#2563eb', fx: ['gem'], lip: '#d946ef', blush: true },
  { id: 'g13', gender: 'm', label: 'Bald & Bright',  bg: ['#fde68a', '#d97706'], pat: 'rays',  hair: 'bald',   hc: '#15101a', acc: 'round',  ac: '#1b1830', top: '#0ea5e9', tee: 'tee',    gfx: 'bolt',   face: 'grin',  iris: '#3f2a14', fx: [] },
  { id: 'g14', gender: 'm', label: 'Locs & Specs',    bg: ['#a7f3d0', '#047857'], pat: 'dots',  hair: 'locs',   hc: '#2a160c', acc: 'round',  ac: '#facc15', top: '#f97316', tee: 'hoodie', gfx: 'star',   face: 'smile', iris: '#3f2a14', fx: ['stud'] },
  { id: 'g15', gender: 'f', label: 'Box Braids',      bg: ['#fbcfe8', '#be185d'], pat: 'stars', hair: 'braids', hc: '#1b1410', acc: 'none',   ac: '#fff',    top: '#8b5cf6', tee: 'tee',    gfx: 'heart',  face: 'grin',  iris: '#4a2512', fx: ['hoops'], lip: '#e11d48' },
  { id: 'g16', gender: 'm', label: 'Clean Dome',      bg: ['#c4b5fd', '#5b21b6'], pat: 'zig',   hair: 'bald',   hc: '#15101a', acc: 'y2k',    ac: '#22d3ee', top: '#111827', tee: 'bomber', gfx: 'stripe', face: 'smirk', iris: '#3f2a14', fx: ['chain'] },
  { id: 'g17', gender: 'f', label: 'Locs & Glasses',  bg: ['#fed7aa', '#c2410c'], pat: 'rays',  hair: 'locs',   hc: '#3b1d0f', acc: 'round',  ac: '#ec4899', top: '#14b8a6', tee: 'tee',    gfx: 'bolt',   face: 'smile', iris: '#4a2512', fx: ['hoops'], lip: '#be123c' },
  { id: 'g18', gender: 'm', label: 'Braids & Frames', bg: ['#bae6fd', '#0369a1'], pat: 'dots',  hair: 'braids', hc: '#15101a', acc: 'round',  ac: '#ef4444', top: '#facc15', tee: 'hoodie', gfx: 'star',   face: 'grin',  iris: '#3f2a14', fx: ['stud'] },
  { id: 'g19', gender: 'f', label: 'Bald Beauty',     bg: ['#fda4af', '#9f1239'], pat: 'stars', hair: 'bald',   hc: '#15101a', acc: 'phones', ac: '#facc15', top: '#f43f5e', tee: 'hoodie', gfx: 'heart',  face: 'grin',  iris: '#4a2512', fx: ['hoops', 'gem'], lip: '#e11d48', blush: true },
  { id: 'g20', gender: 'm', label: 'Dread Beanie',    bg: ['#fde047', '#4d7c0f'], pat: 'zig',   hair: 'locs',   hc: '#1b1410', acc: 'beanie', ac: '#10b981', top: '#0f172a', tee: 'bomber', gfx: 'bolt',   face: 'smirk', iris: '#3f2a14', fx: ['stud'] },
];

const HF = 'url(#hg)'; // hair gradient
const PATTERN = {
  rays: (c) => `<g opacity=".16" fill="${c}">${Array.from({ length: 14 }, (_, i) => { const a = (Math.PI * 2 / 14) * i, b = a + Math.PI / 14; return `<polygon points="64,64 ${(64 + Math.cos(a) * 130).toFixed(0)},${(64 + Math.sin(a) * 130).toFixed(0)} ${(64 + Math.cos(b) * 130).toFixed(0)},${(64 + Math.sin(b) * 130).toFixed(0)}"/>`; }).join('')}</g>`,
  dots: (c) => `<g fill="${c}" opacity=".22">${[10, 30, 50, 70, 90, 110].map((y, r) => [10, 30, 50, 70, 90, 110].map((x) => `<circle cx="${x + (r % 2) * 10}" cy="${y}" r="3.6"/>`).join('')).join('')}</g>`,
  stars: (c) => `<g fill="${c}" opacity=".5"><path d="M18 22 l3 7 7 3 -7 3 -3 7 -3 -7 -7 -3 7 -3z"/><path d="M104 40 l2.4 5.6 5.6 2.4 -5.6 2.4 -2.4 5.6 -2.4 -5.6 -5.6 -2.4 5.6 -2.4z"/><path d="M96 14 l1.6 3.8 3.8 1.6 -3.8 1.6 -1.6 3.8 -1.6 -3.8 -3.8 -1.6 3.8 -1.6z"/><path d="M14 74 l2 4.6 4.6 2 -4.6 2 -2 4.6 -2 -4.6 -4.6 -2 4.6 -2z"/></g>`,
  zig: (c) => `<g fill="none" stroke="${c}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity=".35">${[16, 38, 60].map((y) => `<path d="M-4 ${y} l10 -8 10 8 10 -8 10 8 10 -8 10 8 10 -8 10 8 10 -8 10 8 10 -8 10 8 10 -8"/>`).join('')}</g>`,
};

const bgLayer = (g) => `
<defs>
  <radialGradient id="bgg" cx="50%" cy="38%" r="80%"><stop offset="0" stop-color="${g.bg[0]}"/><stop offset="1" stop-color="${g.bg[1]}"/></radialGradient>
  <linearGradient id="hg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${shade(g.hc, 38)}"/><stop offset=".55" stop-color="${g.hc}"/><stop offset="1" stop-color="${shade(g.hc, -42)}"/></linearGradient>
  <linearGradient id="tg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${shade(g.top, 34)}"/><stop offset="1" stop-color="${shade(g.top, -36)}"/></linearGradient>
  <radialGradient id="cheek" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#ff5c8a" stop-opacity=".6"/><stop offset="1" stop-color="#ff5c8a" stop-opacity="0"/></radialGradient>
  <filter id="stk" x="-15%" y="-15%" width="130%" height="130%"><feMorphology in="SourceAlpha" operator="dilate" radius="3.2" result="d"/><feFlood flood-color="#ffffff"/><feComposite in2="d" operator="in" result="o"/><feDropShadow in="o" dx="0" dy="2" stdDeviation="2" flood-color="#000" flood-opacity=".35" result="s"/><feMerge><feMergeNode in="s"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
</defs>
<rect width="128" height="128" fill="url(#bgg)"/>
${PATTERN[g.pat](shade(g.bg[0], 40))}
<circle cx="106" cy="104" r="22" fill="${shade(g.bg[1], 26)}" opacity=".45"/><circle cx="18" cy="108" r="14" fill="${shade(g.bg[0], -10)}" opacity=".4"/>`;

const BACK = {
  afro: () => `<circle cx="64" cy="44" r="36" fill="${HF}" stroke="${O}" stroke-width="2.6"/>`,
  locs: (g) => [34, 40, 46, 82, 88, 94].map((x, i) => `<rect x="${x - 3}" y="42" width="7" height="${50 + (i % 3) * 8}" rx="3.5" fill="${HF}" stroke="${O}" stroke-width="2"/><path d="M${x} 50 v${34 + (i % 3) * 8}" stroke="${shade(g.hc, 40)}" stroke-width="1.4" opacity=".6"/>`).join(''),
  braids: (g) => `<g fill="${g.hc}" stroke="${O}" stroke-width="2">${[0, 1, 2, 3, 4].map((i) => `<ellipse cx="38" cy="${70 + i * 9}" rx="5" ry="5.5" fill="${i % 2 ? shade(g.hc, 22) : g.hc}"/><ellipse cx="90" cy="${70 + i * 9}" rx="5" ry="5.5" fill="${i % 2 ? shade(g.hc, 22) : g.hc}"/>`).join('')}</g><circle cx="38" cy="118" r="3.4" fill="#fbbf24" stroke="${O}" stroke-width="1.4"/><circle cx="90" cy="118" r="3.4" fill="#fbbf24" stroke="${O}" stroke-width="1.4"/>`,
  buns: () => `<circle cx="38" cy="26" r="11" fill="${HF}" stroke="${O}" stroke-width="2.4"/><circle cx="90" cy="26" r="11" fill="${HF}" stroke="${O}" stroke-width="2.4"/>`,
  pigtails: () => `<path d="M36 52 C20 56 16 78 24 98 C30 90 34 76 38 66Z M92 52 C108 56 112 78 104 98 C98 90 94 76 90 66Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  bob: () => `<path d="M34 44 C30 70 32 92 40 98 L50 80 L78 80 L88 98 C96 92 98 70 94 44Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  wavy: () => `<path d="M34 46 C26 70 30 92 22 108 C40 106 44 94 44 80Z M94 46 C102 70 98 92 106 108 C88 106 84 94 84 80Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  hijab: () => `<path d="M26 104 C22 56 36 22 64 22 C92 22 106 56 102 104 C92 112 36 112 26 104Z" fill="${HF}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`,
};
const FRONT = {
  afro: () => `<path d="M40 52 C42 36 52 30 64 30 C76 30 86 36 88 52 C82 44 74 40 64 40 C54 40 46 44 40 52Z" fill="${HF}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>`,
  locs: () => `<path d="M38 58 C36 34 48 26 64 26 C80 26 92 34 90 58 C86 44 76 38 64 38 C52 38 42 44 38 58Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  braids: (g) => `<path d="M38 58 C36 34 48 26 64 26 C80 26 92 34 90 58 C86 44 76 36 64 36 C52 36 42 44 38 58Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M64 28 V40 M52 31 L55 41 M76 31 L73 41" stroke="${shade(g.hc, 38)}" stroke-width="1.5"/>`,
  buns: () => `<path d="M38 56 C36 32 50 26 64 26 C78 26 92 32 90 56 C84 42 76 38 64 38 C52 38 44 42 38 56Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  pigtails: () => `<path d="M38 56 C36 32 50 25 64 25 C78 25 92 32 90 56 C80 46 74 38 62 36 C54 42 46 46 38 56Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><circle cx="31" cy="64" r="4" fill="#fff" stroke="${O}" stroke-width="1.6"/><circle cx="97" cy="64" r="4" fill="#fff" stroke="${O}" stroke-width="1.6"/>`,
  fade: () => `<path d="M39 56 C37 36 48 29 64 29 C80 29 91 36 89 56 C86 46 78 42 64 42 C50 42 42 46 39 56Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 52 L44 58 M88 52 L84 58" stroke="${O}" stroke-width="1.6" opacity=".5"/>`,
  curly: () => `<g fill="${HF}" stroke="${O}" stroke-width="2.2">${[[44, 36], [54, 28], [66, 26], [77, 29], [85, 38], [50, 40], [64, 36], [78, 40]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="9"/>`).join('')}</g>`,
  bob: () => `<path d="M38 60 C36 34 48 26 64 26 C80 26 92 34 90 60 C84 46 74 40 62 38 C54 46 46 52 38 60Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  buzz: () => `<path d="M40 52 C40 36 50 30 64 30 C78 30 88 36 88 52 C84 42 76 38 64 38 C52 38 44 42 40 52Z" fill="${HF}" opacity=".9" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  mohawk: (g) => `<path d="M56 44 L54 16 L60 24 L64 10 L68 24 L74 16 L72 44Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 52 C42 42 50 40 56 42 L72 42 C78 40 86 42 88 52 C84 46 76 44 64 44 C52 44 44 46 40 52Z" fill="${shade(g.hc, -80)}" opacity=".5"/><path d="M62 18 L61 38" stroke="#fff" stroke-width="2" opacity=".5" stroke-linecap="round"/>`,
  wavy: () => `<path d="M38 60 C34 32 50 24 66 26 C82 28 94 36 90 60 C84 44 74 38 62 40 C50 42 44 50 38 60Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>`,
  hijab: () => `<path d="M38 62 C34 34 48 24 64 24 C80 24 94 34 90 62 C86 46 78 40 64 40 C50 40 42 46 38 62Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M52 30 Q64 24 78 32" stroke="#fff" stroke-width="2.4" fill="none" opacity=".4" stroke-linecap="round"/>`,
};
FRONT.bald = () => '';
const SHINE = { mohawk: 1, buzz: 1, curly: 1, hijab: 1 };

const ACC = {
  phones: (c) => `<path d="M36 58 C32 24 96 24 92 58" fill="none" stroke="${O}" stroke-width="8" stroke-linecap="round"/><path d="M36 58 C32 24 96 24 92 58" fill="none" stroke="${c}" stroke-width="4.6" stroke-linecap="round"/>
    <rect x="28" y="51" width="12" height="22" rx="6" fill="${c}" stroke="${O}" stroke-width="2.4"/><rect x="88" y="51" width="12" height="22" rx="6" fill="${c}" stroke="${O}" stroke-width="2.4"/>
    <rect x="31" y="56" width="3" height="12" rx="1.5" fill="#fff" opacity=".55"/><rect x="94" y="56" width="3" height="12" rx="1.5" fill="#fff" opacity=".55"/>`,
  bucket: (c) => `<path d="M30 46 C34 38 48 36 64 36 C80 36 94 38 98 46 C90 50 38 50 30 46Z" fill="${shade(c, -28)}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M38 42 C38 24 50 18 64 18 C78 18 90 24 90 42 C80 46 48 46 38 42Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 38 C52 41 76 41 88 38" fill="none" stroke="${O}" stroke-width="1.6" opacity=".6"/><path d="M48 26 Q56 21 66 22" stroke="#fff" stroke-width="2.6" fill="none" opacity=".55" stroke-linecap="round"/>`,
  beanie: (c) => `<path d="M37 48 C36 24 50 18 64 18 C78 18 92 24 91 48 C80 52 48 52 37 48Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M44 24 v22 M54 20 v26 M64 18 v28 M74 20 v26 M84 24 v22" stroke="${shade(c, -34)}" stroke-width="1.6" opacity=".6"/><rect x="36" y="42" width="56" height="9" rx="4" fill="${shade(c, -38)}" stroke="${O}" stroke-width="2"/><circle cx="64" cy="14" r="6.5" fill="#fff" stroke="${O}" stroke-width="2"/>`,
  cap: (c) => `<path d="M38 46 C38 26 50 20 64 20 C78 20 90 26 90 46 C80 42 48 42 38 46Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M42 44 C36 48 20 48 14 44 C22 38 36 38 44 40Z" fill="${shade(c, -32)}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><circle cx="64" cy="20" r="2.6" fill="#fff"/><path d="M52 28 Q60 24 70 26" stroke="#fff" stroke-width="2.4" fill="none" opacity=".5" stroke-linecap="round"/>`,
  y2k: (c) => `<rect x="37" y="52" width="22" height="14" rx="7" fill="${c}" stroke="${O}" stroke-width="2.4"/><rect x="69" y="52" width="22" height="14" rx="7" fill="${c}" stroke="${O}" stroke-width="2.4"/><path d="M59 57 H69" stroke="${O}" stroke-width="2.6"/><path d="M41 55.5 L48 55.5 M73 55.5 L80 55.5" stroke="#fff" stroke-width="2.2" stroke-linecap="round" opacity=".8"/><path d="M37 58 L31 55 M91 58 L97 55" stroke="${O}" stroke-width="2.4"/>`,
  band: (c) => `<path d="M38 44 C46 38 82 38 90 44 L90 51 C82 45 46 45 38 51Z" fill="${c}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M46 43 l4 5 M60 41 l4 5 M74 41 l4 5" stroke="#fff" stroke-width="1.8" opacity=".6" stroke-linecap="round"/>`,
  round: (c) => `<circle cx="54" cy="58" r="9.5" fill="#fff" fill-opacity=".14" stroke="${c}" stroke-width="3.2"/><circle cx="74" cy="58" r="9.5" fill="#fff" fill-opacity=".14" stroke="${c}" stroke-width="3.2"/><path d="M63.5 58 H64.5" stroke="${c}" stroke-width="3"/><path d="M48 54 Q51 51 55 52" stroke="#fff" stroke-width="2" fill="none" opacity=".7" stroke-linecap="round"/>`,
  cowboy: (c) => `<ellipse cx="64" cy="40" rx="42" ry="9" fill="${shade(c, -26)}" stroke="${O}" stroke-width="2.4"/><path d="M42 40 C40 16 52 10 64 12 C76 10 88 16 86 40 C78 44 50 44 42 40Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M42 34 C54 38 74 38 86 34" fill="none" stroke="#fde68a" stroke-width="4"/><path d="M52 18 Q60 13 70 15" stroke="#fff" stroke-width="2.4" fill="none" opacity=".4" stroke-linecap="round"/>`,
  pirate: (c) => `<path d="M24 46 C30 30 46 20 64 20 C82 20 98 30 104 46 C92 38 80 36 64 36 C48 36 36 38 24 46Z" fill="#1b1830" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M36 38 C44 28 54 26 64 28 C74 26 84 28 92 38 C84 34 74 33 64 33 C54 33 44 34 36 38Z" fill="${c}" stroke="${O}" stroke-width="1.6"/><circle cx="64" cy="26" r="5" fill="#fff"/><circle cx="62.2" cy="25.4" r="1.2" fill="#1b1830"/><circle cx="65.8" cy="25.4" r="1.2" fill="#1b1830"/><path d="M60 29 h8 M62 29 v2 M66 29 v2" stroke="#1b1830" stroke-width="1"/>`,
  tophat: (c) => `<ellipse cx="64" cy="38" rx="32" ry="7" fill="${c}" stroke="${O}" stroke-width="2.4"/><path d="M42 38 L45 8 C56 4 72 4 83 8 L86 38 C76 42 52 42 42 38Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M43 31 C54 35 74 35 85 31 L86 38 C76 42 52 42 42 38Z" fill="#dc2626" stroke="${O}" stroke-width="1.6"/><path d="M50 12 L51 28" stroke="#fff" stroke-width="2.6" opacity=".35" stroke-linecap="round"/>`,
  flatcap: (c) => `<path d="M34 46 C34 28 48 22 66 22 C86 22 96 32 94 46 C80 42 50 42 34 46Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M34 46 C40 50 66 50 94 46 C94 44 90 44 88 43 C70 47 50 46 40 43Z" fill="${shade(c, -30)}" stroke="${O}" stroke-width="1.8"/><circle cx="64" cy="22" r="2.6" fill="${shade(c, -30)}"/>`,
  partyhat: (c) => `<path d="M50 38 L70 4 L84 38Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M56 28 L74 33 M60 20 L78 26 M64 12 L72 15" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".8"/><circle cx="70" cy="4" r="5" fill="#fde047" stroke="${O}" stroke-width="2"/>`,
  tiara: (c) => `<path d="M42 40 L46 24 L54 34 L64 18 L74 34 L82 24 L86 40 C74 36 54 36 42 40Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><circle cx="64" cy="26" r="3.2" fill="#67e8f9" stroke="${O}" stroke-width="1.2"/><circle cx="50" cy="31" r="2" fill="#f9a8d4"/><circle cx="78" cy="31" r="2" fill="#f9a8d4"/>`,
  bandana: (c) => `<path d="M36 46 C38 28 50 22 64 22 C78 22 90 28 92 46 C80 40 48 40 36 46Z" fill="${c}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><g fill="#fff" opacity=".85"><circle cx="48" cy="34" r="2"/><circle cx="60" cy="29" r="2"/><circle cx="72" cy="29" r="2"/><circle cx="82" cy="35" r="2"/><circle cx="64" cy="37" r="2"/></g><path d="M92 44 l10 4 -8 4z" fill="${c}" stroke="${O}" stroke-width="2"/>`,
  none: () => '',
};

const GFX = {
  bolt: () => `<polygon points="66,100 56,115 63,115 59,126 74,109 67,109 71,100" fill="#fde047" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/>`,
  star: () => `<polygon points="64,100 67.5,108 76,109 69.5,114.5 71.5,123 64,118.5 56.5,123 58.5,114.5 52,109 60.5,108" fill="#fff" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/>`,
  heart: () => `<path d="M64 124 C50 114 52 102 59 102 C62 102 64 105 64 106 C64 105 66 102 69 102 C76 102 78 114 64 124Z" fill="#ff4d8d" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/>`,
  smile: () => `<circle cx="64" cy="112" r="10" fill="#fde047" stroke="${O}" stroke-width="1.8"/><circle cx="60.5" cy="109" r="1.4" fill="${O}"/><circle cx="67.5" cy="109" r="1.4" fill="${O}"/><path d="M59 114.5 Q64 119 69 114.5" stroke="${O}" stroke-width="1.6" fill="none" stroke-linecap="round"/>`,
  stripe: () => `<rect x="14" y="108" width="100" height="5" fill="#fff" opacity=".7"/><rect x="14" y="116" width="100" height="3" fill="#fff" opacity=".4"/>`,
};

const mouthSvg = (g) => {
  const lip = g.lip ? (g.face === 'grin' ? `<path d="M53 71 Q64 83 75 71" stroke="${g.lip}" stroke-width="3.6" fill="none" stroke-linecap="round"/>` : g.face === 'smile' ? `<path d="M55 72 Q64 80 73 72" stroke="${g.lip}" stroke-width="3.8" fill="none" stroke-linecap="round"/>` : `<path d="M56 73 Q64 77 74 70" stroke="${g.lip}" stroke-width="3.8" fill="none" stroke-linecap="round"/>`) : '';
  const m = g.face === 'grin' ? `<path d="M54 72 Q64 82 74 72 Q64 76 54 72Z" fill="#fff" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`
    : g.face === 'smile' ? `<path d="M55 72 Q64 80 73 72" stroke="${O}" stroke-width="2.4" fill="none" stroke-linecap="round"/>`
      : `<path d="M56 73 Q64 77 74 70" stroke="${O}" stroke-width="2.4" fill="none" stroke-linecap="round"/>`;
  return lip + m;
};

const torso = (g, sk) => {
  const d = shade(g.top, -40), dark = shade(sk, -28);
  const body = `<path d="M12 128 C14 102 36 92 64 92 C92 92 114 102 116 128Z" fill="url(#tg)" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`;
  const neck = `<rect x="52" y="78" width="24" height="20" rx="6" fill="${dark}" stroke="${O}" stroke-width="2.4"/>`;
  const gfx = GFX[g.gfx] ? GFX[g.gfx]() : '';
  if (g.tee === 'vest') return body.replace('url(#tg)', '#f8fafc') + neck + `<path d="M30 128 C30 108 40 98 52 94 L64 128Z M98 128 C98 108 88 98 76 94 L64 128Z" fill="${g.top}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M50 92 L64 110 L78 92 L72 90 L64 98 L56 90Z" fill="#fff" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><path d="M52 100 L58 108 M76 100 L70 108" stroke="#fff" stroke-width="1.4" opacity=".5"/><circle cx="58" cy="118" r="1.6" fill="#fbbf24"/><circle cx="58" cy="124" r="1.6" fill="#fbbf24"/>` + `<path d="M56 96 L64 99 L72 96 L72 104 L64 101 L56 104Z" fill="#dc2626" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/><circle cx="64" cy="100" r="2.4" fill="#b91c1c" stroke="${O}" stroke-width="1.2"/>`;
  if (g.tee === 'tux') return `<path d="M12 128 C14 102 36 92 64 92 C92 92 114 102 116 128Z" fill="#15131f" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>` + neck + `<path d="M46 94 L64 128 L82 94 L72 92 L64 104 L56 92Z" fill="#f8fafc" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><path d="M40 100 L52 126 M88 100 L76 126" stroke="#3b3552" stroke-width="3" stroke-linecap="round"/><path d="M55 98 L64 101.5 L73 98 L73 106 L64 103 L55 106Z" fill="${g.top}" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/><circle cx="64" cy="102" r="2.4" fill="${shade(g.top, -30)}" stroke="${O}" stroke-width="1.2"/>`;
  if (g.tee === 'apron') return body.replace('url(#tg)', '#fef3c7') + neck + `<path d="M44 96 L84 96 L90 128 L38 128Z" fill="${g.top}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M44 96 L50 90 M84 96 L78 90" stroke="${g.top}" stroke-width="5" stroke-linecap="round"/><path d="M44 96 L50 90 M84 96 L78 90" stroke="${O}" stroke-width="1.4" stroke-linecap="round" opacity=".6"/><rect x="54" y="110" width="20" height="10" rx="2" fill="${shade(g.top, -22)}" stroke="${O}" stroke-width="1.6"/>`;
  if (g.tee === 'hawaii') return body + neck + `<g fill="#fde047" stroke="${O}" stroke-width="1">${[[30, 112], [46, 120], [84, 114], [98, 122], [64, 124], [22, 124]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="4.4"/>`).join('')}</g><g fill="#fb7185">${[[30, 112], [46, 120], [84, 114], [98, 122], [64, 124], [22, 124]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="1.6"/>`).join('')}</g><path d="M46 93 L64 112 L82 93 L74 91 L64 100 L54 91Z" fill="${shade(g.top, 40)}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`;
  if (g.tee === 'hoodie') return `<path d="M30 100 C34 86 50 82 64 98 C78 82 94 86 98 100 L98 112 L30 112Z" fill="${d}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>` + body + neck
    + `<path d="M50 92 C56 102 72 102 78 92" fill="${d}" stroke="${O}" stroke-width="2.2"/><path d="M57 98 V112 M71 98 V112" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/><circle cx="57" cy="113" r="1.8" fill="#fff"/><circle cx="71" cy="113" r="1.8" fill="#fff"/>` + (g.gfx === 'stripe' ? gfx : '');
  if (g.tee === 'bomber') return body + neck + `<path d="M50 92 C56 100 72 100 78 92 L82 102 C72 110 56 110 46 102Z" fill="${d}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M64 102 V128" stroke="${O}" stroke-width="2.4"/><path d="M12 118 H116" stroke="#fff" stroke-width="3" opacity=".5"/><path d="M13 123 H115" stroke="${g.ac}" stroke-width="3" opacity=".9"/>`;
  return body + neck + `<path d="M48 93 C56 104 72 104 80 93" fill="${shade(sk, -18)}" stroke="${O}" stroke-width="2.2"/>` + (g.gfx === 'stripe' ? gfx : '');
};

const fxFront = (g, sk) => (g.fx || []).map((f) => {
  if (f === 'hoops') return `<circle cx="40" cy="67" r="4.6" fill="none" stroke="#fbbf24" stroke-width="2.2"/><circle cx="88" cy="67" r="4.6" fill="none" stroke="#fbbf24" stroke-width="2.2"/>`;
  if (f === 'freckles') return `<g fill="${shade(sk, -52)}" opacity=".7">${[[50, 66], [54, 69], [47, 69], [78, 66], [74, 69], [81, 69]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="1"/>`).join('')}</g>`;
  if (f === 'gem') return `<path d="M82 62 l2 3 3 .6 -2.4 2.2 .8 3.2 -3.4 -1.8 -3.4 1.8 .8 -3.2 -2.4 -2.2 3 -.6z" fill="#67e8f9" stroke="${O}" stroke-width="1"/>`;
  if (f === 'stud') return `<circle cx="67" cy="66" r="1.5" fill="#fde047" stroke="${O}" stroke-width=".8"/>`;
  return '';
}).join('');
const fxChain = (g) => (g.fx || []).includes('chain') ? `<path d="M46 99 C52 118 76 118 82 99" fill="none" stroke="${O}" stroke-width="5" stroke-linecap="round"/><path d="M46 99 C52 118 76 118 82 99" fill="none" stroke="#fbbf24" stroke-width="2.8" stroke-linecap="round" stroke-dasharray="5 2"/><circle cx="64" cy="115" r="4" fill="#fbbf24" stroke="${O}" stroke-width="1.6"/>` : '';

const beardSvg = (g) => {
  if (!g.beard) return '';
  const c = g.beard.c, st = g.beard.s;
  const body = st === 'full' ? `<path d="M41 62 C40 92 88 92 87 62 C84 74 78 70 64 72 C50 70 44 74 41 62Z" fill="${c}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>`
    : st === 'goatee' ? `<path d="M54 74 C54 88 74 88 74 74 C70 79 58 79 54 74Z" fill="${c}" stroke="${O}" stroke-width="2"/>` : '';
  const stache = `<path d="M50 70 C54 64 62 66 64 69 C66 66 74 64 78 70 C74 72 68 70 64 72 C60 70 54 72 50 70Z" fill="${c}" stroke="${O}" stroke-width="1.8" stroke-linejoin="round"/>`;
  return body + (st === 'stache' || st === 'full' || st === 'goatee' ? stache : '');
};

// handheld props, bottom-right of the bust
const PROP = {
  shaker: () => `<path d="M92 90 L112 90 L108 124 L96 124Z" fill="#cbd5e1" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M94 80 L110 80 L112 90 L92 90Z" fill="#94a3b8" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><rect x="98" y="75" width="8" height="6" rx="2" fill="#94a3b8" stroke="${O}" stroke-width="1.8"/><path d="M97 96 L99 120" stroke="#fff" stroke-width="2.4" opacity=".6" stroke-linecap="round"/>`,
  wine: () => `<path d="M90 82 L112 82 C112 98 106 106 101 106 C96 106 90 98 90 82Z" fill="#fff" fill-opacity=".35" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M91 90 L111 90 C110 99 106 105 101 105 C96 105 92 99 91 90Z" fill="#9f1239"/><path d="M101 106 V122 M92 124 H110" stroke="${O}" stroke-width="2.6" stroke-linecap="round"/><path d="M94 85 L95 92" stroke="#fff" stroke-width="2" opacity=".7" stroke-linecap="round"/>`,
  stein: () => `<path d="M88 84 L108 84 L106 120 C106 123 90 123 90 120Z" fill="#fbbf24" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M107 92 C118 92 118 110 106 110" fill="none" stroke="${O}" stroke-width="4.5"/><path d="M107 92 C118 92 118 110 106 110" fill="none" stroke="#e5e7eb" stroke-width="2"/><path d="M86 86 C84 76 92 74 94 78 C96 72 104 72 106 78 C110 76 112 84 108 86Z" fill="#fff" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><path d="M93 92 V114 M99 92 V114" stroke="#fde68a" stroke-width="2" opacity=".8" stroke-linecap="round"/>`,
  mic: () => `<rect x="100" y="98" width="8" height="28" rx="4" fill="#475569" stroke="${O}" stroke-width="2.2"/><circle cx="104" cy="90" r="10" fill="#e2e8f0" stroke="${O}" stroke-width="2.4"/><path d="M96 88 H112 M97 93 H111 M104 80 V100" stroke="#64748b" stroke-width="1.4"/><circle cx="101" cy="86" r="2.4" fill="#fff" opacity=".8"/>`,
  bottle: () => `<path d="M98 72 H106 V86 C112 92 114 98 114 104 V124 H90 V104 C90 98 92 92 98 86Z" fill="#14532d" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><rect x="97" y="68" width="10" height="6" rx="2" fill="#b45309" stroke="${O}" stroke-width="1.8"/><rect x="92" y="100" width="20" height="14" rx="2" fill="#fde68a" stroke="${O}" stroke-width="1.6"/><path d="M96 105 h12 M96 109 h8" stroke="#7c2d12" stroke-width="1.6" stroke-linecap="round"/><path d="M95 92 L95 98" stroke="#fff" stroke-width="2" opacity=".5" stroke-linecap="round"/>`,
  tumbler: () => `<path d="M88 88 L112 88 L109 122 L91 122Z" fill="#fff" fill-opacity=".3" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M90 100 L110 100 L108.6 120 L91.4 120Z" fill="#d97706"/><rect x="94" y="102" width="8" height="8" rx="1.6" fill="#fff" fill-opacity=".7" stroke="${O}" stroke-width="1"/><rect x="101" y="108" width="7" height="7" rx="1.6" fill="#fff" fill-opacity=".6" stroke="${O}" stroke-width="1"/><path d="M92 92 L93 98" stroke="#fff" stroke-width="2" opacity=".8" stroke-linecap="round"/>`,
  flute: () => `<path d="M98 76 L108 76 C108 92 106 104 103 106 C100 104 98 92 98 76Z" fill="#fff" fill-opacity=".35" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M99 84 L107 84 C107 94 105 102 103 104 C101 102 99 94 99 84Z" fill="#fde68a"/><circle cx="102" cy="96" r="1" fill="#fff"/><circle cx="104.5" cy="90" r="1" fill="#fff"/><circle cx="102.5" cy="86" r=".8" fill="#fff"/><path d="M103 106 V122 M95 124 H111" stroke="${O}" stroke-width="2.6" stroke-linecap="round"/>`,
  tiki: () => `<path d="M88 92 C88 108 96 122 104 122 C112 122 120 108 120 92Z" fill="#a16207" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M92 98 H116" stroke="#7c4a0a" stroke-width="2" opacity=".6"/><ellipse cx="104" cy="92" rx="16" ry="4.5" fill="#fb7185" stroke="${O}" stroke-width="2"/><path d="M108 90 L116 70" stroke="#16a34a" stroke-width="3" stroke-linecap="round"/><path d="M116 70 C122 70 124 78 118 80Z M116 70 C110 66 106 72 112 76Z" fill="#facc15" stroke="${O}" stroke-width="1.6"/><path d="M98 88 L94 76" stroke="#22d3ee" stroke-width="3" stroke-linecap="round"/><circle cx="94" cy="75" r="3" fill="#f43f5e" stroke="${O}" stroke-width="1.4"/>`,
  cards: () => `<g stroke="${O}" stroke-width="2" stroke-linejoin="round"><rect x="86" y="88" width="20" height="28" rx="3" fill="#fff" transform="rotate(-14 96 102)"/><rect x="92" y="86" width="20" height="28" rx="3" fill="#fff" transform="rotate(8 102 100)"/></g><path d="M102 96 l-4 6 4 6 4 -6z" fill="#dc2626" transform="rotate(8 102 100)"/><text x="94" y="100" font-size="10" font-weight="900" fill="#111" font-family="sans-serif" transform="rotate(-14 96 102)">A</text><path d="M91 108 l3 -3 3 3 -3 4z" fill="#111" transform="rotate(-14 96 102)"/>`,
  martini: () => `<path d="M88 84 L116 84 L102 106Z" fill="#fff" fill-opacity=".35" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M91 87 L113 87 L102 103Z" fill="#fb7185"/><path d="M102 106 V122 M93 124 H111" stroke="${O}" stroke-width="2.6" stroke-linecap="round"/><path d="M106 80 L100 96" stroke="#92400e" stroke-width="1.8" stroke-linecap="round"/><circle cx="108" cy="78" r="3.6" fill="#16a34a" stroke="${O}" stroke-width="1.4"/><circle cx="106.8" cy="77" r="1" fill="#fecaca"/>`,
  shot: () => `<path d="M92 96 L112 96 L109 122 L95 122Z" fill="#fff" fill-opacity=".35" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M93.6 106 L110.4 106 L109 121 L95 121Z" fill="#f59e0b"/><path d="M94 96 L106 86 L112 96Z" fill="#84cc16" stroke="${O}" stroke-width="1.6" stroke-linejoin="round"/>`,
  vinyl: () => `<circle cx="102" cy="104" r="16" fill="#18181b" stroke="${O}" stroke-width="2.2"/><circle cx="102" cy="104" r="11" fill="none" stroke="#3f3f46" stroke-width="1.2"/><circle cx="102" cy="104" r="5.5" fill="#ec4899"/><circle cx="102" cy="104" r="1.6" fill="#18181b"/><path d="M92 96 A14 14 0 0 1 104 90" stroke="#fff" stroke-width="2" fill="none" opacity=".35" stroke-linecap="round"/>`,
};

export function genzSvg(g, skin) {
  const sk = skin.hex, dark = shade(sk, -28), light = shade(sk, 26);
  const covered = g.acc === 'y2k';
  const lash = g.gender === 'f' ? `<path d="M49 54 l-3 -2 M51 52.4 l-1.4 -3 M79 54 l3 -2 M77 52.4 l1.4 -3" stroke="${O}" stroke-width="1.8" stroke-linecap="round"/>` : '';
  const eye = (cx) => `<ellipse cx="${cx}" cy="58" rx="5" ry="5.6" fill="#fff" stroke="${O}" stroke-width="1.7"/><circle cx="${cx + 1}" cy="59" r="3.5" fill="${g.iris}"/><circle cx="${cx + 1}" cy="59" r="1.8" fill="${O}"/><circle cx="${cx + 2.3}" cy="57.4" r="1.3" fill="#fff"/><circle cx="${cx - 0.6}" cy="60.8" r=".7" fill="#fff" opacity=".8"/>`;
  const eyes = covered ? '' : eye(54) + eye(74) + lash;
  const brows = covered ? '' : `<path d="M46 49 Q53 45 60 49 M68 49 Q75 45 82 49" stroke="${shade(g.hair === 'hijab' ? '#2a1a14' : g.hc, -52)}" stroke-width="3" stroke-linecap="round" fill="none"/>`;
  const cheeks = `<ellipse cx="46" cy="68" rx="${g.blush ? 7 : 5.4}" ry="${g.blush ? 4.5 : 3.4}" fill="url(#cheek)"/><ellipse cx="82" cy="68" rx="${g.blush ? 7 : 5.4}" ry="${g.blush ? 4.5 : 3.4}" fill="url(#cheek)"/>`;
  const shine = SHINE[g.hair] ? '' : `<path d="M47 36 Q57 29 72 32" stroke="#fff" stroke-width="3.2" fill="none" opacity=".4" stroke-linecap="round"/>`;
  const hijabDrape = g.hair === 'hijab' ? `<path d="M30 128 C28 108 40 98 64 100 C88 98 100 108 98 128Z" fill="${HF}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 112 Q64 104 88 112" stroke="#fff" stroke-width="2" fill="none" opacity=".35"/>` : '';
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128" shape-rendering="geometricPrecision">
${bgLayer(g)}
<g filter="url(#stk)">
${BACK[g.hair] ? BACK[g.hair](g) : ""}
${torso(g, sk)}
${hijabDrape}
<ellipse cx="40.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/><ellipse cx="87.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/>
<ellipse cx="64" cy="58" rx="24" ry="27.5" fill="${sk}" stroke="${O}" stroke-width="2.6"/>
<ellipse cx="57" cy="46" rx="12" ry="6" fill="${light}" opacity=".35"/>
${beardSvg(g)}
<path d="M61 63 L59 69 L64 70" stroke="${dark}" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
${cheeks}${eyes}${brows}${mouthSvg(g)}
${FRONT[g.hair](g)}
${shine}
${ACC[g.acc](g.ac)}
${fxFront(g, sk)}
${fxChain(g)}
${PROP[g.prop] ? PROP[g.prop](g) : ''}
</g>
</svg>`;
}

export { PATTERN, PROP, ACC, shade, O };
