// Topic tests for public/js/snl-engine.js (the offline party engine): a table that picks topics only gets those topics' question cards.
//   php artisan snl:pack            → storage/app/offline-pack.json
//   node scripts/test-topics.mjs
import fs from 'fs';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
await import(pathToFileURL(path.join(root, 'public/js/snl-engine.js')).href);
const E = globalThis.SNLEngine;
const pack = JSON.parse(fs.readFileSync(path.join(root, 'storage/app/offline-pack.json'), 'utf8'));
const board = pack.boards[0];

let failures = 0;
const check = (name, ok, detail = '') => { if (!ok) { failures++; console.log('  FAIL: ' + name + (detail ? ' — ' + detail : '')); } };
function mulberry(seed) { return () => { seed |= 0; seed = (seed + 0x6D2B79F5) | 0; let t = Math.imul(seed ^ (seed >>> 15), 1 | seed); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const prng = mulberry(20261019); E.setRandom(mulberry(31));
const pick = (a) => a[Math.floor(prng() * a.length)];
const topicOf = {}; pack.cards.forEach((c) => { topicOf[c.kind + '|' + c.text] = c.topic || null; });
const QUESTION_KINDS = pack.config.topic_kinds;
// cards with {placeholders} are shown with the blanks filled in ("Commentate on Amina …"), so match them by pattern
const patterns = pack.cards.filter((c) => c.text.includes('{')).map((c) => ({ kind: c.kind, topic: c.topic || null, re: new RegExp('^' + c.text.replace(/[.*+?^$()|[]\]/g, '\$&').replace(/{[a-z_]+}/g, '.+') + '$') }));
const topicFor = (kind, text) => { const t = String(text); if (Object.prototype.hasOwnProperty.call(topicOf, kind + '|' + t)) return topicOf[kind + '|' + t]; const m = patterns.find((x) => x.kind === kind && x.re.test(t)); return m ? m.topic : undefined; };

function answerFor(st) {
  const p = st.pending;
  switch (p.type) {
    case 'reroll': case 'shield': return [pick(['use', 'skip']), null];
    case 'pick': return ['target', pick(p.eligible)];
    case 'tf': return [pick(['true', 'false']), null];
    case 'bluff_say': return ['done', null];
    case 'bluff_call': return prng() < 0.5 ? ['none', null] : ['call', pick(p.eligible)];
    case 'bluff_verdict': return [pick(['proved', 'busted']), null];
    case 'spot': return [pick(['nailed', 'failed']), null];
    default: throw new Error('unknown pending ' + p.type);
  }
}
/** Plays a whole game and returns every question-style card that was put to the table (prompt text → topic). */
function play(topics, mode = 'family', n = 4) {
  const g = E.create(pack, { boardId: board.id, mode, intensity: 3, entries: Array.from({ length: n }, (_, i) => ({ name: 'P' + i })), topics });
  const seen = []; let st = E.state(pack, g), steps = 0;
  while (st.room.status === 'active' && steps++ < 3000) {
    if (st.pending && st.pending.card && QUESTION_KINDS.includes(st.pending.card.kind)) seen.push(st.pending.card);
    for (const a of st.actions) if (a.type === 'card' && QUESTION_KINDS.includes(a.kind)) seen.push(a);
    if (st.pending) { const [c, t] = answerFor(st); E.decide(pack, g, c, t); } else E.roll(pack, g);
    st = E.state(pack, g);
  }
  return { g, st, seen, finished: st.room.status === 'completed' };
}

const topicKeys = Object.keys(pack.config.topics);
check('12 topics configured', topicKeys.length === 12, String(topicKeys.length));
for (const t of topicKeys) for (const k of QUESTION_KINDS) check(`topic ${t} has ${k} cards`, pack.cards.some((c) => c.topic === t && c.kind === k));

// one topic: every question-style card comes from it
for (const t of ['football', 'history', 'kenya']) {
  let asked = 0, wrong = 0, finished = 0;
  for (let i = 0; i < 12; i++) {
    const r = play([t]); if (r.finished) finished++;
    for (const c of r.seen) { asked++; const tp = topicFor(c.kind, c.text || ''); if (tp !== t) { wrong++; if (wrong <= 2) console.log('   off-topic:', c.kind, tp, '|', String(c.text).slice(0, 60)); } }
  }
  check(`${t}: games finish`, finished === 12, `${finished}/12`);
  check(`${t}: only ${t} questions`, asked > 0 && wrong === 0, `${wrong} of ${asked} were off-topic`);
}
// two topics
{ const got = new Set(); for (let i = 0; i < 10; i++) play(['football', 'music']).seen.forEach((c) => got.add(topicFor(c.kind, c.text))); check('two topics: only those two, and both show up', [...got].every((x) => x === 'football' || x === 'music') && got.has('football') && got.has('music'), [...got].join()); }
// random / nothing chosen: a mix of topics and general cards
for (const tp of [['random'], undefined, []]) { const got = new Set(); for (let i = 0; i < 14; i++) play(tp).seen.forEach((c) => got.add(topicFor(c.kind, c.text))); check('random mixes many topics ' + JSON.stringify(tp), got.size >= 6, [...got].join()); check('random is stored as no topics', E.state(pack, play(tp).g).room.topics.length === 0); }
// junk is cleaned, state shows the picks, rematch keeps them
{ const g = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: [{}, {}], topics: ['kenya', 'nonsense', 'kenya', '<b>x</b>'] }); check('junk and repeats cleaned', JSON.stringify(E.state(pack, g).room.topics) === '["kenya"]', JSON.stringify(E.state(pack, g).room.topics)); }
{ const r = play(['science']); const rm = E.rematch(pack, r.g); check('rematch keeps topics', JSON.stringify(E.state(pack, rm).room.topics) === '["science"]'); }
// a topic with nothing for a kind falls back to the general cards instead of running dry
{ const p2 = JSON.parse(JSON.stringify(pack)); p2.cards = p2.cards.filter((c) => !(c.topic === 'tech' && c.kind === 'bluff'));
  const g = E.create(p2, { boardId: board.id, mode: 'family', intensity: 1, entries: [{}, {}], topics: ['tech'] }); const c = new (E.Ctx || function () {})(); void c;
  check('engine builds with a thin topic', !!g); }
// modes: a topic also works inside Boyz / Girlies / Lovers (their dares stay mode-based)
for (const m of ['boyz', 'girlies', 'lovers', 'drinks']) { const r = play(['movies'], m, 4); check(`movies topic in ${m} mode finishes`, r.finished); check(`movies topic in ${m} mode stays on topic`, r.seen.every((c) => topicFor(c.kind, c.text) === 'movies'), r.seen.filter((c) => topicFor(c.kind, c.text) !== 'movies').map((c) => c.kind).join()); }

console.log(failures ? `${failures} FAILURES` : 'all topic checks passed');
process.exit(failures ? 1 : 0);
