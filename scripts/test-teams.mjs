// Team-mode tests for public/js/snl-engine.js (the offline party engine): random full games, teams of two.
//   php artisan snl:pack            → storage/app/offline-pack.json
//   node scripts/test-teams.mjs
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
const prng = mulberry(20261019); E.setRandom(mulberry(99));
const pick = (a) => a[Math.floor(prng() * a.length)];
const t = (fn) => { try { fn(); return null; } catch (e) { return e instanceof E.GameError ? e.message : 'WRONG:' + e; } };

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

let games = 0, finished = 0, syncs = 0, shared = 0, turns = 0;
const modes = Object.keys(pack.config.modes).filter((m) => pack.config.modes[m].enabled);
for (let gi = 0; gi < 120; gi++) {
  const n = [4, 6, 8, 10, 12][gi % 5], mode = modes[gi % modes.length];
  const entries = Array.from({ length: n }, (_, i) => ({ name: 'P' + (i + 1), color: pack.config.colors[i % 12] }));
  const g = E.create(pack, { boardId: board.id, mode, intensity: 1 + (gi % 3), entries, teams: true });
  games++;
  let st = E.state(pack, g);
  // setup
  check('team_mode on', st.room.team_mode === true);
  const byTeam = {}; st.players.forEach((p) => (byTeam[p.team] = byTeam[p.team] || []).push(p));
  check('n/2 teams of two', Object.keys(byTeam).length === n / 2 && Object.values(byTeam).every((m) => m.length === 2), JSON.stringify(st.players.map((p) => p.team)));
  check('teammates share a colour', Object.values(byTeam).every((m) => m[0].color === m[1].color));
  check('pairs follow entry order (1&2, 3&4)', st.players.every((p) => byTeam[p.team].map((x) => x.name).sort().join() === ['P' + (2 * p.team - 1), 'P' + (2 * p.team)].sort().join()));
  check('turn order alternates between teams', st.players.slice(0, n / 2).map((p) => p.team).join() === Array.from({ length: n / 2 }, (_, i) => i + 1).join(), st.players.map((p) => p.team).join());
  check('first mover is the first in turn order', st.room.current_player_id === st.players[0].id);

  let steps = 0;
  while (st.room.status === 'active' && steps++ < 2000) {
    // the invariant: while nothing is mid-prompt, teammates stand on the same square
    if (!st.pending) for (const m of Object.values(groupBy(st.players.filter((p) => p.status !== 'left'), 'team'))) if (new Set(m.map((p) => p.position)).size > 1) { check('teammates on the same square', false, `game ${gi} step ${steps}: ${m.map((p) => p.position)}`); }
    if (st.pending) { const [c, tg] = answerFor(st); E.decide(pack, g, c, tg); } else { E.roll(pack, g); turns++; }
    const prev = st; st = E.state(pack, g);
    for (const a of st.actions) if (a.seq > (prev.room.event_seq || 0)) {
      if (a.type === 'team_sync') syncs++;
      if (a.type === 'card' && (a.kind === 'self' || a.kind === 'pick') && a.target_ids.length >= 2 && a.kind !== 'everyone') {
        const tm = st.players.find((p) => p.id === a.target_ids[0]).team;
        if (a.target_ids.every((id) => st.players.find((p) => p.id === id).team === tm)) shared++;
      }
    }
  }
  if (st.room.status === 'completed') finished++; else check('game finished', false, `game ${gi} (${n} players, ${mode})`);
}
function groupBy(arr, k) { return arr.reduce((o, x) => ((o[x[k]] = o[x[k]] || []).push(x), o), {}); }

// refused sizes + the add-a-player rule + rematch keeps the pairs
for (const bad of [2, 3, 5, 7, 13]) { const msg = t(() => E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: Array.from({ length: bad }, () => ({})), teams: true })); check('teams of ' + bad + ' refused', msg !== null && msg.startsWith(bad === 13 ? 'Party games need' : 'Teams of two need'), String(msg)); }
const tg = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: Array.from({ length: 4 }, (_, i) => ({ name: 'T' + i })), teams: true });
check('no new player in a team game', t(() => E.addPlayer(pack, tg, { name: 'late' })) === 'Teams are set when the game starts, so nobody can be added now.');
const solo = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: Array.from({ length: 3 }, (_, i) => ({ name: 'S' + i })) });
check('solo games unaffected (no team_mode, no team ids)', E.state(pack, solo).room.team_mode === false && E.state(pack, solo).players.every((p) => p.team === null));
// finish tg quickly then rematch
let s2 = E.state(pack, tg), k = 0;
while (s2.room.status === 'active' && k++ < 2000) { if (s2.pending) { const [c, x] = answerFor(s2); E.decide(pack, tg, c, x); } else E.roll(pack, tg); s2 = E.state(pack, tg); }
const rm = E.rematch(pack, tg), rs = E.state(pack, rm);
check('rematch keeps team mode and pairs', rs.room.team_mode && rs.players.every((p) => p.team) && rs.players.map((p) => p.name).join() !== '' && new Set(rs.players.map((p) => p.team)).size === 2);

// chosen members: the table puts players on teams (not just 1&2, 3&4), and bad splits are refused
const ents = (teams) => teams.map((t, i) => ({ name: 'M' + i, team: t }));
const mk = (teams) => E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: ents(teams), teams: true });
const chosen = mk([1, 2, 3, 1, 2, 3]); const cs = E.state(pack, chosen);
const mates = (t) => cs.players.filter((p) => p.team === t).map((p) => p.name).sort().join('+');
check('chosen members are teammates', mates(1) === 'M0+M3' && mates(2) === 'M1+M4' && mates(3) === 'M2+M5', [1, 2, 3].map(mates).join(' '));
check('teammates share a colour (the team first player)', cs.players.filter((p) => p.team === 1).every((p) => p.color === pack.config.colors[0]));
check('turn order alternates between the chosen teams', cs.players.slice(0, 3).map((p) => p.team).join() === '1,2,3', cs.players.map((p) => p.team).join());
const msgBad = (teams) => t(() => mk(teams));
check('3+1 split refused', (msgBad([1, 1, 1, 2]) || '').startsWith('Every team needs exactly two'), String(msgBad([1, 1, 1, 2])));
check('one big team refused', (msgBad([1, 1, 1, 1]) || '').startsWith('Every team needs exactly two'));
let sc = E.state(pack, chosen), kc = 0;
while (sc.room.status === 'active' && kc++ < 2500) {
  if (!sc.pending) for (const m of Object.values(groupBy(sc.players, 'team'))) if (new Set(m.map((p) => p.position)).size > 1) check('chosen teammates stay together', false, m.map((p) => p.position).join());
  if (sc.pending) { const [c, x] = answerFor(sc); E.decide(pack, chosen, c, x); } else E.roll(pack, chosen); sc = E.state(pack, chosen);
}
check('a game with chosen teams finishes', sc.room.status === 'completed');

// team names: typed, cleaned, defaulted, kept on rematch
const named = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: Array.from({ length: 4 }, (_, i) => ({ name: 'N' + i })), teams: true, teamNames: ['The Snakes', '  <b>Ladder</b>   Legends  '] });
check('team names kept and cleaned', JSON.stringify(E.state(pack, named).room.team_names) === JSON.stringify({ 1: 'The Snakes', 2: 'Ladder Legends' }), JSON.stringify(E.state(pack, named).room.team_names));
const unnamed = E.create(pack, { boardId: board.id, mode: 'family', intensity: 1, entries: Array.from({ length: 6 }, (_, i) => ({ name: 'U' + i })), teams: true });
check('blank names default to Team N', JSON.stringify(E.state(pack, unnamed).room.team_names) === JSON.stringify({ 1: 'Team 1', 2: 'Team 2', 3: 'Team 3' }));
check('solo game has no team names', Object.keys(E.state(pack, solo).room.team_names).length === 0);
let sn = E.state(pack, named), kk = 0;
while (sn.room.status === 'active' && kk++ < 2000) { if (sn.pending) { const [c, x] = answerFor(sn); E.decide(pack, named, c, x); } else E.roll(pack, named); sn = E.state(pack, named); }
check('rematch keeps team names', JSON.stringify(E.state(pack, E.rematch(pack, named)).room.team_names) === JSON.stringify({ 1: 'The Snakes', 2: 'Ladder Legends' }));

console.log(`┌─ team games: ${games} played, ${finished} finished · turns ${turns} · team_sync events ${syncs} · shared-penalty cards ${shared}`);
console.log(failures ? `${failures} FAILURES` : 'all team checks passed');
process.exit(failures ? 1 : 0);
