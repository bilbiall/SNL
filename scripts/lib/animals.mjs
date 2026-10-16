// "Party Animals" — twelve animal mascots holding drinking-game props, drawn in code.
// Instead of skin shades they come in seven neon colourways (the picker shows them as colour swatches).
import { PATTERN, PROP, ACC, shade, O } from './genz.mjs';

export const COLOURWAYS = [
  { id: 1, label: 'Sunset',     hex: '#f59e0b' },
  { id: 2, label: 'Ruby',       hex: '#ef4444' },
  { id: 3, label: 'Bubblegum',  hex: '#f472b6' },
  { id: 4, label: 'Grape',      hex: '#8b5cf6' },
  { id: 5, label: 'Ocean',      hex: '#3b82f6' },
  { id: 6, label: 'Mint',       hex: '#10b981' },
  { id: 7, label: 'Slate',      hex: '#64748b' },
];

const mix = (hex, amt) => shade(hex, amt);
const eye = (cx, cy, r = 5.2, iris = '#1b1830') => `<ellipse cx="${cx}" cy="${cy}" rx="${r}" ry="${r + 0.6}" fill="#fff" stroke="${O}" stroke-width="1.8"/><circle cx="${cx + 0.8}" cy="${cy + 0.8}" r="${r * 0.62}" fill="${iris}"/><circle cx="${cx + 2}" cy="${cy - 0.8}" r="${r * 0.3}" fill="#fff"/>`;
const nose = (cx, cy, c = O) => `<ellipse cx="${cx}" cy="${cy}" rx="4.6" ry="3.4" fill="${c}" stroke="${O}" stroke-width="1.2"/><ellipse cx="${cx - 1.4}" cy="${cy - 1.2}" rx="1.4" ry=".9" fill="#fff" opacity=".7"/>`;
const smile = (cx, cy, w = 9) => `<path d="M${cx} ${cy} V${cy + 3} M${cx - w} ${cy + 3} Q${cx - w / 2} ${cy + 9} ${cx} ${cy + 3} Q${cx + w / 2} ${cy + 9} ${cx + w} ${cy + 3}" fill="none" stroke="${O}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>`;
const blush = (y = 70) => `<ellipse cx="42" cy="${y}" rx="5.4" ry="3.4" fill="#ff5c8a" opacity=".42"/><ellipse cx="86" cy="${y}" rx="5.4" ry="3.4" fill="#ff5c8a" opacity=".42"/>`;
const whiskers = (y = 72) => `<g stroke="${O}" stroke-width="1.6" stroke-linecap="round" opacity=".7"><path d="M44 ${y} L28 ${y - 3} M44 ${y + 4} L28 ${y + 5}"/><path d="M84 ${y} L100 ${y - 3} M84 ${y + 4} L100 ${y + 5}"/></g>`;
const headPath = (fill, d) => `<path d="${d}" fill="${fill}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`;
const ROUND = 'M64 32 C88 32 98 48 98 64 C98 82 84 94 64 94 C44 94 30 82 30 64 C30 48 40 32 64 32Z';
const WIDE = 'M64 38 C88 36 102 50 100 66 C98 84 82 94 64 94 C46 94 30 84 28 66 C26 50 40 36 64 38Z';

// each animal: (fur, light) → { back, head, face, front }
const ANIMALS = {
  fox: (fur, light) => ({
    back: `<path d="M32 50 L26 12 L58 36Z M96 50 L102 12 L70 36Z" fill="${fur}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/><path d="M35 44 L31 22 L50 37Z M93 44 L97 22 L78 37Z" fill="#fff" opacity=".85"/><path d="M26 12 L30 24 L36 20Z M102 12 L98 24 L92 20Z" fill="${mix(fur, -90)}"/>`,
    head: headPath(fur, WIDE),
    face: `<path d="M64 94 C48 94 32 86 28 70 C42 68 54 74 64 82 C74 74 86 68 100 70 C96 86 80 94 64 94Z" fill="${light}" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/>${blush(68)}${eye(51, 58)}${eye(77, 58)}${nose(64, 77)}${smile(64, 82, 8)}`,
  }),
  bear: (fur, light) => ({
    back: `<circle cx="38" cy="38" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="90" cy="38" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="38" cy="38" r="6.5" fill="${light}"/><circle cx="90" cy="38" r="6.5" fill="${light}"/>`,
    head: headPath(fur, ROUND),
    face: `<ellipse cx="64" cy="76" rx="16" ry="12.5" fill="${light}" stroke="${O}" stroke-width="2"/>${blush(70)}${eye(50, 60, 4.8)}${eye(78, 60, 4.8)}${nose(64, 72)}${smile(64, 76, 7)}`,
  }),
  cat: (fur, light) => ({
    back: `<path d="M34 52 L30 16 L58 36Z M94 52 L98 16 L70 36Z" fill="${fur}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/><path d="M36 44 L34 24 L50 36Z M92 44 L94 24 L78 36Z" fill="#fda4af"/>`,
    head: headPath(fur, ROUND),
    face: `<path d="M52 36 V46 M64 34 V46 M76 36 V46" stroke="${mix(fur, -60)}" stroke-width="3" stroke-linecap="round" opacity=".7"/><ellipse cx="64" cy="78" rx="13" ry="9" fill="${light}"/>${blush(70)}<ellipse cx="51" cy="60" rx="5" ry="5.8" fill="#fde047" stroke="${O}" stroke-width="1.8"/><ellipse cx="77" cy="60" rx="5" ry="5.8" fill="#fde047" stroke="${O}" stroke-width="1.8"/><ellipse cx="51" cy="60" rx="1.8" ry="4.4" fill="${O}"/><ellipse cx="77" cy="60" rx="1.8" ry="4.4" fill="${O}"/><circle cx="52.4" cy="57.6" r="1.3" fill="#fff"/><circle cx="78.4" cy="57.6" r="1.3" fill="#fff"/><path d="M60 72 L68 72 L64 77Z" fill="#fb7185" stroke="${O}" stroke-width="1.4" stroke-linejoin="round"/>${smile(64, 77, 7)}${whiskers(74)}`,
  }),
  frog: (fur, light) => ({
    back: '',
    head: headPath(fur, 'M64 40 C90 38 104 54 102 70 C100 86 84 94 64 94 C44 94 28 86 26 70 C24 54 38 38 64 40Z'),
    face: `<circle cx="42" cy="40" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="86" cy="40" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/>${eye(42, 40, 8)}${eye(86, 40, 8)}<path d="M64 94 C46 94 30 88 28 74 C40 84 88 84 100 74 C98 88 82 94 64 94Z" fill="${light}"/><path d="M36 74 Q64 92 92 74" fill="none" stroke="${O}" stroke-width="2.6" stroke-linecap="round"/><circle cx="58" cy="62" r="1.6" fill="${O}"/><circle cx="70" cy="62" r="1.6" fill="${O}"/>${blush(76)}`,
  }),
  panda: (fur, light) => ({
    back: `<circle cx="38" cy="38" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="90" cy="38" r="13" fill="${fur}" stroke="${O}" stroke-width="2.6"/>`,
    head: headPath('#fafafa', ROUND),
    face: `<ellipse cx="48" cy="60" rx="9.5" ry="12" fill="${fur}" transform="rotate(20 48 60)"/><ellipse cx="80" cy="60" rx="9.5" ry="12" fill="${fur}" transform="rotate(-20 80 60)"/>${eye(48, 60, 3.8)}${eye(80, 60, 3.8)}${blush(72)}${nose(64, 74)}${smile(64, 77, 7)}`,
  }),
  owl: (fur, light) => ({
    back: `<path d="M34 46 L32 14 L56 34Z M94 46 L96 14 L72 34Z" fill="${fur}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`,
    head: headPath(fur, ROUND),
    face: `<circle cx="48" cy="58" r="14" fill="${light}" stroke="${O}" stroke-width="2.2"/><circle cx="80" cy="58" r="14" fill="${light}" stroke="${O}" stroke-width="2.2"/>${eye(48, 58, 8, '#f59e0b')}${eye(80, 58, 8, '#f59e0b')}<path d="M64 66 L57 76 L64 84 L71 76Z" fill="#fb923c" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><path d="M40 34 Q48 40 56 36 M88 34 Q80 40 72 36" stroke="${mix(fur, -70)}" stroke-width="3" fill="none" stroke-linecap="round" opacity=".7"/>`,
  }),
  raccoon: (fur, light) => ({
    back: `<circle cx="36" cy="40" r="12" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="92" cy="40" r="12" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="36" cy="40" r="5.5" fill="#1b1830"/><circle cx="92" cy="40" r="5.5" fill="#1b1830"/>`,
    head: headPath(fur, WIDE),
    face: `<path d="M30 56 C40 48 52 50 62 58 L66 58 C76 50 88 48 98 56 C98 70 84 74 72 68 L56 68 C44 74 30 70 30 56Z" fill="#1b1830"/><path d="M64 94 C48 94 36 88 34 78 C46 80 54 76 64 70 C74 76 82 80 94 78 C92 88 80 94 64 94Z" fill="${light}" stroke="${O}" stroke-width="2"/>${eye(48, 60, 4.6)}${eye(80, 60, 4.6)}${nose(64, 76)}${smile(64, 80, 7)}<path d="M64 36 V50" stroke="${light}" stroke-width="5" stroke-linecap="round"/>`,
  }),
  bunny: (fur, light) => ({
    back: `<path d="M44 44 C34 20 38 4 46 4 C56 4 56 28 56 40Z M84 44 C94 20 90 4 82 4 C72 4 72 28 72 40Z" fill="${fur}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/><path d="M46 38 C42 24 43 12 46 12 C50 12 51 26 51 36Z M82 38 C86 24 85 12 82 12 C78 12 77 26 77 36Z" fill="#fda4af"/>`,
    head: headPath(fur, ROUND),
    face: `<ellipse cx="64" cy="78" rx="15" ry="11" fill="${light}"/>${blush(72)}${eye(50, 60, 5)}${eye(78, 60, 5)}<path d="M60 71 L68 71 L64 76Z" fill="#fb7185" stroke="${O}" stroke-width="1.4" stroke-linejoin="round"/><path d="M64 76 V80 M56 80 Q60 86 64 80 Q68 86 72 80" fill="none" stroke="${O}" stroke-width="2" stroke-linecap="round"/><rect x="61" y="82" width="6" height="6.5" rx="1.6" fill="#fff" stroke="${O}" stroke-width="1.4"/>`,
  }),
  dog: (fur, light) => ({
    back: `<path d="M34 40 C20 38 16 62 22 78 C32 72 38 56 38 44Z M94 40 C108 38 112 62 106 78 C96 72 90 56 90 44Z" fill="${mix(fur, -50)}" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/>`,
    head: headPath(fur, ROUND),
    face: `<ellipse cx="64" cy="76" rx="18" ry="14" fill="${light}" stroke="${O}" stroke-width="2"/><ellipse cx="46" cy="56" rx="8" ry="10" fill="${mix(fur, -50)}" opacity=".8"/>${blush(70)}${eye(50, 60, 4.8)}${eye(78, 60, 4.8)}${nose(64, 70)}<path d="M64 74 V78 M56 78 Q60 84 64 78 Q68 84 72 78" fill="none" stroke="${O}" stroke-width="2.2" stroke-linecap="round"/><path d="M60 82 Q64 94 68 82Z" fill="#fb7185" stroke="${O}" stroke-width="1.6" stroke-linejoin="round"/>`,
  }),
  penguin: (fur, light) => ({
    back: '',
    head: headPath(mix(fur, -70), ROUND),
    face: `<path d="M64 46 C80 38 94 52 90 70 C88 84 76 92 64 92 C52 92 40 84 38 70 C34 52 48 38 64 46Z" fill="#fafafa" stroke="${O}" stroke-width="2"/>${blush(72)}${eye(52, 60, 4.8)}${eye(76, 60, 4.8)}<path d="M56 70 L72 70 L64 80Z" fill="#fb923c" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><path d="M58 73 H70" stroke="#fff" stroke-width="1.4" opacity=".5"/>`,
  }),
  monkey: (fur, light) => ({
    back: `<circle cx="32" cy="62" r="12" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="96" cy="62" r="12" fill="${fur}" stroke="${O}" stroke-width="2.6"/><circle cx="32" cy="62" r="6" fill="${light}"/><circle cx="96" cy="62" r="6" fill="${light}"/>`,
    head: headPath(fur, ROUND),
    face: `<path d="M64 52 C74 44 90 52 88 66 C88 80 78 92 64 92 C50 92 40 80 40 66 C38 52 54 44 64 52Z" fill="${light}" stroke="${O}" stroke-width="2"/>${eye(52, 62, 4.8)}${eye(76, 62, 4.8)}<ellipse cx="60" cy="74" rx="1.6" ry="2" fill="${O}"/><ellipse cx="68" cy="74" rx="1.6" ry="2" fill="${O}"/>${smile(64, 80, 8)}`,
  }),
  parrot: (fur, light) => ({
    back: `<path d="M52 34 C48 18 56 8 64 10 C60 16 62 24 64 30 C66 22 72 14 80 14 C76 20 76 28 74 36Z" fill="#fde047" stroke="${O}" stroke-width="2.2" stroke-linejoin="round"/><path d="M60 30 C60 20 66 12 72 8 C70 16 72 24 70 32Z" fill="#ef4444" stroke="${O}" stroke-width="2" stroke-linejoin="round"/>`,
    head: headPath(fur, ROUND),
    face: `<circle cx="48" cy="58" r="13" fill="#fff" stroke="${O}" stroke-width="2"/><circle cx="80" cy="58" r="13" fill="#fff" stroke="${O}" stroke-width="2"/>${eye(48, 58, 6, '#1b1830')}${eye(80, 58, 6, '#1b1830')}<path d="M54 68 C54 62 74 62 74 68 C76 80 70 90 64 92 C58 90 52 80 54 68Z" fill="#fde047" stroke="${O}" stroke-width="2.4" stroke-linejoin="round"/><path d="M58 74 H70" stroke="${O}" stroke-width="1.6" opacity=".5"/><circle cx="60" cy="68" r="1.2" fill="${O}"/><circle cx="68" cy="68" r="1.2" fill="${O}"/>`,
  }),
};

export const ANIMAL_ROSTER = [
  { id: 'a1',  gender: 'f', label: 'Foxy Mixologist',  kind: 'fox',     bg: ['#fde68a', '#b45309'], pat: 'rays',  acc: 'y2k',     ac: '#22d3ee', prop: 'martini', tie: 'bow' },
  { id: 'a2',  gender: 'm', label: 'Beer Bear',        kind: 'bear',    bg: ['#fcd34d', '#7c2d12'], pat: 'dots',  acc: 'partyhat', ac: '#ec4899', prop: 'stein',   tie: 'bow' },
  { id: 'a3',  gender: 'f', label: 'Sommelier Cat',    kind: 'cat',     bg: ['#fda4af', '#881337'], pat: 'stars', acc: 'round',   ac: '#facc15', prop: 'wine',    tie: 'bow' },
  { id: 'a4',  gender: 'f', label: 'Bubbly Frog',      kind: 'frog',    bg: ['#bef264', '#166534'], pat: 'dots',  acc: 'tiara',   ac: '#fde047', prop: 'flute',   tie: 'none' },
  { id: 'a5',  gender: 'm', label: 'Chill Panda',      kind: 'panda',   bg: ['#a5f3fc', '#0e7490'], pat: 'zig',   acc: 'phones',  ac: '#f472b6', prop: 'shot',    tie: 'none' },
  { id: 'a6',  gender: 'm', label: 'Night Owl',        kind: 'owl',     bg: ['#c4b5fd', '#312e81'], pat: 'stars', acc: 'round',   ac: '#f59e0b', prop: 'cards',   tie: 'bow' },
  { id: 'a7',  gender: 'm', label: 'Bandit Raccoon',   kind: 'raccoon', bg: ['#94a3b8', '#1e293b'], pat: 'zig',   acc: 'none',    ac: '#fff',    prop: 'bottle',  tie: 'none' },
  { id: 'a8',  gender: 'f', label: 'Lucky Bunny',      kind: 'bunny',   bg: ['#fbcfe8', '#9d174d'], pat: 'stars', acc: 'none',    ac: '#fff',    prop: 'flute',   tie: 'bow' },
  { id: 'a9',  gender: 'm', label: 'Cowboy Pup',       kind: 'dog',     bg: ['#fdba74', '#9a3412'], pat: 'dots',  acc: 'cowboy',  ac: '#92400e', prop: 'tumbler', tie: 'none' },
  { id: 'a10', gender: 'm', label: 'Tuxedo Penguin',   kind: 'penguin', bg: ['#93c5fd', '#1e3a8a'], pat: 'rays',  acc: 'tophat',  ac: '#18181b', prop: 'martini', tie: 'bow' },
  { id: 'a11', gender: 'f', label: 'Tiki Monkey',      kind: 'monkey',  bg: ['#fde047', '#0e7490'], pat: 'rays',  acc: 'none',    ac: '#fff',    prop: 'tiki',    tie: 'hawaii' },
  { id: 'a12', gender: 'm', label: 'Rum Parrot',       kind: 'parrot',  bg: ['#7dd3fc', '#0c4a6e'], pat: 'zig',   acc: 'pirate',  ac: '#f8fafc', prop: 'bottle',  tie: 'none' },
];

const body = (fur, light, tie) => `<path d="M12 128 C14 100 36 90 64 90 C92 90 114 100 116 128Z" fill="url(#bd)" stroke="${O}" stroke-width="2.6" stroke-linejoin="round"/><ellipse cx="64" cy="122" rx="24" ry="20" fill="${light}" opacity=".9"/>`
  + (tie === 'bow' ? `<path d="M52 96 L64 100 L76 96 L76 108 L64 104 L52 108Z" fill="#dc2626" stroke="${O}" stroke-width="2" stroke-linejoin="round"/><circle cx="64" cy="102" r="3" fill="#b91c1c" stroke="${O}" stroke-width="1.4"/>` : '')
  + (tie === 'hawaii' ? `<g fill="#fde047" stroke="${O}" stroke-width="1">${[[28, 112], [46, 122], [84, 114], [100, 122], [64, 126]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="4.4"/>`).join('')}</g><g fill="#fb7185">${[[28, 112], [46, 122], [84, 114], [100, 122], [64, 126]].map(([x, y]) => `<circle cx="${x}" cy="${y}" r="1.6"/>`).join('')}</g>` : '');

export function animalSvg(a, colourway) {
  const fur = colourway.hex;
  const light = mix(fur, 95);
  const parts = ANIMALS[a.kind](fur, light);
  const hat = ACC[a.acc] ? ACC[a.acc](a.ac) : '';
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128" width="128" height="128" shape-rendering="geometricPrecision">
<defs>
  <radialGradient id="bgg" cx="50%" cy="38%" r="80%"><stop offset="0" stop-color="${a.bg[0]}"/><stop offset="1" stop-color="${a.bg[1]}"/></radialGradient>
  <linearGradient id="bd" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${mix(fur, 30)}"/><stop offset="1" stop-color="${mix(fur, -36)}"/></linearGradient>
  <filter id="stk" x="-15%" y="-15%" width="130%" height="130%"><feMorphology in="SourceAlpha" operator="dilate" radius="3.2" result="d"/><feFlood flood-color="#ffffff"/><feComposite in2="d" operator="in" result="o"/><feDropShadow in="o" dx="0" dy="2" stdDeviation="2" flood-color="#000" flood-opacity=".35" result="s"/><feMerge><feMergeNode in="s"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
</defs>
<rect width="128" height="128" fill="url(#bgg)"/>
${PATTERN[a.pat](mix(a.bg[0], 40))}
<circle cx="106" cy="104" r="22" fill="${mix(a.bg[1], 26)}" opacity=".45"/>
<g filter="url(#stk)">
${parts.back}
${body(fur, light, a.tie)}
${parts.head}
${parts.face}
<path d="M46 40 Q58 33 74 36" stroke="#fff" stroke-width="3.2" fill="none" opacity=".35" stroke-linecap="round"/>
${hat}
${PROP[a.prop] ? PROP[a.prop]() : ''}
</g>
</svg>`;
}
