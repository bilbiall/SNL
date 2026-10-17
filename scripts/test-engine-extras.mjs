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

// ── no back-to-back repeats: a shuffle bag works through a deck before anything comes round again ──
{
  const g = E.create(pack, { boardId: pack.boards[0].id, mode: 'drinks', intensity: 3, entries: [{ name: 'A' }, { name: 'B' }] });
  const seen = []; let repeatsInRow = 0, free = 0, events = [], streakFree = true;
  for (let n = 0; n < 2500 && g.room.status === 'active'; n++) {
    E.roll(pack, g); answer(g);
    g.actions.filter((a) => a.seq > (g._s2 || 0)).forEach((a) => {
      if (a.type === 'neutral' || a.type === 'extra_turn' || (a.type === 'card' && a.kind === 'groupgame')) { free++; const t = a.text; if (seen.length && seen[seen.length - 1] === t) repeatsInRow++; seen.push(t); }
      if (a.type === 'extra_turn') events.push('roll'); if (a.type === 'card' && a.kind === 'groupgame') events.push('group');
    });
    g._s2 = g.room.event_seq;
    const ev = (g.deck_state && g.deck_state.events) || []; if (ev.length === 3 && ev[0] === ev[1] && ev[1] === ev[2]) streakFree = false;
    if (g.room.status !== 'active') { Object.assign(g, E.rematch(pack, g)); }
  }
  assert(repeatsInRow === 0, 'the same free-square text never appears twice in a row (' + repeatsInRow + ')');
  assert(streakFree, 'the same free-square event never happens three times running');
  const distinct = new Set(seen).size;
  console.log('shuffle bag ok — free-square lines shown:', seen.length, 'distinct:', distinct);
}

// ── Buzz Meter: every hit a player takes fills it; kind moments and group games don't ──
{
  let any = 0, gained = 0;
  for (let n = 0; n < 60; n++) {
    const g = E.create(pack, { boardId: pack.boards[0].id, mode: 'drinks', intensity: 2, entries: [{ name: 'A' }, { name: 'B' }, { name: 'C' }] });
    for (let i = 0; i < 40 && g.room.status === 'active'; i++) { E.roll(pack, g); answer(g); }
    const st = E.state(pack, g);
    st.players.forEach((p) => { assert(Number.isInteger(p.buzz) && p.buzz >= 0, 'buzz is a non-negative whole number'); if (p.buzz > 0) gained++; });
    const hits = g.players.reduce((s, p) => s + ((p.stats && p.stats.hits) || 0), 0), buzz = st.players.reduce((s, p) => s + p.buzz, 0);
    assert(buzz >= 0 && buzz >= 0 && (buzz <= hits * 2), 'the meter never outgrows the hits taken (a hit is worth 1 or 2)');
    any++;
  }
  assert(gained > 0, 'somebody gets buzzed');
  console.log('buzz meter ok —', any, 'games, players with a buzz:', gained);
}

// ── Intensity tuning: Chill is lighter, Normal is as written, Spicy makes even free squares bite ──
{
  const tileKind = {}; pack.boards[0].tiles.forEach((t) => { tileKind[t.number] = t.kind; });
  const res = {};
  for (const intensity of [1, 2, 3]) {
    let spice = 0, passes = 0, hits = 0, rolls = 0;
    for (let n = 0; n < 80; n++) {
      const g = E.create(pack, { boardId: pack.boards[0].id, mode: n % 2 ? 'drinks' : 'family', intensity, entries: [{ name: 'A' }, { name: 'B' }, { name: 'C' }] });
      let seen = 0;
      for (let i = 0; i < 40 && g.room.status === 'active'; i++) {
        E.roll(pack, g); rolls++;
        g.actions.filter((a) => a.seq > seen).forEach((a) => {
          if (a.type === 'card' && a.kind === 'self' && tileKind[a.tile] === 'blank') spice++;
          if (a.type === 'neutral' && (pack.config.chill_passes || []).includes(a.text)) passes++;
          if (a.type === 'card' && ['self', 'pick', 'everyone', 'duel'].includes(a.kind)) hits++;
        });
        seen = g.room.event_seq; answer(g);
      }
    }
    res[intensity] = { spice, passes, hitsPerRoll: +(hits / rolls).toFixed(3) };
  }
  assert(res[3].spice > 0, 'Spicy: free squares sometimes bite'); assert(res[1].spice === 0 && res[2].spice === 0, 'only Spicy bites on free squares');
  assert(res[1].passes > 0 && res[2].passes === 0 && res[3].passes === 0, 'only Chill waves cards through');
  assert(res[1].hitsPerRoll < res[2].hitsPerRoll, 'Chill hands out fewer hits than Normal');
  console.log('intensity tuning ok —', JSON.stringify(res));
}
