// Original comic-style hero avatars, drawn in code — no third-party characters, names or emblems.
// Each hero is a layered SVG (background burst → cape → suit → neck/head/face → mask/hair/helmet → emblem),
// so every hero can be rendered in any skin shade.
const O = '#1b1830'; // outline ink

const clamp = (n) => Math.max(0, Math.min(255, Math.round(n)));
const shade = (hex, amt) => {
  const n = parseInt(hex.replace('#', ''), 16);
  const r = clamp((n >> 16) + amt), g = clamp(((n >> 8) & 255) + amt), b = clamp((n & 255) + amt);
  return '#' + [r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('');
};
const star = (cx, cy, R, r, pts = 5) => Array.from({ length: pts * 2 }, (_, i) => {
  const a = (Math.PI / pts) * i - Math.PI / 2, rad = i % 2 ? r : R;
  return `${(cx + Math.cos(a) * rad).toFixed(1)},${(cy + Math.sin(a) * rad).toFixed(1)}`;
}).join(' ');

// ── chest emblems, centred around (64,113) ──
const EMBLEM = {
  bolt: (c) => `<polygon points="66,99 55,115 63,115 59,128 74,109 66,109 71,99" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  star: (c) => `<polygon points="${star(64, 113, 12, 5)}" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  flame: (c) => `<path d="M64 99 C70 106 74 110 72 118 C71 124 67 127 64 127 C60 127 56 124 56 118 C56 113 60 111 61 106 C63 108 63 110 64 112 C66 108 65 104 64 99Z" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  ring: (c) => `<circle cx="64" cy="113" r="10" fill="none" stroke="${c}" stroke-width="5"/><circle cx="64" cy="113" r="10" fill="none" stroke="${O}" stroke-width="1" opacity=".5"/>`,
  hex: (c) => `<polygon points="64,101 74,107 74,119 64,125 54,119 54,107" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><polygon points="64,106 70,110 70,116 64,120 58,116 58,110" fill="#fff" opacity=".7"/>`,
  wing: (c) => `<path d="M64 112 C58 104 48 104 40 108 C48 110 52 114 56 120 C58 116 61 114 64 114 C67 114 70 116 72 120 C76 114 80 110 88 108 C80 104 70 104 64 112Z" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  crescent: (c) => `<path d="M70 101 C58 103 54 113 60 121 C64 126 71 127 76 124 C66 124 61 117 64 110 C65 106 67 103 70 101Z" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  crown: (c) => `<path d="M53 122 L55 106 L60 113 L64 103 L68 113 L73 106 L75 122Z" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  paw: (c) => `<ellipse cx="64" cy="118" rx="7" ry="6" fill="${c}" stroke="${O}" stroke-width="1.5"/><circle cx="55" cy="110" r="3" fill="${c}" stroke="${O}" stroke-width="1.5"/><circle cx="61" cy="105" r="3" fill="${c}" stroke="${O}" stroke-width="1.5"/><circle cx="67" cy="105" r="3" fill="${c}" stroke="${O}" stroke-width="1.5"/><circle cx="73" cy="110" r="3" fill="${c}" stroke="${O}" stroke-width="1.5"/>`,
  arrow: (c) => `<polygon points="64,100 74,114 67,114 67,127 61,127 61,114 54,114" fill="${c}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
  amulet: (c) => `<circle cx="64" cy="113" r="10" fill="${c}" stroke="${O}" stroke-width="2"/><circle cx="64" cy="113" r="4.5" fill="#fff" opacity=".85"/>`,
};

// ── the roster ──
// bg: [centre, edge] gradient · suit/trim: body colours · cape: back-layer colour (or null) · emblem: [shape, colour]
// gender f/m only drives the picker's Women/Men filter.
export const HEROES = [
  { id: 'h1', gender: 'm', label: 'Night Vigil',     bg: ['#4f7a8f', '#10202b'], suit: '#243b4a', trim: '#3a5d70', cape: '#0f1d28', emblem: ['ring', '#ff9f1c'],  face: 'serious', front: 'cowl-ears', cowl: '#243b4a' },
  { id: 'h2', gender: 'm', label: 'Crimson Speedster', bg: ['#ff9ad5', '#5a1146'], suit: '#d81b60', trim: '#ffe14d', cape: null,      emblem: ['bolt', '#ffe14d'],  face: 'grin',    front: 'cowl-fins', cowl: '#d81b60' },
  { id: 'h3', gender: 'f', label: 'Star Guardian',   bg: ['#6ee7d4', '#0b4f4a'], suit: '#00897b', trim: '#fff3c4', cape: '#ef6c00', emblem: ['star', '#ffe082'],  face: 'smile',   front: 'cowl-star', cowl: '#00897b', hair: '#f2c94c' },
  { id: 'h4', gender: 'm', label: 'Armored Genius',  bg: ['#9fa8ff', '#1a1f6b'], suit: '#1a237e', trim: '#ff9800', cape: null,      emblem: ['hex', '#ff9800'],   face: 'smirk',   front: 'helmet', helmet: '#5c6bc0', trimc: '#ff9800', beard: '#2b1a10' },
  { id: 'h5', gender: 'm', label: 'Sky Courier',     bg: ['#7be4ff', '#0d4a7a'], suit: '#0e8fa8', trim: '#e6f3f7', cape: null,      emblem: ['wing', '#e6f3f7'],  face: 'grin',    front: 'wing-helm', helmet: '#cfd8dc' },
  { id: 'h6', gender: 'f', label: 'Hooded Archer',   bg: ['#7be08b', '#0d4a2a'], suit: '#2e7d32', trim: '#a5d6a7', cape: '#1b5e20', emblem: ['arrow', '#fff59d'], face: 'smirk',   front: 'hood-mask', hood: '#2e7d32', mask: '#10261a' },
  { id: 'h7', gender: 'f', label: 'Warrior Princess', bg: ['#ffcf6b', '#7a2a1d'], suit: '#6a1b9a', trim: '#cfd8dc', cape: '#4a148c', emblem: ['crown', '#e0e0e0'], face: 'smile',   front: 'circlet', hair: '#1c1420' },
  { id: 'h8', gender: 'f', label: 'Storm Weaver',    bg: ['#b794ff', '#241354'], suit: '#41307a', trim: '#8be9ff', cape: '#2a1d57', emblem: ['bolt', '#8be9ff'],  face: 'serious', front: 'storm-hair', hair: '#ff7ac6', glow: '#7fe8ff' },
  { id: 'h9', gender: 'f', label: 'Cat Burglar',     bg: ['#ff8fcf', '#4a1456'], suit: '#241f33', trim: '#a259ff', cape: null,      emblem: ['paw', '#a259ff'],   face: 'smirk',   front: 'cat-cowl', cowl: '#241f33' },
  { id: 'h10', gender: 'f', label: 'Flame Heroine',  bg: ['#ffd166', '#7a2a0a'], suit: '#e8590c', trim: '#ffd43b', cape: null,      emblem: ['flame', '#ffd43b'], face: 'grin',    front: 'flame-hair', hair: '#ff7a1a', mask: '#9d0208' },
  { id: 'h11', gender: 'm', label: 'Mystic Sorcerer', bg: ['#9be7ff', '#1d2b6b'], suit: '#5d4037', trim: '#ffca28', cape: '#00796b', emblem: ['amulet', '#ffca28'], face: 'serious', front: 'sorcerer', hair: '#241915', beard: '#241915' },
  { id: 'h12', gender: 'm', label: 'Moon Warden',    bg: ['#d7e4ff', '#2c3a6b'], suit: '#cfd8ec', trim: '#8aa2d6', cape: '#9fb0d8', emblem: ['crescent', '#3a4b86'], face: 'serious', front: 'hood-mask', hood: '#e9eefb', mask: '#2c3a6b' },
];

const bg = (h) => `
<defs>
  <radialGradient id="g" cx="50%" cy="36%" r="78%"><stop offset="0" stop-color="${h.bg[0]}"/><stop offset="1" stop-color="${h.bg[1]}"/></radialGradient>
  <pattern id="d" width="7" height="7" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.2" fill="#000" opacity=".13"/></pattern>
  <linearGradient id="sg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${shade(h.suit, 36)}"/><stop offset="1" stop-color="${shade(h.suit, -30)}"/></linearGradient>
  <linearGradient id="cg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${shade(h.cape || h.suit, 26)}"/><stop offset="1" stop-color="${shade(h.cape || h.suit, -34)}"/></linearGradient>
  <radialGradient id="ck" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#ff5c8a" stop-opacity=".5"/><stop offset="1" stop-color="#ff5c8a" stop-opacity="0"/></radialGradient>
  <filter id="stk" x="-15%" y="-15%" width="130%" height="130%"><feMorphology in="SourceAlpha" operator="dilate" radius="3" result="d"/><feFlood flood-color="#ffffff"/><feComposite in2="d" operator="in" result="o"/><feDropShadow in="o" dx="0" dy="2" stdDeviation="2" flood-color="#000" flood-opacity=".35" result="s"/><feMerge><feMergeNode in="s"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
</defs>
<rect width="128" height="128" fill="url(#g)"/>
<g opacity=".13" fill="#fff">${Array.from({ length: 14 }, (_, i) => { const a = (Math.PI * 2 / 14) * i, b = a + Math.PI / 14; return `<polygon points="64,62 ${(64 + Math.cos(a) * 120).toFixed(0)},${(62 + Math.sin(a) * 120).toFixed(0)} ${(64 + Math.cos(b) * 120).toFixed(0)},${(62 + Math.sin(b) * 120).toFixed(0)}"/>`; }).join('')}</g>
<rect width="128" height="128" fill="url(#d)"/>
<g fill="#fff" opacity=".7"><path d="M16 20 l2.2 5.6 5.6 2.2 -5.6 2.2 -2.2 5.6 -2.2 -5.6 -5.6 -2.2 5.6 -2.2z"/><path d="M108 34 l1.8 4.4 4.4 1.8 -4.4 1.8 -1.8 4.4 -1.8 -4.4 -4.4 -1.8 4.4 -1.8z"/><path d="M100 12 l1.4 3.4 3.4 1.4 -3.4 1.4 -1.4 3.4 -1.4 -3.4 -3.4 -1.4 3.4 -1.4z"/></g>`;

const eyes = (h, skin) => {
  const lens = `<ellipse cx="54" cy="58" rx="5.4" ry="4.6" fill="#fff" stroke="${O}" stroke-width="1.6"/><ellipse cx="74" cy="58" rx="5.4" ry="4.6" fill="#fff" stroke="${O}" stroke-width="1.6"/>
    <circle cx="55.2" cy="59" r="3.3" fill="${h.iris || '#2f7be8'}"/><circle cx="75.2" cy="59" r="3.3" fill="${h.iris || '#2f7be8'}"/><circle cx="55.2" cy="59" r="1.8" fill="${O}"/><circle cx="75.2" cy="59" r="1.8" fill="${O}"/><circle cx="56.4" cy="57.6" r="1.2" fill="#fff"/><circle cx="76.4" cy="57.6" r="1.2" fill="#fff"/>`;
  const brow = h.face === 'serious' ? `<path d="M47 51 L60 54 M81 51 L68 54" stroke="${O}" stroke-width="3" stroke-linecap="round"/>`
    : h.face === 'smirk' ? `<path d="M47 52 Q53 49 60 52 M68 53 Q75 49 81 51" stroke="${O}" stroke-width="2.6" stroke-linecap="round" fill="none"/>`
      : `<path d="M47 52 Q53 49 60 52 M68 52 Q75 49 81 52" stroke="${O}" stroke-width="2.6" stroke-linecap="round" fill="none"/>`;
  return { lens, brow };
};

const mouth = (h, skin) => {
  const dark = shade(skin, -70);
  if (h.face === 'grin') return `<path d="M54 72 Q64 82 74 72 Q64 76 54 72Z" fill="#fff" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`;
  if (h.face === 'smile') return `<path d="M55 72 Q64 79 73 72" stroke="${O}" stroke-width="2.6" fill="none" stroke-linecap="round"/>`;
  if (h.face === 'smirk') return `<path d="M56 73 Q64 76 74 70" stroke="${O}" stroke-width="2.6" fill="none" stroke-linecap="round"/>`;
  return `<path d="M56 74 L72 74" stroke="${O}" stroke-width="2.8" stroke-linecap="round"/>`;
};

// front-layer pieces (hair, cowl, mask, helmet …) keyed by h.front
const FRONT = {
  'cowl-ears': (h) => `
    <path d="M41 46 L28 32 L52 36Z M87 46 L100 32 L76 36Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M39 68 C36 40 48 28 64 28 C80 28 92 40 89 68 C84 60 76 58 64 58 C52 58 44 60 39 68Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M45 54 L58 58 L56 63 L44 59Z M83 54 L70 58 L72 63 L84 59Z" fill="#fff" stroke="${O}" stroke-width="1.4" stroke-linejoin="round"/>`,
  'cowl-fins': (h) => `
    <path d="M40 54 C28 52 20 44 14 40 C22 36 30 36 40 42Z M88 54 C100 52 108 44 114 40 C106 36 98 36 88 42Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M39 62 C36 36 48 27 64 27 C80 27 92 36 89 62 C86 52 78 47 64 47 C50 47 42 52 39 62Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M64 30 L58 44 L64 41 L70 44Z" fill="#ffd23f" stroke="${O}" stroke-width="1.6" stroke-linejoin="round"/>`,
  'cowl-star': (h) => `
    <path d="M40 66 C38 36 50 26 64 26 C78 26 90 36 88 66 C86 54 78 46 64 46 C50 46 42 54 40 66Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M88 44 C104 44 106 66 98 84 C98 70 94 58 88 54Z" fill="${h.hair}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>
    <polygon points="${star(64, 37, 7, 3)}" fill="#fff" stroke="${O}" stroke-width="1.4" stroke-linejoin="round"/>`,
  'helmet': (h) => `
    <path d="M40 60 C37 34 50 24 64 24 C78 24 91 34 88 60 C84 50 76 44 64 44 C52 44 44 50 40 60Z" fill="${h.helmet}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M60 24 L64 44 L68 24Z" fill="${h.trimc}" stroke="${O}" stroke-width="1.6" stroke-linejoin="round"/>
    <path d="M38 58 C34 66 36 78 42 84 L46 74 C42 70 41 64 42 58Z M90 58 C94 66 92 78 86 84 L82 74 C86 70 87 64 86 58Z" fill="${h.helmet}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>
    <path d="M55 77 Q64 85 73 77 Q64 81 55 77Z" fill="${h.beard}"/><path d="M52 71 Q58 69 64 71 Q70 69 76 71" stroke="${h.beard}" stroke-width="2.4" fill="none" stroke-linecap="round"/>`,
  'wing-helm': (h) => `
    <path d="M40 42 C26 40 14 28 8 20 C22 16 34 22 42 32Z M88 42 C102 40 114 28 120 20 C106 16 94 22 86 32Z" fill="#eceff1" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>
    <path d="M39 56 C36 34 49 24 64 24 C79 24 92 34 89 56 C84 46 76 42 64 42 C52 42 44 46 39 56Z" fill="${h.helmet}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <rect x="42" y="48" width="44" height="8" rx="4" fill="#00b8d4" opacity=".75" stroke="${O}" stroke-width="1.6"/>`,
  'hood-mask': (h) => `
    <path d="M32 74 C26 44 40 20 64 20 C88 20 102 44 96 74 C92 60 88 52 80 46 C72 42 56 42 48 46 C40 52 36 60 32 74Z" fill="${h.hood}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M38 52 Q64 42 90 52 Q92 64 80 66 Q72 61 64 64 Q56 61 48 66 Q36 64 38 52Z" fill="${h.mask}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>
    <ellipse cx="54" cy="58" rx="5.4" ry="3.6" fill="#fff"/><ellipse cx="74" cy="58" rx="5.4" ry="3.6" fill="#fff"/><circle cx="55.4" cy="58.4" r="2" fill="${O}"/><circle cx="75.4" cy="58.4" r="2" fill="${O}"/>`,
  'circlet': (h) => `
    <path d="M38 70 C34 38 46 25 64 25 C82 25 94 38 90 70 C92 82 96 96 98 112 C86 102 88 80 86 60 C82 46 76 40 64 40 C52 40 46 46 42 60 C40 80 42 102 30 112 C32 96 36 82 38 70Z" fill="${h.hair}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M41 46 Q64 34 87 46" stroke="#e0e0e0" stroke-width="4.5" fill="none" stroke-linecap="round"/><path d="M41 46 Q64 34 87 46" stroke="${O}" stroke-width="1" fill="none" opacity=".4"/>
    <polygon points="64,33 69,40 64,47 59,40" fill="#26c6da" stroke="${O}" stroke-width="1.6" stroke-linejoin="round"/>`,
  'storm-hair': (h) => `
    <path d="M36 70 C30 36 46 22 64 22 C82 22 98 36 92 70 C96 86 100 104 104 122 C88 112 90 88 86 66 C82 48 76 40 64 40 C52 40 46 48 42 66 C38 88 40 112 24 122 C28 104 32 86 36 70Z" fill="${h.hair}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M64 24 L58 40 L66 36 L60 52" stroke="${h.glow}" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>`,
  'cat-cowl': (h) => `
    <path d="M40 44 L34 14 L58 32Z M88 44 L94 14 L70 32Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M40 40 L38 22 L50 32Z M88 40 L90 22 L78 32Z" fill="#a259ff" opacity=".8"/>
    <path d="M39 64 C36 38 48 27 64 27 C80 27 92 38 89 64 C84 54 76 50 64 50 C52 50 44 54 39 64Z" fill="${h.cowl}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <rect x="42" y="38" width="44" height="9" rx="4.5" fill="#ffe082" opacity=".9" stroke="${O}" stroke-width="1.6"/><circle cx="52" cy="42.5" r="3.4" fill="#4dd0e1" stroke="${O}" stroke-width="1.2"/><circle cx="76" cy="42.5" r="3.4" fill="#4dd0e1" stroke="${O}" stroke-width="1.2"/>`,
  'flame-hair': (h) => `
    <path d="M38 66 C32 48 36 34 46 28 C46 36 50 38 54 36 C52 28 56 20 62 14 C64 24 70 26 74 22 C76 28 82 30 82 38 C88 34 92 28 94 22 C100 36 96 54 90 66 C84 52 76 46 64 46 C52 46 44 52 38 66Z" fill="${h.hair}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M50 30 C54 38 58 38 60 30 C62 38 68 38 72 30" stroke="#ffd43b" stroke-width="3" fill="none" stroke-linecap="round"/>
    <path d="M38 52 Q64 42 90 52 Q92 64 80 66 Q72 61 64 64 Q56 61 48 66 Q36 64 38 52Z" fill="${h.mask}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>
    <ellipse cx="54" cy="58" rx="5.2" ry="3.6" fill="#fff"/><ellipse cx="74" cy="58" rx="5.2" ry="3.6" fill="#fff"/><circle cx="55.2" cy="58.4" r="2" fill="${O}"/><circle cx="75.2" cy="58.4" r="2" fill="${O}"/>`,
  'sorcerer': (h) => `
    <path d="M38 58 C36 34 48 24 64 24 C80 24 92 34 90 58 C88 44 80 36 64 36 C48 36 40 44 38 58Z" fill="${h.hair}" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/>
    <path d="M38 54 C40 50 42 48 44 46 L44 56Z M90 54 C88 50 86 48 84 46 L84 56Z" fill="#e0e0e0"/>
    <path d="M52 71 Q64 68 76 71 Q70 75 64 74 Q58 75 52 71Z" fill="${h.beard}"/><path d="M57 77 Q64 82 71 77 Q64 80 57 77Z" fill="${h.beard}"/>
    <path d="M26 128 L22 96 C30 84 42 82 54 88 L48 100Z M102 128 L106 96 C98 84 86 82 74 88 L80 100Z" fill="${h.cape}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>`,
};

export function heroSvg(h, skin) {
  const sk = skin.hex;
  const dark = shade(sk, -26);
  const { lens, brow } = eyes(h, sk);
  const capeLayer = h.cape ? `<path d="M10 128 C12 94 34 80 64 80 C94 80 116 94 118 128Z" fill="url(#cg)" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M26 128 C28 104 44 92 64 92 C84 92 100 104 102 128Z" fill="${shade(h.cape, 18)}" opacity=".5"/>` : '';
  // hair that falls behind the head (drawn under it) for the long-hair heroes
  const backHair = ['circlet', 'storm-hair', 'cowl-star'].includes(h.front) ? '' : '';
  const maskedFront = ['cowl-ears', 'cowl-fins', 'cowl-star', 'cat-cowl', 'wing-helm', 'hood-mask', 'flame-hair'].includes(h.front);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128" shape-rendering="geometricPrecision">
${bg(h)}
<g filter="url(#stk)">
${capeLayer}
<path d="M12 128 C14 102 36 92 64 92 C92 92 114 102 116 128Z" fill="url(#sg)" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>
<path d="M26 106 Q38 97 54 95" stroke="#fff" stroke-width="3" opacity=".28" fill="none" stroke-linecap="round"/>
<path d="M12 128 C14 112 22 104 34 99 C32 108 32 118 34 128Z M116 128 C114 112 106 104 94 99 C96 108 96 118 94 128Z" fill="${h.trim}" opacity=".55"/>
<rect x="52" y="78" width="24" height="20" rx="6" fill="${dark}" stroke="${O}" stroke-width="2.4"/>
<path d="M50 94 C56 100 72 100 78 94 L82 100 C72 108 56 108 46 100Z" fill="${h.trim}" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>
<circle cx="64" cy="113" r="14" fill="${h.emblem[1]}" opacity=".28"/>
${EMBLEM[h.emblem[0]](h.emblem[1])}
<ellipse cx="40.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/><ellipse cx="87.5" cy="60" rx="4.2" ry="6.2" fill="${sk}" stroke="${O}" stroke-width="2.2"/>
<ellipse cx="64" cy="58" rx="24" ry="27.5" fill="${sk}" stroke="${O}" stroke-width="2.6"/>
<ellipse cx="57" cy="46" rx="12" ry="6" fill="#fff" opacity=".22"/>
<ellipse cx="46" cy="68" rx="5.6" ry="3.6" fill="url(#ck)"/><ellipse cx="82" cy="68" rx="5.6" ry="3.6" fill="url(#ck)"/>
<path d="M61 62 L59 69 L64 70" stroke="${dark}" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
${maskedFront ? '' : lens}
${maskedFront ? '' : brow}
${mouth(h, sk)}
${FRONT[h.front](h)}
<path d="M48 36 Q58 30 72 33" stroke="#fff" stroke-width="3" fill="none" opacity=".3" stroke-linecap="round"/>
</g>
</svg>`;
  return svg;
}
