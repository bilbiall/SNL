// Tests for public/js/snl-engine.js (the offline party engine).
//   php artisan snl:pack                                  → storage/app/offline-pack.json
//   php storage/scratch/php-state-sample.php              → storage/scratch/php-state-sample.json (PHP reference states)
//   node scripts/test-engine.mjs
import fs from 'fs';
import path from 'path';
import { createRequire } from 'module';
import { fileURLToPath, pathToFileURL } from 'url';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
await import(pathToFileURL(path.join(root, 'public/js/snl-engine.js')).href);
const E = globalThis.SNLEngine;
const pack = JSON.parse(fs.readFileSync(path.join(root, 'storage/app/offline-pack.json'), 'utf8'));
const samplePath = path.join(root, 'storage/scratch/php-state-sample.json');
const sample = fs.existsSync(samplePath) ? JSON.parse(fs.readFileSync(samplePath, 'utf8')) : null;

let failures = 0;
const results = [];
function check(name, ok, detail = '') {
  results.push({ name, ok, detail });
  if (!ok) { failures++; console.log('  FAIL: ' + name + (detail ? ' — ' + detail : '')); }
}

// ── seeded RNGs: one for the engine, one for the "players" ──
function mulberry(seed) { return () => { seed |= 0; seed = (seed + 0x6D2B79F5) | 0; let t = Math.imul(seed ^ (seed >>> 15), 1 | seed); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; }; }
const prng = mulberry(20261017);
const pick = (a) => a[Math.floor(prng() * a.length)];
const int = (a, b) => a + Math.floor(prng() * (b - a + 1));
E.setRandom(mulberry(777));

const deepEq = (a, b) => JSON.stringify(a) === JSON.stringify(b);
const board = pack.boards[0];
const N = board.tile_count;

// ── structural "shape" of a value (keys only; leaves are 'v'; empty arrays are wildcards) ──
function shape(v) {
  if (Array.isArray(v)) return v.length ? [shape(v[0])] : [];
  if (v && typeof v === 'object') { const o = {}; Object.keys(v).sort().forEach((k) => { o[k] = shape(v[k]); }); return o; }
  return 'v';
}
function shapeDiff(a, b, p = '') {
  const isObj = (x) => x && typeof x === 'object' && !Array.isArray(x);
  if (Array.isArray(a) && Array.isArray(b)) { if (!a.length || !b.length) return null; return shapeDiff(a[0], b[0], p + '[]'); }
  if (isObj(a) && isObj(b)) {
    const ka = Object.keys(a), kb = Object.keys(b);
    for (const k of ka) if (!(k in b)) return `${p}.${k} only in PHP/JS-first`;
    for (const k of kb) if (!(k in a)) return `${p}.${k} only in other`;
    for (const k of ka) { const d = shapeDiff(a[k], b[k], p + '.' + k); if (d) return d; }
    return null;
  }
  if (isObj(a) !== isObj(b) && a !== 'v' && b !== 'v') return `${p} type differs`;
  return null;
}
function hasKey(v, keys) { // true when any object below v carries one of `keys`
  if (Array.isArray(v)) return v.some((x) => hasKey(x, keys));
  if (v && typeof v === 'object') return Object.keys(v).some((k) => keys.includes(k) || hasKey(v[k], keys));
  return false;
}

const KEYSETS = sample ? sample.actionKeys : {};
const unknownTypes = new Set();
const seenShapes = {}; // pending key -> first JS state shape

function newSetup() {
  const mode = pick(['drinks', 'family']);
  const count = int(2, 12);
  const names = ['Sam', 'Sam', 'Ana', 'Jo', 'Léa', 'Max', 'Zed', 'Kit', 'Bo', 'Ivy', 'Cy', 'Di'];
  const entries = [];
  for (let i = 0; i < count; i++) entries.push({ name: i < 2 && prng() < 0.3 ? 'Sam' : (names[i] + (prng() < 0.2 ? '  ' + i : '')), color: prng() < 0.5 ? undefined : '#ff0000', avatar: prng() < 0.3 ? 'zz' : (prng() < 0.5 ? 'f2-3' : undefined) });
  return { boardId: board.id, mode, intensity: int(1, 3), entries };
}

function answerFor(state) {
  const p = state.pending, t = p.type;
  switch (t) {
    case 'pick': return ['target', pick(p.eligible)];
    case 'shield': return [prng() < 0.5 ? 'use' : 'skip', null];
    case 'tf': return [prng() < 0.5 ? 'true' : 'false', null];
    case 'bluff_say': return ['done', null];
    case 'bluff_call': return prng() < 0.6 ? ['call', pick(p.eligible)] : ['none', null];
    case 'bluff_verdict': return [prng() < 0.5 ? 'proved' : 'busted', null];
    case 'spot': return [prng() < 0.5 ? 'nailed' : 'failed', null];
    default: return ['skip', null];
  }
}

const wrongChoice = { pick: 'true', shield: 'target', tf: 'use', bluff_say: 'call', bluff_call: 'proved', bluff_verdict: 'void', spot: 'true' };

// ═════════════ 1. random games ═════════════
let games = 0, completed = 0, steps = 0, maxSteps = 0, leaves = 0, illegalOk = 0, rounds = 0;
const pendingTypes = {}, actionTypes = {};
let leaks = 0, bad = 0;
const GAMES = 400;

for (let gi = 0; gi < GAMES; gi++) {
  const setup = newSetup();
  let g;
  try { g = E.create(pack, setup); } catch (e) { check('create game #' + gi, false, e.message); continue; }
  games++;
  let lastSeq = g.room.event_seq, s = 0;
  for (; s < 6000; s++) {
    let st = E.state(pack, g);
    if (st.room.status === 'completed') break;
    // invariants
    if (!deepEq(JSON.parse(JSON.stringify(g)), g)) { check('JSON round trip', false, 'game #' + gi); bad++; break; }
    if (st.actions.length > 24) { check('log <= 24', false); bad++; break; }
    for (let i = 1; i < st.actions.length; i++) if (st.actions[i].seq <= st.actions[i - 1].seq) { check('seq strictly increasing', false); bad++; }
    if (st.room.event_seq < lastSeq) { check('event_seq monotone', false); bad++; }
    lastSeq = st.room.event_seq;
    for (const p of st.players) if (p.position < 0 || p.position > N) { check('position in range', false, `${p.position}`); bad++; }
    if (st.pending && hasKey(st.pending, ['answer', 'explain'])) { leaks++; }
    for (const a of st.actions) {
      actionTypes[a.type] = (actionTypes[a.type] || 0) + 1;
      if (a.type === 'card' && hasKey(a, ['answer', 'explain'])) leaks++;
      if (sample && KEYSETS[a.type]) {
        const ks = Object.keys(a).filter((k) => !(a.type === 'win' && k === 'by_default')).sort();
        if (!KEYSETS[a.type].some((k) => deepEq(k, ks))) { check('action keys match PHP: ' + a.type, false, ks.join(',')); bad++; }
      } else if (sample) unknownTypes.add(a.type);
    }
    if (sample) {
      const k = st.pending ? (st.pending.type === 'pick' ? 'pick_' + st.pending.sub : st.pending.type) : 'none';
      if (st.pending && !seenShapes[k]) seenShapes[k] = shape(st);
    }
    if (st.pending) pendingTypes[st.pending.type] = (pendingTypes[st.pending.type] || 0) + 1;

    // deliberately illegal calls must throw GameError and leave the game untouched
    if (prng() < 0.04) {
      const before = JSON.stringify(g);
      try {
        if (st.pending) E.decide(pack, g, wrongChoice[st.pending.type] || 'true', null); else E.decide(pack, g, 'use', null);
        check('illegal decide throws', false);
      } catch (e) { if (e instanceof E.GameError && JSON.stringify(g) === before) illegalOk++; else { check('illegal call is a clean GameError', false, String(e)); bad++; } }
      if (st.pending) { try { E.roll(pack, g); check('roll during prompt throws', false); } catch (e) { if (!(e instanceof E.GameError) || JSON.stringify(g) !== before) { check('roll-during-prompt clean error', false); bad++; } } }
    }
    // occasionally somebody leaves
    if (prng() < 0.004 && st.players.filter((p) => p.status === 'active').length > 1) {
      E.leave(pack, g, pick(st.players.filter((p) => p.status === 'active')).id); leaves++; continue;
    }
    try {
      if (st.pending) { const [c, t] = answerFor(st); E.decide(pack, g, c, t); } else E.roll(pack, g);
    } catch (e) { check('legal move threw', false, e.message + ' (pending ' + (st.pending && st.pending.type) + ')'); bad++; break; }
  }
  steps += s; maxSteps = Math.max(maxSteps, s);
  const fin = E.state(pack, g);
  for (const a of fin.actions) {
    actionTypes[a.type] = (actionTypes[a.type] || 0) + 1;
    if (sample && KEYSETS[a.type]) { const ks = Object.keys(a).filter((k) => !(a.type === 'win' && k === 'by_default')).sort(); if (!KEYSETS[a.type].some((k) => deepEq(k, ks))) { check('action keys match PHP: ' + a.type, false, ks.join(',')); bad++; } }
  }
  if (fin.room.status === 'completed' && fin.room.winner_player_id != null && fin.recap) { completed++; rounds += fin.recap.rounds; }
  else { check('game #' + gi + ' finished', false, `status ${fin.room.status} after ${s} steps`); }
}
leaks += 0;
check('no answer/explain leaked in pending views or card logs', leaks === 0, leaks + ' leaks');
check(`all ${GAMES} games finish with a winner + recap`, completed === games, `${completed}/${games}`);
check('no invariant breaches', bad === 0, bad + ' breaches');

// ═════════════ 2. shape parity with PHP ═════════════
if (!sample) console.log('  (php-state-sample.json missing — skipping PHP parity)');
else {
  const g0 = E.create(pack, { boardId: board.id, mode: 'drinks', intensity: 2, entries: [{ name: 'A' }, { name: 'B' }, { name: 'C' }] });
  const stripped = (st) => { const c = Object.assign({}, st); delete c.actions; return c; };
  let d = shapeDiff(stripped(sample.fresh), stripped(E.state(pack, g0)));
  check('fresh state shape == PHP', !d, d || '');
  const fa = sample.fresh.actions[0], ja = E.state(pack, g0).actions[0];
  check('start action keys == PHP', deepEq(Object.keys(fa).sort(), Object.keys(ja).sort()));
  const types = Object.keys(sample.pending);
  for (const t of types) {
    if (!seenShapes[t]) { check('JS reached pending ' + t, false); continue; }
    const dd = shapeDiff(stripped(sample.pending[t]), (() => { const o = JSON.parse(JSON.stringify(seenShapes[t])); return o; })());
    // seenShapes holds shapes (leaves 'v'); compare shapes against PHP shape by converting PHP state too
    const phpShape = shape(stripped(sample.pending[t])); const jsShape = JSON.parse(JSON.stringify(seenShapes[t])); delete jsShape.actions;
    if (t === 'shield') { for (const sh of [phpShape, jsShape]) if (sh.pending && sh.pending.card) { delete sh.pending.card.intensity; delete sh.pending.card.units; delete sh.pending.card.random; } }
    const dd2 = shapeDiff(phpShape, jsShape);
    check('pending ' + t + ' state shape == PHP', !dd2, dd2 || '');
  }
  if (sample.completed) {
    // finish a game quickly
    let g = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: [{ name: 'A' }, { name: 'B' }] });
    for (let i = 0; i < 4000 && E.state(pack, g).room.status !== 'completed'; i++) { const st = E.state(pack, g); if (st.pending) { const [c, t] = answerFor(st); E.decide(pack, g, c, t); } else E.roll(pack, g); }
    const cs = E.state(pack, g);
    const dc = shapeDiff(shape(stripped(sample.completed)), shape(stripped(cs)));
    check('completed state + recap shape == PHP', !dc, dc || '');
  }
  const missing = Object.keys(KEYSETS);
  const jsTypes = Object.keys(actionTypes);
  const phpOnly = missing.filter((t) => !jsTypes.includes(t));
  check('every PHP action type also appears in JS games', phpOnly.length === 0, phpOnly.join(','));
  if (unknownTypes.size) console.log('  note: JS action types not present in the PHP sample (unverified keys): ' + [...unknownTypes].join(', '));
}

// ═════════════ 3. fromOnline ═════════════
{
  const online = JSON.parse(JSON.stringify(sample ? sample.fresh : E.state(pack, E.create(pack, { boardId: board.id, mode: 'drinks', intensity: 2, entries: [{ name: 'A' }, { name: 'B' }, { name: 'C' }, { name: 'D' }] }))));
  online.room.type = 'online'; online.room.join_code = 'ABCDE'; online.room.token = 'x';
  online.players = [
    { id: 101, name: 'Mo', color: '#f59e0b', avatar: 'f3-4', position: 17, status: 'active', turn_order: 0, powers: { shield: 2, reroll: 0 }, is_host: true, missed: 0 },
    { id: 205, name: 'Lu', color: '#ef4444', avatar: 'h2-1', position: 44, status: 'active', turn_order: 1, powers: { shield: 0, reroll: 0 }, is_host: false, missed: 1 },
    { id: 330, name: 'Gone', color: '#3b82f6', avatar: 'bad', position: 9, status: 'left', turn_order: 2, powers: { shield: 1 }, is_host: false, missed: 4 },
    { id: 411, name: 'Ty', color: '#10b981', avatar: 'a2-5', position: 70, status: 'active', turn_order: 3, powers: { shield: 3 }, is_host: false, missed: 0 },
  ];
  Object.assign(online.room, { status: 'active', current_player_id: 205, round: 6, active_rule: 'No pointing', content_mode: 'family', intensity: 3, tile_count: N });
  online.pending = { type: 'tf', actor_id: 205, card: { text: 'x' } };
  const g = E.fromOnline(pack, online);
  const st = E.state(pack, g);
  check('fromOnline keeps seat order/names/positions', deepEq(st.players.map((p) => [p.name, p.position, p.status]), [['Mo', 17, 'active'], ['Lu', 44, 'active'], ['Gone', 9, 'left'], ['Ty', 70, 'active']]));
  check('fromOnline: party type, no pending, current player mapped', st.room.type === 'party' && st.pending === null && st.players.find((p) => p.id === st.room.current_player_id).name === 'Lu');
  check('fromOnline keeps round, rule, mode, intensity, powers', st.room.round === 6 && st.room.active_rule === 'No pointing' && st.room.content_mode === 'family' && st.room.intensity === 3 && st.players[0].powers.shield === 2 && st.players[3].powers.shield === 3);
  check('fromOnline: invalid avatar replaced by a valid default', /^[a-z]\d+-\d+$/.test(st.players[2].avatar));
  let ok = true;
  try { for (let i = 0; i < 400 && E.state(pack, g).room.status !== 'completed'; i++) { const s2 = E.state(pack, g); if (s2.pending) { const [c, t] = answerFor(s2); E.decide(pack, g, c, t); } else E.roll(pack, g); } } catch (e) { ok = false; }
  check('fromOnline game is playable to the end', ok && E.state(pack, g).room.status === 'completed');
  const gone = JSON.parse(JSON.stringify(online)); gone.room.current_player_id = 330;
  const sg = E.state(pack, E.fromOnline(pack, gone));
  check('fromOnline: current player who left → first active', sg.players.find((p) => p.id === sg.room.current_player_id).status === 'active');
  const solo = JSON.parse(JSON.stringify(online)); solo.players = solo.players.slice(0, 1);
  const ss = E.state(pack, E.fromOnline(pack, solo));
  check('fromOnline: only one player left → completed', ss.room.status === 'completed' && ss.room.winner_player_id != null);
  const done = JSON.parse(JSON.stringify(online)); done.room.status = 'completed'; done.room.winner_player_id = 411; done.players[3].status = 'won';
  const sd = E.state(pack, E.fromOnline(pack, done));
  check('fromOnline: finished game stays finished', sd.room.status === 'completed' && sd.players.find((p) => p.id === sd.room.winner_player_id).name === 'Ty');
}

// ═════════════ 4. rematch ═════════════
{
  let g = E.create(pack, { boardId: board.id, mode: 'drinks', intensity: 2, entries: [{ name: 'A', color: '#111111', avatar: 'f2-3' }, { name: 'B' }, { name: 'C' }] });
  let early = false; try { E.rematch(pack, g); } catch (e) { early = e instanceof E.GameError && /still going/.test(e.message); }
  check('rematch refused while the game is on', early);
  E.leave(pack, g, E.state(pack, g).players[2].id); // C leaves
  for (let i = 0; i < 4000 && E.state(pack, g).room.status !== 'completed'; i++) { const st = E.state(pack, g); if (st.pending) { const [c, t] = answerFor(st); E.decide(pack, g, c, t); } else E.roll(pack, g); }
  const ng = E.rematch(pack, g), ns = E.state(pack, ng);
  check('rematch: leavers stay out, same names/colours/avatars', deepEq(ns.players.map((p) => [p.name, p.color, p.avatar]), [['A', '#111111', 'f2-3'], ['B', ns.players[1].color, ns.players[1].avatar]]));
  check('rematch: fresh positions/powers/round, active, start logged', ns.players.every((p) => p.position === 0 && p.status === 'active' && p.powers.shield === 1) && ns.room.status === 'active' && ns.room.round === 1 && ns.actions.length === 1 && ns.actions[0].type === 'start');
}

// ═════════════ setup validation messages ═════════════
{
  const t = (fn) => { try { fn(); return null; } catch (e) { return e instanceof E.GameError ? e.message : 'WRONG:' + e; } };
  check('too few players message', t(() => E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: [{ name: 'A' }] })) === 'Party games need 2–12 players.');
  check('unknown mode message', t(() => E.create(pack, { boardId: board.id, mode: 'x', intensity: 1, entries: [{}, {}] })) === 'Unknown game mode.');
  check('lovers disabled message', t(() => E.create(pack, { boardId: board.id, mode: 'lovers', intensity: 1, entries: [{}, {}] })) === "Lovers mode isn't available yet.");
  check('unknown intensity message', t(() => E.create(pack, { boardId: board.id, mode: 'family', intensity: 9, entries: [{}, {}] })) === 'Unknown intensity.');
  check('unknown board message', t(() => E.create(pack, { boardId: 9999, mode: 'family', intensity: 1, entries: [{}, {}] })) === 'That board is not available.');
  const g = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: [{ name: '  ' }, { name: 'X' }] });
  check('blank name becomes "Player 1"', E.state(pack, g).players[0].name === 'Player 1');
  check('decide with nothing pending message', t(() => E.decide(pack, g, 'use', null)) === 'There is nothing to decide right now.');
}

// ── summary ──
const passed = results.filter((r) => r.ok).length;
console.log('\n┌──────────────────────────────────────────────┐');
console.log('│ SNLEngine tests                              │');
console.log('├──────────────────────────────────────────────┤');
console.log(`│ games played        ${String(games).padStart(6)}  completed ${String(completed).padStart(5)} │`);
console.log(`│ avg steps/game      ${String(Math.round(steps / Math.max(1, games))).padStart(6)}  max       ${String(maxSteps).padStart(5)} │`);
console.log(`│ avg rounds/game     ${String((rounds / Math.max(1, completed)).toFixed(1)).padStart(6)}  leaves    ${String(leaves).padStart(5)} │`);
console.log(`│ illegal calls caught${String(illegalOk).padStart(6)}  leaks     ${String(leaks).padStart(5)} │`);
console.log('│ prompts: ' + Object.entries(pendingTypes).map(([k, v]) => k + ':' + v).join(' ').slice(0, 36).padEnd(36) + '   │');
console.log('│ actions: ' + Object.keys(actionTypes).length + ' types                              │'.slice(0, 40));
console.log(`│ checks passed       ${String(passed).padStart(3)}/${String(results.length).padEnd(3)}                    │`);
console.log('└──────────────────────────────────────────────┘');
console.log('action types seen: ' + Object.keys(actionTypes).sort().join(', '));
process.exit(failures ? 1 : 0);
