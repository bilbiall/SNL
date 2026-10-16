// "Bar Crew" — twelve characters from a night out (bartender, sommelier, brewmaster, DJ, pirate captain…),
// drawn with the same glossy sticker toolkit as the Rookie set. Every one holds a drinking-game prop and comes in 7 skin shades.
import { genzSvg } from './genz.mjs';

export const BARCREW = [
  { id: 'c1',  gender: 'm', label: 'Bartender',      bg: ['#fcd34d', '#92400e'], pat: 'rays',  hair: 'curly',  hc: '#2a1a14', acc: 'none',    ac: '#fff',    top: '#1f2937', tee: 'vest',  gfx: null, face: 'grin',  iris: '#5b3a1e', fx: [], prop: 'shaker',  beard: { c: '#2a1a14', s: 'stache' } },
  { id: 'c2',  gender: 'f', label: 'Sommelier',      bg: ['#fda4af', '#881337'], pat: 'stars', hair: 'buns',   hc: '#7c2d12', acc: 'round',   ac: '#facc15', top: '#9f1239', tee: 'tux',   gfx: null, face: 'smirk', iris: '#7c2d12', fx: ['hoops'], lip: '#9f1239', prop: 'wine' },
  { id: 'c3',  gender: 'm', label: 'Brewmaster',     bg: ['#fde68a', '#b45309'], pat: 'zig',   hair: 'buzz',   hc: '#b45309', acc: 'flatcap', ac: '#78350f', top: '#d97706', tee: 'apron', gfx: null, face: 'grin',  iris: '#2563eb', fx: [], prop: 'stein',   beard: { c: '#b45309', s: 'full' } },
  { id: 'c4',  gender: 'f', label: 'Mixologist',     bg: ['#c4b5fd', '#4c1d95'], pat: 'dots',  hair: 'bob',    hc: '#a855f7', acc: 'y2k',     ac: '#22d3ee', top: '#7c3aed', tee: 'vest',  gfx: null, face: 'smirk', iris: '#7c3aed', fx: ['gem'], lip: '#d946ef', prop: 'shaker' },
  { id: 'c5',  gender: 'm', label: 'DJ Dropbeat',    bg: ['#67e8f9', '#1e3a8a'], pat: 'rays',  hair: 'afro',   hc: '#15101a', acc: 'phones',  ac: '#22d3ee', top: '#0f172a', tee: 'tee',   gfx: 'bolt', face: 'grin',  iris: '#0ea5e9', fx: ['chain'], prop: 'vinyl' },
  { id: 'c6',  gender: 'f', label: 'Karaoke Queen',  bg: ['#f9a8d4', '#9d174d'], pat: 'stars', hair: 'wavy',   hc: '#ec4899', acc: 'tiara',   ac: '#e5e7eb', top: '#f472b6', tee: 'bomber', gfx: null, face: 'grin',  iris: '#be185d', fx: ['hoops'], lip: '#e11d48', prop: 'mic' },
  { id: 'c7',  gender: 'm', label: 'Pirate Captain', bg: ['#7dd3fc', '#0c4a6e'], pat: 'zig',   hair: 'wavy',   hc: '#1b1410', acc: 'pirate',  ac: '#f8fafc', top: '#7f1d1d', tee: 'vest',  gfx: null, face: 'smirk', iris: '#3f2a14', fx: ['hoops'], prop: 'bottle', beard: { c: '#1b1410', s: 'full' } },
  { id: 'c8',  gender: 'm', label: 'Whiskey Cowboy', bg: ['#fdba74', '#9a3412'], pat: 'dots',  hair: 'fade',   hc: '#78350f', acc: 'cowboy',  ac: '#92400e', top: '#dc2626', tee: 'tee',   gfx: 'star', face: 'smirk', iris: '#3f2a14', fx: ['stud'], prop: 'tumbler', beard: { c: '#78350f', s: 'stache' } },
  { id: 'c9',  gender: 'm', label: 'High Roller',    bg: ['#86efac', '#14532d'], pat: 'stars', hair: 'fade',   hc: '#15101a', acc: 'tophat',  ac: '#18181b', top: '#facc15', tee: 'tux',   gfx: null, face: 'smirk', iris: '#15803d', fx: ['chain'], prop: 'cards',   beard: { c: '#15101a', s: 'goatee' } },
  { id: 'c10', gender: 'f', label: 'Tiki Host',      bg: ['#fde047', '#0e7490'], pat: 'rays',  hair: 'wavy',   hc: '#1b1410', acc: 'none',    ac: '#fff',    top: '#14b8a6', tee: 'hawaii', gfx: null, face: 'grin',  iris: '#3f2a14', fx: ['gem'], lip: '#f43f5e', prop: 'tiki' },
  { id: 'c11', gender: 'm', label: 'Bouncer',        bg: ['#94a3b8', '#0f172a'], pat: 'zig',   hair: 'buzz',   hc: '#15101a', acc: 'y2k',     ac: '#1f2937', top: '#ef4444', tee: 'tux',   gfx: null, face: 'smirk', iris: '#475569', fx: ['stud'], prop: null },
  { id: 'c12', gender: 'f', label: 'Bubbly Socialite', bg: ['#fef3c7', '#a16207'], pat: 'stars', hair: 'bob',  hc: '#fde68a', acc: 'tiara',   ac: '#fbbf24', top: '#fbbf24', tee: 'tee',   gfx: 'heart', face: 'smile', iris: '#a16207', fx: ['hoops', 'chain'], lip: '#dc2626', prop: 'flute', blush: true },
];

export const barSvg = (c, skin) => genzSvg(c, skin);
