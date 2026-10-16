// Generates the retro pixel-art avatar roster as static SVGs + a manifest.
//   npm run avatars
// Art: DiceBear "Pixel Art" style — CC0 1.0 (public domain), https://www.dicebear.com/styles/pixel-art/
// Output: public/avatars/{look}-{skin}.svg  and  resources/avatars.json  (both committed — production needs no Node).
import fs from 'fs';
import { createAvatar } from '@dicebear/core';
import { pixelArt } from '@dicebear/collection';

// 90s vaporwave backdrops
const BG = { pink: 'ff71ce', cyan: '01cdfe', mint: '05ffa1', purple: 'b967ff', yellow: 'fffb96', coral: 'ff9b85' };

// Skin shades, light → deep. `mouth` is darkened on deeper skin so smiles stay readable.
const SKINS = [
  { id: 1, label: 'Porcelain', hex: 'ffdbac', mouth: 'd98a7b' },
  { id: 2, label: 'Sand',      hex: 'e0b687', mouth: 'c98276' },
  { id: 3, label: 'Honey',     hex: 'cb9e6e', mouth: 'b2705f' },
  { id: 4, label: 'Caramel',   hex: 'a26d3d', mouth: '8f4f43' },
  { id: 5, label: 'Chestnut',  hex: '7b4a28', mouth: '7a3b36' },
  { id: 6, label: 'Espresso',  hex: '523020', mouth: '6e3030' },
  { id: 7, label: 'Ebony',     hex: '33201a', mouth: '6e3030' },
];

const common = { accessoriesProbability: 0, glassesProbability: 0, hatProbability: 0, beardProbability: 0, eyes: ['variant05'], eyesColor: ['4b4b4b'] };

// 8 women + 8 men, each a distinct 90s look.
const LOOKS = [
  { id: 'f1', gender: 'f', label: 'Scrunchie',     bg: 'pink',   o: { hair: ['long05'], hairColor: ['603a14'], clothing: ['variant12'], clothingColor: ['ff6f69'], mouth: ['happy06'] } },
  { id: 'f2', gender: 'f', label: 'Pigtails',      bg: 'purple', o: { hair: ['long19'], hairColor: ['28150a'], accessories: ['variant03'], accessoriesProbability: 100, accessoriesColor: ['ffd700'], clothing: ['variant05'], clothingColor: ['5bc0de'], mouth: ['happy03'] } },
  { id: 'f3', gender: 'f', label: 'Space Buns',    bg: 'cyan',   o: { hair: ['long14'], hairColor: ['009bbd'], clothing: ['variant16'], clothingColor: ['ff71ce'], mouth: ['happy09'] } },
  { id: 'f4', gender: 'f', label: 'Braids',        bg: 'mint',   o: { hair: ['long18'], hairColor: ['28150a'], accessories: ['variant04'], accessoriesProbability: 100, accessoriesColor: ['ffd700'], clothing: ['variant08'], clothingColor: ['ffc425'], mouth: ['happy06'] } },
  { id: 'f5', gender: 'f', label: 'Curly Puffs',   bg: 'yellow', o: { hair: ['long15'], hairColor: ['28150a'], glasses: ['light07'], glassesProbability: 100, glassesColor: ['43677d'], clothing: ['variant03'], clothingColor: ['d11141'], mouth: ['happy02'] } },
  { id: 'f6', gender: 'f', label: 'Auburn Bob',    bg: 'coral',  o: { hair: ['long01'], hairColor: ['611c17'], glasses: ['dark05'], glassesProbability: 100, glassesColor: ['191919'], clothing: ['variant10'], clothingColor: ['03396c'], mouth: ['happy05'] } },
  { id: 'f7', gender: 'f', label: 'Blonde Shades', bg: 'purple', o: { hair: ['long13'], hairColor: ['cab188'], glasses: ['dark04'], glassesProbability: 100, glassesColor: ['191919'], clothing: ['variant14'], clothingColor: ['88d8b0'], mouth: ['happy06'] } },
  { id: 'f8', gender: 'f', label: 'Cap & Swoop',   bg: 'cyan',   o: { hair: ['long08'], hairColor: ['bd1700'], hat: ['variant02'], hatProbability: 100, hatColor: ['2663a3'], clothing: ['variant06'], clothingColor: ['ae0001'], mouth: ['happy04'] } },

  { id: 'm1', gender: 'm', label: 'Flat Top',      bg: 'cyan',   o: { hair: ['short16'], hairColor: ['28150a'], clothing: ['variant02'], clothingColor: ['ffc425'], mouth: ['happy06'] } },
  { id: 'm2', gender: 'm', label: 'Curly Top',     bg: 'pink',   o: { hair: ['short11'], hairColor: ['28150a'], glasses: ['light07'], glassesProbability: 100, glassesColor: ['191919'], clothing: ['variant07'], clothingColor: ['44c585'], mouth: ['happy03'] } },
  { id: 'm3', gender: 'm', label: 'Frosted Tips',  bg: 'purple', o: { hair: ['short13'], hairColor: ['cab188'], clothing: ['variant12'], clothingColor: ['ff6f69'], mouth: ['happy09'] } },
  { id: 'm4', gender: 'm', label: 'Mohawk',        bg: 'mint',   o: { hair: ['short21'], hairColor: ['bd1700'], clothing: ['variant18'], clothingColor: ['03396c'], mouth: ['happy05'] } },
  { id: 'm5', gender: 'm', label: 'Backwards Cap', bg: 'yellow', o: { hair: ['short06'], hairColor: ['603a14'], hat: ['variant06'], hatProbability: 100, hatColor: ['a62116'], clothing: ['variant04'], clothingColor: ['428bca'], mouth: ['happy06'] } },
  { id: 'm6', gender: 'm', label: 'Cool Shades',   bg: 'purple', o: { hair: ['short03'], hairColor: ['28150a'], glasses: ['dark07'], glassesProbability: 100, glassesColor: ['191919'], clothing: ['variant15'], clothingColor: ['ff71ce'], mouth: ['happy02'] } },
  { id: 'm7', gender: 'm', label: 'Goatee',        bg: 'coral',  o: { hair: ['short09'], hairColor: ['603a14'], beard: ['variant04'], beardProbability: 100, clothing: ['variant09'], clothingColor: ['00b159'], mouth: ['happy06'] } },
  { id: 'm8', gender: 'm', label: 'Beanie',        bg: 'pink',   o: { hair: ['short01'], hairColor: ['603a14'], hat: ['variant03'], hatProbability: 100, hatColor: ['614f8a'], clothing: ['variant13'], clothingColor: ['ffd969'], mouth: ['happy08'] } },
];

fs.mkdirSync('public/avatars', { recursive: true });
let n = 0;
for (const look of LOOKS) {
  for (const skin of SKINS) {
    const svg = createAvatar(pixelArt, {
      seed: look.id, ...common, ...look.o,
      skinColor: [skin.hex], mouthColor: [skin.mouth], backgroundColor: [BG[look.bg]], size: 128,
    }).toString();
    fs.writeFileSync(`public/avatars/${look.id}-${skin.id}.svg`, svg);
    n++;
  }
}
fs.writeFileSync('resources/avatars.json', JSON.stringify({
  looks: LOOKS.map(({ id, gender, label }) => ({ id, gender, label })),
  skins: SKINS.map(({ id, label, hex }) => ({ id, label, hex: '#' + hex })),
}, null, 2) + '\n');
console.log(`Built ${n} avatars (${LOOKS.length} looks × ${SKINS.length} skin shades) → public/avatars/, resources/avatars.json`);
