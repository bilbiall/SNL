// Extra checks for the free-square events and adding a player mid-game:  node scripts/test-engine-extras.mjs
import fs from 'fs';
import assert from 'assert';
await import('../public/js/snl-engine.js');
const E = globalThis.SNLEngine;
const pack = JSON.parse(fs.readFileSync('storage/app/offline-pack.json', 'utf8'));
const answer = (g) => { let guard = 0; while (g.pending && guard++ < 8) { const p = g.pending; const a = { shield: ['skip'], pick: ['target', p.eligible && p.eligible[0]], tf: ['true'], bluff_say: ['done'], bluff_call: ['none'], bluff_verdict: ['proved'], spot: ['nailed'] }[p.type]; E.decide(pack, g, a[0], a[1]); } };
const seen = {}; let extra = 0, quick = 0;
for (let n = 0; n < 300; n++) {
  const g = E.create(pack, { boardId: pack.boards[0].id, mode: n % 2 ? 'drinks' : 'family', intensity: 1 + (n % 3), entries: [{ name: 'A' }, { name: 'B' }, { name: 'C' }] });
  for (let i = 0; i < 60 && g.room.status === 'active'; i++) {
    const before = g.room.current_player_id; E.roll(pack, g);
    if (g.pending && g.pending.card && g.pending.card.kind === 'quickfire') { quick++; assert(g.pending.card.timer >= 5, 'quickfire has a timer'); }
    const last = g.actions[g.actions.length - 1];
    if (g.actions.some((a) => a.type === 'extra_turn' && a.seq > (g._seen || 0)) && !g.pending && g.room.status === 'active' && g.room.current_player_id === before) extra++;
    g._seen = g.room.event_seq; answer(g);
    g.actions.forEach((a) => { if (a.type === 'card') seen[a.kind] = (seen[a.kind] || 0) + 1; });
  }
}
assert(seen.groupgame > 0, 'group games appear'); assert(quick > 0, 'quickfire appears'); assert(extra > 0, 'roll-again keeps the same player');
// add a player mid-game
const g = E.create(pack, { boardId: pack.boards[0].id, mode: 'family', intensity: 2, entries: [{ name: 'A' }, { name: 'B' }] });
E.roll(pack, g); answer(g);
const id = E.addPlayer(pack, g, { name: 'Newbie', avatar: 'a3-2' });
const s = E.state(pack, g);
const nb = s.players.find((p) => p.id === id);
assert(nb && nb.position === 0 && nb.turn_order === 2 && nb.avatar === 'a3-2', 'new player seated at the start, last in order');
assert(g.actions[g.actions.length - 1].type === 'joined');
assert.throws(() => { for (let i = 0; i < 20; i++) E.addPlayer(pack, g, { name: 'X' + i }); }, /table is full/);
const done = E.create(pack, { boardId: pack.boards[0].id, mode: 'family', intensity: 1, entries: [{ name: 'A' }, { name: 'B' }] });
done.room.status = 'completed';
assert.throws(() => E.addPlayer(pack, done, { name: 'Late' }), /over/);
console.log('extras ok — card kinds:', JSON.stringify(seen), '| quickfire prompts:', quick, '| bonus turns:', extra);
