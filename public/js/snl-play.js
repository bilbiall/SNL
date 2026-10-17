/* ═══════════════════════════════════════════════════════════════════════
   Snakes & Liquor — table client.

   The server is the referee: every roll, card and prompt is written to the room's
   action log. This script only ever (1) sends the player's intent, (2) replays new
   log entries IN ORDER so every device animates the same story, and (3) draws the
   latest state once the animations have finished. Party mode (one device) and online
   mode (many devices) are the same code path — only "who may act" differs.

   All glyphs are game icons from /icons.svg (game-icons.net, CC BY 3.0) — no emoji.
   ═══════════════════════════════════════════════════════════════════════ */
(() => {
  'use strict';
  const C = window.SNL;
  const $ = (id) => document.getElementById(id);
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  // ArcadeSound is a top-level `const` in arcade-sounds.js (not a window property).
  const snd = (n) => { try { SNLAudio.play(n); } catch (_) {} };
  /** A meme sound for a moment (pick / shot / fail / snake / ladder / correct / win / bluff / swap / duel / everyone / power / block). */
  const meme = (m) => { if (!m) return; try { SNLAudio.react(m); } catch (_) {} };

  // ───────────────────────── icons ─────────────────────────
  const ICONS = new Set(C.iconKeys);
  /** A game icon by key. Anything that isn't a known key (e.g. a custom glyph an admin typed) is shown as plain text. */
  const ic = (name, cls = '') => ICONS.has(name)
    ? `<svg class="ic ${cls}" aria-hidden="true"><use href="${C.iconUrl}#i-${name}"/></svg>`
    : `<span class="ic-txt ${cls}">${esc(name || '')}</span>`;

  const HOP_MS = C.timing.hop_ms;
  let S = C.state;            // what the screen currently shows
  let latest = C.state;       // newest state from the server
  let lastSeq = S.room.event_seq;
  let lastTurn = S.room.current_player_id;
  const queue = [];
  let busy = false;           // an animation / card is on screen
  let rolling = false;        // I tapped the die and am waiting for the server
  let turnLeft = null, pendingLeft = null;
  let rematchRedirected = false;
  const tokens = {};          // playerId -> element
  const shown = {};           // playerId -> square the token is displayed on

  const ST = $('stage'), BOARD = $('boardWrap'), DIE = $('dieBtn'), CUBE = $('dieCube');
  const players = () => latest.players;
  const P = (id) => players().find((p) => p.id === id) || { id, name: 'Someone', color: '#64748b', avatar: 'm1-3', powers: {}, status: 'active' };
  const iAmActor = (id) => C.isParty || id === C.meId;
  /** Can this device answer the current prompt? (Some — like "call bluff" — let several players respond.) */
  const canAct = (p) => C.isParty || (p.actors ? p.actors.includes(C.meId) : p.actor_id === C.meId);
  const kindInfo = (k) => C.kinds[k] || { label: k, icon: 'cards', help: '' };
  const kindColor = (k) => C.kindColors[k] || '#f59e0b';

  // ───────────────────────── small UI helpers ─────────────────────────
  function toast(text, ms = 2600, color) {
    const box = $('toasts');
    while (box.children.length >= 3) box.firstChild.remove();
    const t = document.createElement('div');
    t.className = 'toast';
    if (color) t.style.setProperty('--c', color);
    t.innerHTML = text;
    box.appendChild(t);
    setTimeout(() => { t.classList.add('out'); setTimeout(() => t.remove(), 300); }, ms);
  }
  // Retro pixel avatars ("f3-4" = look f3, skin shade 4) are images; anything else falls back to a game icon.
  const AV = /^[a-z][0-9]+-[0-9]+$/;
  const avatar = (p) => AV.test(p.avatar || '') ? `<img class="avimg" src="${C.avatarBase}/${p.avatar}.svg" alt="">` : ic(p.avatar || 'cards');
  const chip = (p) => `<span class="tgt static" style="--c:${p.color}"><span class="av">${avatar(p)}</span>${esc(p.name)}</span>`;
  const CARD_OVERLAYS = ['ovCard', 'ovPrompt', 'ovConfirm', 'ovMenu', 'ovWin', 'ovLobby'];
  /** Shows an overlay; a freshly dealt card gets its whoosh and a tick for every word that lands. */
  function show(id, html) {
    const el = $(id);
    const fresh = html !== undefined && !el.classList.contains('show');
    if (html !== undefined) el.innerHTML = html;
    el.classList.add('show');
    if (fresh && CARD_OVERLAYS.indexOf(id) !== -1) {
      snd('deal');
      el.querySelectorAll('.pc-text .w').forEach((w, i) => { if (i < 16) setTimeout(() => snd('pip'), 560 + i * 70 + 40); });
    }
    return el;
  }
  function hide(id) { $(id).classList.remove('show'); }

  const isRotated = () => innerWidth < 1024 && matchMedia('(orientation: portrait)').matches;
  /** Where player `id` waits before their first roll: a slot inside the GameSet start zone (spread in a grid, centred). */
  function zoneSlot(id, z) {
    const ids = players().filter((q) => q.status !== 'left' && (shown[q.id] === undefined ? q.position : shown[q.id]) === 0).sort((a, b) => a.turn_order - b.turn_order).map((q) => q.id);
    const i = ids.indexOf(id);
    if (i < 0) return null;
    const k = ids.length;
    const cs = getComputedStyle(document.documentElement);
    const ratio = (parseFloat(cs.getPropertyValue('--bw')) || 1264) / (parseFloat(cs.getPropertyValue('--bh')) || 848);
    const cols = Math.max(1, Math.min(k, Math.round(Math.sqrt(k * (z.width * ratio) / z.height))));
    const rows = Math.ceil(k / cols);
    const r = Math.floor(i / cols), c = i % cols, inRow = Math.min(cols, k - r * cols);
    return { l: z.left + z.width * ((c + 0.5 + (cols - inRow) / 2) / cols), t: z.top + z.height * ((r + 0.5) / rows) };
  }
  function xy(n, id) {
    const p = C.positions[n] || C.positions[0] || { left: 8, top: 90, left_m: 8, top_m: 90 };
    if (n === 0 && p.zone && id != null) { const s = zoneSlot(id, p.zone); if (s) return s; }
    return isRotated() ? { l: p.left_m, t: p.top_m } : { l: p.left, t: p.top };
  }
  function place(el, n) { const { l, t } = xy(n, +el.dataset.pid); el.style.left = l + '%'; el.style.top = t + '%'; }

  // ───────────────────────── tokens ─────────────────────────
  function ensureTokens() {
    players().forEach((p) => {
      if (tokens[p.id]) return;
      const el = document.createElement('div');
      el.className = 'token';
      el.dataset.pid = p.id;
      el.style.setProperty('--c', p.color);
      el.innerHTML = `${avatar(p)}<span class="tname">${esc(p.name)}</span>`;
      BOARD.appendChild(el);
      tokens[p.id] = el;
      shown[p.id] = p.position;
      place(el, p.position);
    });
    layoutTokens();
  }

  /** Tokens sharing a square fan out so nobody is hidden underneath. */
  function layoutTokens() {
    const groups = {};
    players().forEach((p) => {
      const el = tokens[p.id];
      if (!el) return;
      el.classList.toggle('gone', p.status === 'left');
      if (p.status === 'left') return;
      (groups[shown[p.id]] = groups[shown[p.id]] || []).push(p.id);
    });
    const zone = C.positions[0] && C.positions[0].zone;
    if (zone && groups[0]) groups[0].forEach((id) => place(tokens[id], 0)); // re-spread everyone still waiting
    Object.keys(groups).forEach((key) => {
      const ids = groups[key];
      ids.forEach((id, i) => {
        const el = tokens[id];
        if (ids.length === 1 || (zone && +key === 0)) { el.style.setProperty('--ox', '0px'); el.style.setProperty('--oy', '0px'); return; }
        const a = (Math.PI * 2 * i) / ids.length - Math.PI / 2;
        const r = Math.min(14, 6 + ids.length * 2.2);
        el.style.setProperty('--ox', (Math.cos(a) * r).toFixed(1) + 'px');
        el.style.setProperty('--oy', (Math.sin(a) * r).toFixed(1) + 'px');
      });
    });
  }

  /** After a reconnect, a swap, or a rotation, put every token where the server says it is. */
  function syncTokens() {
    players().forEach((p) => {
      const el = tokens[p.id];
      if (!el) return;
      if (shown[p.id] !== p.position) shown[p.id] = p.position;
      place(el, shown[p.id]);
      el.classList.toggle('active', S.room.status === 'active' && p.id === S.room.current_player_id);
    });
    layoutTokens();
  }
  const replaceAll = () => players().forEach((p) => tokens[p.id] && place(tokens[p.id], shown[p.id]));
  /** Small screens: when the board leaves a free strip on the right, show the recent moves there, above the die, never over the board. */
  function layoutSide() {
    const hb = document.querySelector('.hud-br'), nav = $('topnav');
    const side = (ST.clientWidth - BOARD.offsetWidth) / 2;                 // free width each side of the board
    const bottom = (hb ? hb.offsetHeight : 150) + 16;                         // the die dock
    const top = (nav ? nav.offsetHeight : 40) + 8;
    const room = ST.clientHeight - bottom - top;
    const desktop = ST.clientWidth >= 1000 && ST.clientHeight >= 600;         // the full sidebar already has the feed
    const ok = !desktop && side >= 104 && room >= 64;
    ST.classList.toggle('feed-mini', ok);
    const lw = Math.floor(side) - 12;                                      // the left strip: players + their meters
    ST.classList.toggle('list-mini', !desktop && lw >= 96);
    if (!desktop && lw >= 96) ST.style.setProperty('--lw', Math.min(lw, 210) + 'px');
    if (ok) { ST.style.setProperty('--sidew', Math.min(Math.floor(side) - 14, 230) + 'px'); ST.style.setProperty('--feedbot', bottom + 'px'); }
  }
  addEventListener('resize', () => { layoutSide(); });
  addEventListener('orientationchange', () => setTimeout(layoutSide, 300));
  addEventListener('resize', replaceAll);
  addEventListener('orientationchange', () => setTimeout(replaceAll, 250));

  async function hop(id, path) {
    const el = tokens[id];
    if (!el) return;
    for (const n of path) {
      shown[id] = n;
      place(el, n);
      layoutTokens();
      snd('move');
      await sleep(HOP_MS);
    }
    landed(id);
  }
  async function glide(id, n) {
    const el = tokens[id];
    if (!el) return;
    el.classList.add('glide');
    shown[id] = n;
    place(el, n);
    layoutTokens();
    await sleep(1050);
    el.classList.remove('glide');
    landed(id);
  }

  /** A little squash-and-stretch plus an expanding ring where a token comes to rest. */
  function landed(id) {
    const el = tokens[id];
    if (!el) return;
    el.classList.remove('landed'); void el.offsetWidth; el.classList.add('landed');
    setTimeout(() => el.classList.remove('landed'), 520); // so the idle bob can resume
    const { l, t } = xy(shown[id], id);
    const r = document.createElement('div');
    r.className = 'ripple'; r.style.left = l + '%'; r.style.top = t + '%';
    BOARD.appendChild(r);
    setTimeout(() => r.remove(), 1000);
  }
  /** The number rolled, floating up off the roller's token. */
  function rollPop(id, value) {
    const { l, t } = xy(shown[id] ?? 0, id);
    const r = document.createElement('div');
    r.className = 'rollpop'; r.style.left = l + '%'; r.style.top = t + '%'; r.style.setProperty('--c', P(id).color);
    r.innerHTML = `${ic('dice')}<span>${value}</span>`;
    BOARD.appendChild(r);
    setTimeout(() => r.remove(), 1300);
  }

  // ───────────────────────── the die ─────────────────────────
  const FACE = { 1: [0, 0], 2: [-90, 0], 3: [0, -90], 4: [0, 90], 5: [90, 0], 6: [0, 180] };
  let dieTimer = null;
  function spinDie() {
    CUBE.style.transition = 'none'; CUBE.classList.add('spinning');
    DIE.classList.remove('tossing'); void DIE.offsetWidth; DIE.classList.add('tossing');
  }
  function settleDie(v) {
    CUBE.classList.remove('spinning');
    DIE.classList.remove('tossing');
    const [x, y] = FACE[v] || FACE[1];
    CUBE.style.transition = 'transform .5s cubic-bezier(.25,1,.5,1)';
    CUBE.style.transform = `rotateX(${720 - 18 + x}deg) rotateY(${1080 + 22 + y}deg)`;
    const badge = $('dieVal');
    badge.textContent = v;
    badge.classList.add('show');
    clearTimeout(dieTimer);
    dieTimer = setTimeout(() => badge.classList.remove('show'), 2200);
  }

  // ───────────────────────── talking to the server ─────────────────────────
  async function post(url, body = {}) {
    if (typeof url === 'string' && url.indexOf('local:') === 0) { // a party game running on this device — no server involved
      const res = SNLLocal.act(url.slice(6), body);
      if (res.error) { toast(`${ic('info')} ${esc(res.error)}`, 3400, '#f87171'); if (res.state) ingest(res.state); return null; }
      ingest(res.state);
      return res.state;
    }
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': C.csrf },
        body: JSON.stringify(body),
      });
      const data = await r.json().catch(() => ({}));
      if (r.status === 419) { // the page's session token expired — a refresh gets a fresh one (party links and online seats survive it)
        toast(`${ic('replay')} Refreshing…`, 1500);
        setTimeout(() => location.reload(), 900);
        return null;
      }
      if (!r.ok) {
        if (data.error) toast(`${ic('info')} ${esc(data.error)}`, 3400, '#f87171');
        else toast(r.status === 429 ? `${ic('hourglass')} Slow down a little…` : `${ic('info')} Something went wrong — try again.`, 3000, '#f87171');
        if (data.room) ingest(data);
        return null;
      }
      ingest(data);
      return data;
    } catch (_) {
      toast(`${ic('info')} Connection problem — try again.`, 3000, '#f87171');
      if (++netFails >= 2) netLost();
      return null;
    }
  }

  function ingest(ns) {
    if (!ns || !ns.room) return;
    if (ns.room.event_seq < latest.room.event_seq) return; // an older response that lost the race
    const fresh = (ns.actions || []).filter((a) => a.seq > lastSeq);
    if (fresh.length) { lastSeq = fresh[fresh.length - 1].seq; queue.push(...fresh); }
    latest = ns;
    turnLeft = ns.room.turn_seconds_left;
    pendingLeft = ns.room.pending_seconds_left;
    pump();
  }

  async function pump() {
    if (busy) return;
    busy = true;
    DIE.disabled = true;
    hide('ovPrompt'); // whatever it was asking has been answered — the replay below shows what happened
    try {
      while (queue.length) {
        try { await play(queue.shift()); } catch (e) { console.error(e); }
      }
    } finally {
      busy = false;
      rolling = false;
      render();
      if (queue.length) pump(); // something arrived while rendering
    }
  }

  // ───────────────────────── replaying actions ─────────────────────────
  /** One line for the "recent moves" feed (shown in the right sidebar on wide screens). */
  function feedLine(a) {
    const who = a.player_id !== undefined ? P(a.player_id) : null;
    const n = (p) => `<b>${esc(p.name)}</b>`;
    const clip = (t, k = 70) => esc(t.length > k ? t.slice(0, k - 1) + '…' : t);
    switch (a.type) {
      case 'start': return [`${ic('flag')} Game on!`, '#f59e0b'];
      case 'roll': return [`${ic('dice')} ${n(who)} rolled a <b>${a.roll}</b>`, who.color];
      case 'reroll': return [`${ic('reroll')} ${n(who)} rerolled → <b>${a.new_roll}</b>`, '#6366f1'];
      case 'move': return a.via === 'snake_head'
        ? [`${ic('snake')} ${n(who)} slid down to ${a.to}`, '#dc2626'] : [`${ic('ladder')} ${n(who)} climbed to ${a.to}`, '#16a34a'];
      case 'card': return [`${ic(a.icon || 'cards')} ${a.kind === 'everyone' ? 'Everybody' : n(P(a.source_id))}: ${clip(a.text)}`, kindColor(a.kind)];
      case 'shield': return [`${ic('shield')} ${n(who)} blocked it`, '#38bdf8'];
      case 'duel': return [`${ic('duel')} ${n(P(a.a_id))} vs ${n(P(a.b_id))}: <b>${esc(P(a.loser_id).name)}</b> lost`, '#ef4444'];
      case 'swap': return [`${ic('swap')} ${n(P(a.a_id))} swapped with ${n(P(a.b_id))}`, '#8b5cf6'];
      case 'power': return [`${ic('power')} ${n(who)} found a power-up`, '#eab308'];
      case 'rule': return [`${ic('rule')} New rule: ${clip(a.text, 60)}`, kindColor('rule')];
      case 'neutral': return [`${ic('hourglass')} ${n(who)}: ${clip(a.text, 50)}`, '#6b7280'];
      case 'extra_turn': return [`${ic('dice')} ${n(who)} rolls again`, '#38bdf8'];
      case 'joined': return [`${ic('team')} ${n(who)} joined the game`, '#34d399'];
      case 'factcheck': return [`${ic(a.correct ? 'check' : 'cross')} ${n(who)} ${a.correct ? 'got it right' : 'got it wrong'} — it's <b>${a.answer ? 'TRUE' : 'FALSE'}</b>`, a.correct ? '#34d399' : '#f87171'];
      case 'bluff_said': return [`${ic('jester')} ${n(who)} made a claim`, '#14b8a6'];
      case 'bluff_pass': return [`${ic('check')} Nobody doubted ${n(who)}`, '#34d399'];
      case 'bluff_called':
        return [`${ic('search')} ${n(P(a.caller_id))} called bluff on ${n(who)}`, '#f87171'];
      case 'bluff_result': return a.result === 'void' ? null : [`${ic(a.result === 'proved' ? 'check' : 'search')} ${a.result === 'proved' ? 'Proven' : 'Bluff caught'}!`, a.result === 'proved' ? '#34d399' : '#f87171'];
      case 'spot_result': return [`${ic('mic')} ${n(who)} ${a.nailed ? 'nailed it' : 'ran out of time'}`, a.nailed ? '#34d399' : '#f87171'];
      case 'win': return [`${ic('trophy')} ${n(who)} reached the finish!`, '#fbbf24'];
      case 'left': return [`${ic('exit')} ${n(who)} left`, '#6b7280'];
      case 'skip': return [`${ic('clock')} ${n(who)} ran out of time`, '#6b7280'];
      default: return null; // reactions etc. don't belong in the log
    }
  }
  function feedAdd(a) {
    const list = $('feedList');
    if (!list) return;
    const l = feedLine(a);
    if (!l) return;
    const el = document.createElement('div');
    el.className = 'fi';
    el.style.setProperty('--c', l[1]);
    el.innerHTML = l[0];
    list.appendChild(el);
    while (list.children.length > 8) list.firstChild.remove();
  }

  async function play(a) {
    const who = a.player_id !== undefined ? P(a.player_id) : null;
    feedAdd(a);
    switch (a.type) {
      case 'start': toast(`${ic('flag')} Game on!`, 2200, '#f59e0b'); snd('notify'); await sleep(450); break;

      case 'roll': {
        spinDie();
        snd('roll');
        await sleep(620);
        settleDie(a.roll);
        rollPop(a.player_id, a.roll);
        await sleep(560);
        await hop(a.player_id, a.hop_path || []);
        if (a.overshoot) { toast(`Needed exactly ${a.overshoot.needed} — bounced back to ${a.overshoot.bounced_to}`, 2400); await sleep(500); }
        await sleep(280);
        break;
      }

      case 'reroll':
        toast(`${ic('reroll')} <b>${esc(who.name)}</b> used a Reroll: ${a.from_roll} → ${a.new_roll}`, 2200, '#6366f1');
        snd('boostArm');
        break;

      case 'move': {
        const snake = a.via === 'snake_head';
        toast(snake ? `${ic('snake')} Snake! ${esc(who.name)} slides to ${a.to}` : `${ic('ladder')} Ladder! ${esc(who.name)} climbs to ${a.to}`, 2200, snake ? '#dc2626' : '#16a34a');
        snd(snake ? 'snake' : 'ladder');
        meme(snake ? 'snake' : 'ladder');
        await sleep(350);
        await glide(a.player_id, a.to);
        await sleep(250);
        break;
      }

      case 'card': await cardOverlay(a); break;

      case 'shield': {
        const t = tokens[a.player_id];
        t && t.classList.add('shieldfx');
        snd('shieldUp'); meme('block');
        toast(`${ic('shield')} <b>${esc(who.name)}</b> blocked it!`, 2200, '#38bdf8');
        await sleep(1500);
        t && t.classList.remove('shieldfx');
        break;
      }

      case 'duel': await duelOverlay(a); break;

      case 'swap': {
        toast(`${ic('swap')} <b>${esc(P(a.a_id).name)}</b> swapped places with <b>${esc(P(a.b_id).name)}</b>`, 2600, '#8b5cf6');
        snd('whoosh'); meme('swap');
        await Promise.all([glide(a.a_id, a.a_pos), glide(a.b_id, a.b_pos)]);
        await sleep(250);
        break;
      }

      case 'power': {
        const name = esc(who.name);
        if (a.power) {
          const cfg = C.powersCfg[a.power];
          toast(`${ic('power')} <b>${name}</b> grabbed a ${ic(cfg.icon)} <b>${cfg.label}</b>${a.spin ? ' (spin)' : ''}`, 2400, '#eab308');
          snd('badge'); meme('power');
        } else {
          toast(`${ic('power')} ${name} found a power-up — but is already full!`, 2400, '#eab308');
        }
        await sleep(900);
        break;
      }

      case 'factcheck': await factResultOverlay(a); break;

      case 'random_pick': await randomPickOverlay(a); break;

      case 'extra_turn':
        snd('bonus'); meme('power');
        flavorSplash(who, a.text, 'dice', 'bonus turn');
        await sleep(3600);
        break;

      case 'joined': {
        ensureTokens();
        snd('arrive');
        toast(`${ic('team')} <b>${esc(who.name)}</b> joined the game`, 2800, '#34d399');
        await sleep(500);
        break;
      }

      case 'neutral':
        snd('arrive');
        flavorSplash(who, a.text);
        await sleep(4000);
        break;

      case 'bluff_said':
        toast(`${ic('jester')} <b>${esc(who.name)}</b> has made a claim — believe it?`, 2600, '#14b8a6');
        snd('drum');
        await sleep(500);
        break;

      case 'bluff_pass':
        toast(`${ic('check')} Nobody doubted <b>${esc(who.name)}</b> — safe!`, 2600, '#34d399');
        snd('ladder');
        await sleep(600);
        break;

      case 'bluff_called':
        meme('bluff');
        toast(`${ic('search')} <b>${esc(P(a.caller_id).name)}</b> calls BLUFF on <b>${esc(who.name)}</b>!`, 3000, '#f87171');
        snd('mystery');
        await sleep(1200);
        break;

      case 'bluff_result': await bluffResultOverlay(a); break;

      case 'spot_result':
        if (a.nailed) { toast(`${ic('mic')} <b>${esc(who.name)}</b> nailed it!`, 2400, '#34d399'); snd('quest'); meme('correct'); }
        else { toast(`${ic('hourglass')} Time's up for <b>${esc(who.name)}</b>`, 2400, '#f87171'); snd('buzz'); meme('fail'); }
        await sleep(700);
        break;

      case 'rule': await ruleOverlay(a); break;

      case 'win': {
        snd('win'); setTimeout(() => snd('quest'), 900); meme('win');
        confetti();
        toast(`${ic('trophy')} <b>${esc(who.name)}</b> reached the finish!`, 3200, '#fbbf24');
        await sleep(1400);
        break;
      }

      case 'left': toast(`${ic('exit')} ${esc(who.name)} left the game`, 2400); await sleep(350); break;
      case 'skip': toast(`${ic('clock')} ${esc(who.name)} ran out of time`, 2200); await sleep(250); break;
      case 'react': floater(a.player_id, a.reaction); snd('reaction'); break;
      default: break;
    }
  }

  const SPARKS = `<span class="spark" style="left:9%;top:26%">${ic('sparkles')}</span><span class="spark" style="right:9%;top:34%;animation-delay:.5s">${ic('sparkles')}</span><span class="spark" style="left:12%;bottom:24%;animation-delay:1s">${ic('sparkles')}</span><span class="spark" style="right:11%;bottom:28%;animation-delay:.3s">${ic('sparkles')}</span>`;

  /** A standard playing-card pop-up tinted by what kind of card it is. Every device shows it;
   *  party mode waits for a tap, online auto-closes after a few seconds. */
  /** Splits text into words that pop in one after another. */
  const words = (t) => String(t).split(' ').map((w, i) => `<span class="w" style="--i:${i}">${esc(w)}</span>`).join(' ');
  const EMBERS = Array.from({ length: 9 }, (_, i) => `<i class="ember" style="left:${8 + i * 10.5}%;--d:${(i * 0.37) % 2.4}s;--s:${0.5 + (i % 3) * 0.25}"></i>`).join('');

  /** The one card shell every pop-up uses: foil border, corner indices, watermark icon, intensity pips, footer. */
  function cardShell({ color, head, icon, who = '', text = '', sub = '', extra = '', buttons = '', intensity = 0, mid = '', cls = '' }) {
    if (!cls && (String(buttons).match(/<button/g) || []).length >= 2) cls = 'fit';   // answer cards: a compact layout that always fits
    const pips = intensity ? `<div class="pc-pips" title="Intensity">${[1, 2, 3].map((i) => `<i class="${i <= intensity ? 'on' : ''}"></i>`).join('')}</div>` : '';
    return `<div class="pcard ${cls}" style="--k:${color}">
      <span class="pc-corner tl">${ic(icon)}</span><span class="pc-corner br">${ic(icon)}</span>
      <div class="pc-mark">${ic(icon)}</div><div class="pc-rays"></div>${EMBERS}
      <div class="pc-head">${head}</div>${SPARKS}
      <div class="pc-body">
        <div class="pc-icon">${ic(icon)}</div>
        <div class="pc-mid">
          ${who ? `<div class="tgt-row">${who}</div>` : ''}
          ${mid || `<div class="pc-text">${words(text)}</div>`}
          ${sub ? `<div class="ov-sub">${sub}</div>` : ''}
          ${extra}
        </div>
        <div class="ov-btns">${buttons}</div>
        <div class="pc-foot">${pips}<span>Snakes &amp; Liquor</span></div>
      </div></div>`;
  }

  function holdCard(id, { color = '#f59e0b', head, icon, who = '', text, sub = '', seconds = 7, intensity = 0 }) {
    return new Promise((resolve) => {
      try { SNLAudio.duck(true); } catch (_) {}
      const auto = !C.isParty;
      show(id, cardShell({ color, head, icon, who, text, sub, intensity,
        buttons: '<button class="obtn" data-ok>Got it</button>',
        extra: auto ? `<div class="autobar" style="width:100%;animation-duration:${seconds}s"></div>` : '' }));
      let timer = null;
      const done = () => { clearTimeout(timer); hide(id); snd('close'); try { SNLAudio.duck(false); } catch (_) {} resolve(); };
      $(id).querySelector('[data-ok]').onclick = done;
      if (auto) timer = setTimeout(done, seconds * 1000);
    });
  }

  function cardOverlay(a) {
    const src = P(a.source_id);
    const targets = (a.target_ids || []).map(P);
    const spin = a.spin ? 'Mystery spin · ' : '';
    let head;
    let who = '';
    switch (a.kind) {
      case 'self': head = `${spin}${esc(src.name)}`; who = chip(src); break;
      case 'pick': head = a.random ? `${spin}The wheel picked` : `${spin}${esc(src.name)} picked`; who = targets.map(chip).join(''); break;
      case 'everyone': head = `${spin}Everybody!`; break;
      case 'duel': head = `${esc(targets[0]?.name || 'Someone')} lost the duel`; who = targets.map(chip).join(''); break;
      case 'fact': head = `${spin}Fun fact`; break;
      case 'heart': head = `${spin}Love ladder · ${esc(src.name)}`; who = chip(src); break;
      case 'groupgame': head = 'Everybody plays!'; break;
      case 'blank': head = 'Mystery spin'; break;
      default: head = 'Card';
    }
    snd(a.kind === 'heart' ? 'heart' : (a.kind === 'everyone' || a.kind === 'groupgame') ? (C.mode.label === 'Drinks' ? 'cheers' : 'sparkle') : a.kind === 'fact' ? 'notify' : (a.kind === 'self' || a.kind === 'pick') ? (C.mode.label === 'Drinks' ? 'clink' : 'decisionPing') : 'mystery');
    if (!a.random) meme(a.kind === 'pick' ? 'pick' : (a.kind === 'everyone' || a.kind === 'groupgame') ? 'everyone' : (a.kind === 'self' && C.mode.label === 'Drinks') || (a.kind === 'self' && a.icon === 'fire') ? 'shot' : null);
    return holdCard('ovCard', { color: a.kind === 'blank' || a.spin ? kindColor('spin') : kindColor(a.kind), head, icon: a.icon || 'cards', who, text: a.text, intensity: a.intensity || 0 });
  }

  /** The wheel spins over everyone's avatar and slows to a stop on the victim. */
  async function randomPickOverlay(a) {
    const ids = a.candidates || [];
    const mid = `<div class="rw">${ids.map((id) => chip(P(id)).replace('tgt static', 'tgt static rwc').replace('<span class="tgt', `<span data-id="${id}" class="tgt`)).join('')}</div>`;
    show('ovCard', cardShell({ color: kindColor('pick'), head: 'The wheel decides…', icon: 'dice', mid }));
    snd('roll');
    const chips = [...$('ovCard').querySelectorAll('.rw .tgt')];
    const target = Math.max(0, chips.findIndex((c) => +c.dataset.id === a.target_id));
    const steps = chips.length * 3 + target;
    for (let i = 0; i <= steps; i++) {
      chips.forEach((c, k) => c.classList.toggle('hot', k === i % chips.length));
      snd('tick');
      await sleep(70 + Math.pow(i / steps, 3) * 380);
    }
    chips[target].classList.add('won');
    snd('sparkle'); meme('pick');
    await sleep(900);
    hide('ovCard');
  }

  function ruleOverlay(a) {
    snd('mystery');
    return holdCard('ovCard', {
      color: kindColor('rule'), head: `${a.spin ? 'Mystery spin · ' : ''}New table rule`, icon: a.icon || 'rule', intensity: a.intensity || 0,
      text: a.text, sub: 'Applies to everyone until the next rule square.', seconds: 8,
    });
  }

  /** True-or-false reveal: right or wrong, the real answer, and why. */
  function factResultOverlay(a) {
    snd(a.correct ? 'quest' : 'buzz'); meme(a.correct ? 'correct' : 'fail');
    return holdCard('ovCard', {
      color: a.correct ? '#34d399' : '#f87171',
      head: a.correct ? 'Correct!' : 'Wrong!',
      icon: a.correct ? 'check' : 'cross',
      who: chip(P(a.player_id)),
      text: `${a.text}`,
      sub: `<b>${a.answer ? 'TRUE' : 'FALSE'}</b>${a.explain ? ' — ' + esc(a.explain) : ''}`,
      seconds: 9,
    });
  }

  /** The bluff has been judged. */
  function bluffResultOverlay(a) {
    const roller = P(a.player_id), caller = P(a.caller_id);
    if (a.result === 'void') {
      toast(`${ic('info')} No ruling — nobody drinks.`, 2600);
      return sleep(400);
    }
    const proved = a.result === 'proved';
    snd(proved ? 'quest' : 'buzz'); meme(proved ? 'correct' : 'fail');
    return holdCard('ovCard', {
      color: proved ? '#34d399' : '#f87171',
      head: proved ? 'Proven!' : 'Busted!',
      icon: proved ? 'check' : 'search',
      who: chip(proved ? caller : roller),
      text: proved ? `${roller.name} backed it up — ${caller.name} pays up.` : `${roller.name} was bluffing — caught red-handed.`,
      seconds: 7,
    });
  }

  async function duelOverlay(a) {
    const A = P(a.a_id), B = P(a.b_id), loser = P(a.loser_id);
    snd('roll'); setTimeout(() => snd('clash'), 600); meme('duel');
    show('ovCard', cardShell({ color: kindColor('duel'), head: 'Duel!', icon: 'duel',
      who: `${chip(A)}<b style="align-self:center;font-size:1.3rem;">${a.a_roll}</b><span style="align-self:center;font-size:.7rem;">vs</span><b style="align-self:center;font-size:1.3rem;">${a.b_roll}</b>${chip(B)}`,
      text: `${loser.name} loses!` }));
    await sleep(2600);
    hide('ovCard');
  }

  // ───────────────────────── fx ─────────────────────────
  function floater(playerId, key) {
    const el = document.createElement('div');
    el.className = 'floater';
    el.style.setProperty('--c', P(playerId).color);
    el.innerHTML = ic(key);
    const { l, t } = xy(shown[playerId] ?? 0);
    el.style.left = l + '%';
    el.style.top = t + '%';
    BOARD.appendChild(el);
    setTimeout(() => el.remove(), 1800);
  }
  function confetti() {
    const colors = ['#f59e0b', '#ec4899', '#8b5cf6', '#34d399', '#38bdf8', '#fbbf24'];
    for (let i = 0; i < 80; i++) {
      const c = document.createElement('div');
      c.className = 'confetti';
      c.style.left = Math.random() * 100 + '%';
      c.style.background = colors[i % colors.length];
      c.style.animationDuration = 1.8 + Math.random() * 2 + 's';
      c.style.animationDelay = Math.random() * 0.6 + 's';
      ST.appendChild(c);
      setTimeout(() => c.remove(), 4800);
    }
  }

  /** Game pieces that pop in around the board, then drift: dice, cards, chips, snakes, glasses… */
  (function pieces() {
    const box = $('pieces');
    if (!box) return;
    const family = C.mode && C.mode.label === 'Family';
    const set = family
      ? ['dice', 'cards', 'balloons', 'party', 'star', 'coin', 'snake', 'ladder', 'crown', 'trophy', 'ace', 'hands', 'shield', 'power']
      : ['dice', 'cards', 'cheers', 'martini', 'cork', 'coin', 'snake', 'ladder', 'ace', 'crown', 'wine', 'stein', 'shield', 'power'];
    const colors = ['#f59e0b', '#ec4899', '#8b5cf6', '#22d3ee', '#34d399', '#f87171', '#facc15'];
    const rnd = (a, b) => a + Math.random() * (b - a);
    set.forEach((name, i) => {
      const el = document.createElement('div');
      el.className = 'pc';
      const left = i % 2 === 0;
      el.style.cssText = `--x:${left ? rnd(1.5, 11) : rnd(87, 96)}%;--y:${rnd(5, 88)}%;--s:${rnd(24, 50)}px;--r:${rnd(-24, 24)}deg;--o:${rnd(.28, .55)};--c:${colors[i % colors.length]};` +
        `--dl:${(0.15 * i + rnd(0, .4)).toFixed(2)}s;--bd:${rnd(6, 11).toFixed(1)}s;--dx:${rnd(6, 16).toFixed(0)}px;--dy:${rnd(10, 22).toFixed(0)}px`;
      el.innerHTML = ic(name);
      box.appendChild(el);
    });
  })();

  // ───────────────────────── drawing the table ─────────────────────────
  /** A free square's flavour line — the same banner as "your turn", just with a line of text that lingers and animates in. */
  function flavorSplash(p, text, icon = 'hourglass', label = 'free square') {
    const el = document.createElement('div');
    el.className = 'splash flavor';
    el.style.setProperty('--c', p.color);
    el.innerHTML = `<b><span class="spl-av">${avatar(p)}</span><span class="spl-tx"><small>${ic(icon)} ${esc(p.name)} · ${esc(label)}</small><span class="fl-t">${words(text)}</span></span></b>`;
    ST.appendChild(el);
    setTimeout(() => el.remove(), 4500);
  }

  function splash(p, mine) {
    const el = document.createElement('div');
    el.className = 'splash';
    el.style.setProperty('--c', p.color);
    if (!mine) snd('arrive');
    el.innerHTML = `<b><span class="spl-av">${avatar(p)}</span><span class="spl-tx">${mine ? 'Your turn!' : esc(p.name) + "'s turn"}</span></b>`;
    ST.appendChild(el);
    setTimeout(() => el.remove(), 3300);
  }

  function render() {
    S = latest;
    if (S.room.status === 'active' && S.room.current_player_id !== lastTurn && !S.pending) {
      const p = P(S.room.current_player_id);
      const mine = !C.isParty && S.room.current_player_id === C.meId;
      splash(p, mine);
      if (mine) snd('notify');
    }
    lastTurn = S.room.current_player_id;
    ensureTokens();
    syncTokens();
    renderHudTl();
    renderPlayers();
    renderTurn();
    renderPowers();
    renderPrompt();
    renderLobby();
    renderWin();
  }

  function renderHudTl() {
    const r = S.room;
    const lv = C.levels[r.intensity] || { label: '', icon: 'fire' };
    let nav = `<span class="chip">${ic(C.mode.icon)} ${esc(C.mode.label)} · ${ic(lv.icon)} ${esc(lv.label)}</span>`;
    if (r.name) nav = `<span class="chip" title="${r.is_public ? 'Public game' : 'Private game'}">${ic(r.is_public ? 'globe' : 'tavern')} ${esc(r.name)}</span>` + nav;
    if (r.join_code) nav += `<button class="chip" type="button" id="codeChip" style="cursor:pointer;">${ic('share')} ${esc(r.join_code)}</button>`;
    $('navMid').innerHTML = nav;
    let h = '';
    if (r.status === 'active') h += `<span class="chip">${ic('dice')} Round ${r.round}</span>`;
    if (r.active_rule && r.status === 'active') h += `<button class="chip rule" type="button" id="ruleChip">${ic('rule')} ${esc(r.active_rule)}</button>`;
    $('hudTl').innerHTML = h;
    const cc = $('codeChip'); if (cc) cc.onclick = shareInvite;
    const rc = $('ruleChip'); if (rc) rc.onclick = () => toast(`${ic('rule')} ${esc(r.active_rule)}`, 5000, kindColor('rule'));
  }

  /** The Buzz Meter: its name and the level label for a score, per mode. */
  const buzzCfg = () => (C.buzz && (C.buzz[S.room.content_mode] || C.buzz.drinks)) || { name: 'Buzz Meter', levels: [[0, 'Sober']] };
  const buzzLevel = (n) => { const lv = buzzCfg().levels; let out = lv[0][1]; lv.forEach(([from, label]) => { if (n >= from) out = label; }); return out; };

  /** Left leaderboard: ordered by the Buzz Meter (not by square), each row still tracks tile progress. Rows glide to their new place. */
  function renderPlayers() {
    const r = S.room, box = $('players'), max = (C.buzz && C.buzz.max) || 12, cfg = buzzCfg();
    const before = {};
    box.querySelectorAll('.pl[data-id]').forEach((el) => { before[el.dataset.id] = { x: el.offsetLeft, y: el.offsetTop }; });
    const meter = cfg.enabled !== false;          // Drinks games use the Buzz Meter; Family games just rank by square
    const score = (p) => (meter ? (p.buzz || 0) : (p.status === 'won' ? 1e6 : p.position));
    const order = S.players.slice().sort((x, y) => ((x.status === 'left') - (y.status === 'left')) || (score(y) - score(x)) || (x.turn_order - y.turn_order));
    const topScore = order[0] && order[0].status !== 'left' ? score(order[0]) : 0;
    const leaders = order.filter((p) => p.status !== 'left' && topScore > 0 && score(p) === topScore).length;
    box.classList.toggle('no-meter', !meter);
    box.innerHTML = `<div class="pl-head">${ic(meter ? 'fire' : 'trophy')} ${esc(cfg.name)}</div>` + order.map((p, idx) => {
      const cur = r.status === 'active' && p.id === r.current_player_id;
      const out = p.status === 'left';
      const won = p.status === 'won';
      const pw = Object.entries(C.powersCfg).filter(([k]) => p.powers[k]).map(([k, c]) => `<span>${ic(c.icon)}${p.powers[k]}</span>`).join('');
      const pct = won ? 100 : Math.round((p.position / r.tile_count) * 100);
      const bz = p.buzz || 0, bpct = Math.min(100, Math.round((bz / max) * 100));
      const lead = leaders === 1 && score(p) === topScore && !out;
      const fill = `style="width:${bpct}%;background-size:${bpct ? (10000 / bpct).toFixed(0) : 100}% 100%"`;
      return `<div class="pl ${cur ? 'cur' : ''} ${out ? 'out' : ''} ${lead ? 'lead' : ''}" data-id="${p.id}" style="--c:${p.color}">
        <div class="ring" style="--p:${pct}"><div class="av">${won ? ic('crown') : avatar(p)}</div>${lead ? `<span class="lead-flame">${ic(meter ? 'fire' : 'crown')}</span>` : ''}</div>
        <div class="info"><span class="nm">${esc(p.name)}${p.id === C.meId ? ' (you)' : ''}</span><span class="sq">${won ? ic('crown') : '#' + p.position}</span><span class="pos">${won ? 'Finished' : 'Square ' + p.position + ' of ' + r.tile_count}</span><i class="bar"><b style="width:${pct}%"></b></i>
          ${meter ? `<span class="bz"><span class="bz-l">${ic('cheers')} ${esc(buzzLevel(bz))}</span><i class="bz-bar"><b ${fill}></b></i><span class="bz-n">${bz}</span></span>` : ''}</div>
        <span class="pw">${pw}</span>${meter ? `<i class="bz-mini"><b ${fill}></b></i>` : `<span class="rk">${idx + 1}</span>`}</div>`;
    }).join('');
    // glide each row from where it used to be (layout coordinates, so it works inside the rotated phone stage too)
    box.querySelectorAll('.pl[data-id]').forEach((el) => {
      const was = before[el.dataset.id];
      if (!was || !el.animate) return;
      const dx = was.x - el.offsetLeft, dy = was.y - el.offsetTop;
      if (dx || dy) el.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], { duration: 520, easing: 'cubic-bezier(.22,1,.36,1)' });
    });
  }

  function renderTurn() {
    const r = S.room;
    const b = $('turnBanner');
    CUBE.classList.remove('spinning');
    let mine = false;
    let html;
    let cls = 'wait';
    const secs = (n) => (n === null || n === undefined ? '' : ` <span style="white-space:nowrap"><span class="secs ${n <= 8 ? 'low' : ''}" data-secs>${Math.max(0, n)}</span>s</span>`);

    if (r.status === 'open') {
      html = 'Waiting to start…';
    } else if (r.status === 'completed') {
      html = `${ic('flag')} Game over`;
    } else if (S.pending) {
      const who = P(S.pending.actor_id);
      const verb = {
        pick: 'choosing', shield: 'deciding on a Shield', reroll: 'deciding on a Reroll', tf: 'making the call',
        bluff_say: 'making a claim', bluff_call: 'waiting for a challenger', bluff_verdict: 'waiting for a ruling', spot: 'in the spotlight',
      }[S.pending.type] || 'deciding';
      html = canAct(S.pending) && !C.isParty ? 'Your move!' : `${ic('hourglass')} <b>${esc(who.name)}</b> is ${verb}…`;
      if (!C.isParty) html += secs(pendingLeft);
    } else {
      const cur = P(r.current_player_id);
      mine = C.isParty || r.current_player_id === C.meId;
      if (C.isParty) { html = `<span class="dot" style="--c:${cur.color}"></span> <b>${esc(cur.name)}</b>'s turn — roll!`; cls = 'mine'; }
      else if (mine) { html = 'Your turn — roll!' + secs(turnLeft); cls = 'mine'; }
      else html = `${ic('hourglass')} <b>${esc(cur.name)}</b> is playing…` + secs(turnLeft);
    }
    const curP = r.status === 'active' ? P(S.pending ? S.pending.actor_id : r.current_player_id) : null;
    if (curP) b.style.setProperty('--c', curP.color); else b.style.removeProperty('--c');
    b.className = 'turn-banner ' + cls;
    b.innerHTML = html;
    DIE.disabled = !(r.status === 'active' && !S.pending && mine && !rolling && !busy);
    if (S.pending && S.pending.type === 'reroll') settleDie(S.pending.roll);
    const glow = $('glow');
    if (S.pending && S.pending.type === 'reroll' && canAct(S.pending)) {
      const { l, t } = xy(S.pending.preview_tile);
      glow.style.left = l + '%'; glow.style.top = t + '%';
      glow.classList.add('show');
    } else glow.classList.remove('show');
  }

  function renderPowers() {
    const r = S.room;
    const me = C.isParty ? P(r.current_player_id) : P(C.meId);
    if (r.status !== 'active' || !me || !me.powers) { $('powers').innerHTML = ''; return; }
    $('powers').innerHTML = Object.entries(C.powersCfg).map(([k, cfg]) =>
      `<span class="pwr ${me.powers[k] ? '' : 'empty'}" title="${esc(cfg.help)}">${ic(cfg.icon)} ${me.powers[k] || 0}</span>`).join('');
  }

  // ── prompts that wait on a player ──
  /** A card-shaped prompt with answer buttons — the same standard card as every pop-up. */
  function promptCard({ color, head, icon, who = '', text, sub = '', buttons = '', extra = '', cls = '' }) {
    return cardShell({ color, head, icon, who, text, sub, extra, buttons, cls });
  }

  /** A slim, non-blocking card for everyone who isn't the one being asked (online): what's being decided, and by whom. */
  function renderWatch(p) {
    const w = $('watch');
    const card = p.card;
    const who = P(p.actor_id);
    const heads = {
      tf: 'True or false?', bluff_say: 'Fact or bluff', bluff_call: 'Call bluff?', bluff_verdict: 'Was it proven?', spot: (p.card && p.card.kind === 'quickfire') ? 'Quickfire!' : 'Spotlight',
    };
    if (!card || !heads[p.type]) { w.classList.remove('show'); return; }
    const status = {
      tf: `${esc(who.name)} is making the call…`,
      bluff_say: `${esc(who.name)} is about to make a claim…`,
      bluff_call: p.actors && p.actors.includes(C.meId) ? 'Think they\'re bluffing?' : `Waiting for challengers…`,
      bluff_verdict: `${esc(P(p.caller_id).name)} called bluff on ${esc(who.name)} — judges are deciding…`,
      spot: `${esc(who.name)} is on the clock…`,
    }[p.type];
    w.style.setProperty('--k', kindColor(p.type === 'tf' ? 'fact' : p.type === 'spot' ? (card.kind === 'quickfire' ? 'quickfire' : 'spotlight') : 'bluff'));
    w.innerHTML = `<div class="w-ic">${ic(card.icon || 'cards')}</div><div class="w-tx"><b>${heads[p.type]}</b>
      <div>${p.type === 'bluff_verdict' ? '' : esc(card.text)}</div><span>${status}</span></div>`;
    w.classList.add('show');
  }

  // Spotlight keeps its own clock so a re-render (e.g. from a poll) never restarts it.
  let spotKey = null, spotEnds = 0, spotTick = null;
  function runSpotClock(key, secs) {
    if (spotKey !== key) { spotKey = key; spotEnds = Date.now() + secs * 1000; }
    clearInterval(spotTick);
    const el = () => document.querySelector('[data-spot]');
    const paint = () => {
      const e = el(); if (!e) { clearInterval(spotTick); return; }
      const left = Math.max(0, Math.ceil((spotEnds - Date.now()) / 1000));
      e.textContent = left > 0 ? left : 'Time!';
      e.parentElement.classList.toggle('low', left <= 5);
      if (left <= 0) clearInterval(spotTick);
    };
    paint();
    spotTick = setInterval(paint, 250);
  }

  function renderPrompt() {
    const p = S.pending;
    if (!p || S.room.status !== 'active') { hide('ovPrompt'); $('watch').classList.remove('show'); return; }
    if (!canAct(p)) { hide('ovPrompt'); renderWatch(p); return; }
    $('watch').classList.remove('show');
    const actor = P(p.actor_id);
    const cfg = C.powersCfg;
    const left = !C.isParty && pendingLeft !== null ? `<div class="ov-sub" data-pend>${ic('clock')} <span data-secs>${Math.max(0, pendingLeft)}</span>s</div>` : '';

    if (p.type === 'reroll') {
      const k = p.preview_kind === 'finish' ? { label: 'the finish', icon: 'flag' } : kindInfo(p.preview_kind);
      const n = actor.powers.reroll || 0;
      show('ovPrompt', promptCard({
        color: kindColor(p.preview_kind), head: `${esc(actor.name)} rolled`, icon: 'dice', who: chip(actor), text: String(p.roll),
        sub: `Lands on square <b>${p.preview_tile}</b> — ${ic(k.icon)} ${esc(k.label)}. Reroll?${left}`,
        buttons: `<button class="obtn" data-act="use">${ic(cfg.reroll.icon)} Reroll (${n} left)</button><button class="obtn alt" data-act="skip">Keep it</button>`,
      }));
      $('ovPrompt').querySelectorAll('[data-act]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.act })));
      return;
    }

    if (p.type === 'shield') {
      const src = P(p.source_id);
      const n = actor.powers.shield || 0;
      const own = p.source_id === p.actor_id; // a card the player landed on themselves
      show('ovPrompt', promptCard({
        color: '#38bdf8', head: own ? 'Your card' : (C.isParty ? `Pass the phone to ${esc(actor.name)}` : 'Incoming!'), icon: 'shield', who: chip(actor), text: p.card.text,
        sub: (own ? 'Spend a Shield to dodge it?' : `${esc(src.name)} landed this on you. Block it?`) + left,
        buttons: `<button class="obtn vio" data-act="use">${ic(cfg.shield.icon)} Block it (${n} left)</button><button class="obtn alt" data-act="skip">Take it</button>`,
      }));
      $('ovPrompt').querySelectorAll('[data-act]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.act })));
      return;
    }

    if (p.type === 'pick') {
      const head = { pick: 'Choose a friend', duel: 'Choose your opponent', swap: 'Choose who to swap with' }[p.sub] || 'Choose a player';
      const tiles = p.eligible.map((id, i) => {
        const t = P(id);
        return `<button class="ptile" data-id="${id}" style="--c:${t.color};--i:${i}"><span class="pav">${avatar(t)}</span><b>${esc(t.name)}</b><small>square ${t.position}</small></button>`;
      }).join('');
      show('ovPrompt', promptCard({
        color: kindColor(p.sub), head: `${esc(actor.name)} · ${head}`, icon: p.card.icon || 'pick', text: p.card.text, sub: left,
        extra: `<div class="pgrid">${tiles}</div>`, cls: 'wide',
      }));
      $('ovPrompt').querySelectorAll('[data-id]').forEach((b) => (b.onclick = () => answer({ choice: 'target', target_id: +b.dataset.id })));
      return;
    }

    // ── True or false: the game states a fact, the roller calls it ──
    if (p.type === 'tf') {
      show('ovPrompt', promptCard({
        color: kindColor('fact'), head: `${esc(actor.name)} · True or false?`, icon: p.card.icon || 'fact', text: p.card.text,
        sub: 'Get it right and pass. Get it wrong and you pay.' + (!C.isParty && pendingLeft !== null ? ` (<span data-secs>${Math.max(0, pendingLeft)}</span>s)` : ''),
        buttons: `<button class="obtn ok" data-ans="true">${ic('check')} True</button><button class="obtn no" data-ans="false">${ic('cross')} False</button>`,
      }));
      $('ovPrompt').querySelectorAll('[data-ans]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.ans })));
      return;
    }

    // ── Fact or bluff, step 1: the roller says their "fact" out loud ──
    if (p.type === 'bluff_say') {
      show('ovPrompt', promptCard({
        color: kindColor('bluff'), head: `${esc(actor.name)} · Fact or bluff`, icon: p.card.icon || 'jester', text: p.card.text,
        sub: 'Say it out loud — true, or totally made up. Everyone else: watch for the tell.',
        buttons: `<button class="obtn" data-say="1">${ic('mic')} I've said it</button>`,
      }));
      $('ovPrompt').querySelector('[data-say]').onclick = () => answer({ choice: 'done' });
      return;
    }

    // ── Step 2: does anyone call bluff? ──
    if (p.type === 'bluff_call') {
      if (C.isParty) {
        const btns = p.eligible.map((id) => { const t = P(id); return `<button class="tgt" data-call="${id}" style="--c:${t.color}"><span class="av">${avatar(t)}</span>${esc(t.name)}</button>`; }).join('');
        show('ovPrompt', promptCard({
          color: kindColor('bluff'), head: 'Anyone calling bluff?', icon: 'search', who: chip(actor), text: `Does anyone think ${actor.name} is bluffing?`,
          sub: 'Tap who is calling it — or let it slide.', extra: `<div class="tgt-row">${btns}</div>`,
          buttons: `<button class="obtn alt" data-none="1">Nobody — they're safe</button>`,
        }));
        $('ovPrompt').querySelectorAll('[data-call]').forEach((b) => (b.onclick = () => answer({ choice: 'call', target_id: +b.dataset.call })));
        $('ovPrompt').querySelector('[data-none]').onclick = () => answer({ choice: 'none' });
      } else {
        show('ovPrompt', promptCard({
          color: '#f87171', head: `${esc(actor.name)} made a claim`, icon: 'search', text: 'Think they\'re bluffing?',
          sub: `Call it and they must prove it — if they can, <b>you</b> pay. <span class="watch-secs">(<span data-secs>${Math.max(0, pendingLeft ?? 0)}</span>s)</span>`,
          buttons: `<button class="obtn no" data-bluff="1">${ic('search')} Call bluff!</button>`,
        }));
        $('ovPrompt').querySelector('[data-bluff]').onclick = () => answer({ choice: 'call' });
      }
      return;
    }

    // ── Step 3: was it proven? ──
    if (p.type === 'bluff_verdict') {
      const caller = P(p.caller_id);
      show('ovPrompt', promptCard({
        color: kindColor('bluff'), head: 'The verdict', icon: 'search', who: chip(caller),
        text: `${caller.name} called bluff on ${actor.name}.`,
        sub: C.isParty ? 'The table decides: did they prove it?' : 'You\'re a judge — did they prove it?',
        buttons: `<button class="obtn ok" data-v="proved">${ic('check')} Proven</button><button class="obtn no" data-v="busted">${ic('cross')} Bluff caught</button>`,
      }));
      $('ovPrompt').querySelectorAll('[data-v]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.v })));
      return;
    }

    // ── Spotlight: a creative prompt on the clock ──
    if (p.type === 'spot') {
      const secs = p.card.timer || 15;
      const qf = p.card.kind === 'quickfire';
      show('ovPrompt', promptCard({
        color: kindColor(qf ? 'quickfire' : 'spotlight'), head: `${esc(actor.name)} · ${qf ? 'Quickfire!' : 'Spotlight'}`, icon: p.card.icon || (qf ? 'clock' : 'mic'), text: p.card.text,
        extra: `<div class="spot-clock"><b data-spot>${secs}</b></div>`,
        buttons: `<button class="obtn ok" data-spotr="nailed">${ic('check')} Nailed it</button><button class="obtn no" data-spotr="failed">${ic('cross')} Couldn't</button>`,
      }));
      $('ovPrompt').querySelectorAll('[data-spotr]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.spotr })));
      runSpotClock(`${S.room.event_seq}:${p.actor_id}:${p.card.text}`, secs);
    }
  }

  let answering = false;
  async function answer(body) {
    if (answering) return;
    answering = true;
    $('ovPrompt').querySelectorAll('button').forEach((b) => (b.disabled = true));
    snd('press');
    const res = await post(C.urls.decide, body);
    answering = false;
    if (!res) renderPrompt(); // failed — let them try again
  }

  // ── lobby (online, before the host starts) ──
  let lobbySig = null, waitRaf = 0;
  /** The hopping-token loader, shrunk into the lobby card — it cycles through the avatars of whoever has joined. */
  function startWaitLoader() {
    cancelAnimationFrame(waitRaf);
    const track = $('waitTrack'), tok = $('waitTok'), img = $('waitAv');
    if (!track || !tok || !img) return;
    const tiles = [...track.querySelectorAll('.w-tile')], av = (S.players || []).filter((p) => p.status === 'active').map((p) => p.avatar).filter((k) => AV.test(k || ''));
    if (matchMedia('(prefers-reduced-motion: reduce)').matches || tiles.length < 3) return;
    const HOP = 760, GAP = 150, N = tiles.length - 1, CYCLE = N * (HOP + GAP) + 560, t0 = performance.now();
    let landed = -1, ai = 0, pitch = 0, size = 0;
    const ease = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);
    const frame = (now) => {
      if (!document.body.contains(tok)) return;                       // the card was re-drawn or closed
      if (!pitch) { pitch = tiles[1].offsetLeft - tiles[0].offsetLeft; size = tiles[0].offsetWidth; }
      const t = (now - t0) % CYCLE, h = Math.floor(t / (HOP + GAP)), local = t - h * (HOP + GAP);
      let x = 0, y = 0, sx = 1, sy = 1, sc = 1;
      if (h < N) {
        if (local < HOP) { const u = local / HOP; x = (h + ease(u)) * pitch; y = -Math.sin(Math.PI * u) * size * 1.05; const st = Math.sin(Math.PI * u) * 0.12; sy = 1 + st; sx = 1 - st * 0.6; }
        else { const s = (local - HOP) / GAP; x = (h + 1) * pitch; const sq = Math.sin((1 - s) * Math.PI / 2) * 0.2; sy = 1 - sq; sx = 1 + sq * 0.8; if (landed < h) { landed = h; if (av.length) { ai = (ai + 1) % av.length; img.src = `${C.avatarBase}/${av[ai]}.svg`; } } }
      } else { x = N * pitch; const f = Math.min(1, (t - N * (HOP + GAP)) / 260); sc = f >= 1 ? 1 : 1 - ease(f); if (f >= 1) x = 0; if (t >= CYCLE - 20) landed = -1; }
      tok.style.transform = `translate3d(${x.toFixed(1)}px,${y.toFixed(1)}px,0) scale(${(sx * sc).toFixed(3)},${(sy * sc).toFixed(3)})`;
      waitRaf = requestAnimationFrame(frame);
    };
    waitRaf = requestAnimationFrame(frame);
  }

  function renderLobby() {
    const r = S.room;
    if (r.status !== 'open') { hide('ovLobby'); lobbySig = null; cancelAnimationFrame(waitRaf); return; }
    const active = S.players.filter((p) => p.status === 'active');
    const iHost = r.host_player_id === C.meId;
    // Only redraw when something actually changed — a card that keeps re-drawing (and re-animating) can't be clicked.
    const sig = JSON.stringify([r.join_code, r.name, r.is_public, r.max_players, r.host_player_id, C.meId, active.map((p) => [p.id, p.name, p.avatar])]);
    if (sig === lobbySig && $('ovLobby').classList.contains('show')) return;
    const first = lobbySig === null;
    lobbySig = sig;
    const tiles = [['#f59e0b', 'self'], ['#ec4899', 'pick'], ['#22d3ee', 'fact'], ['#facc15', 'power'], ['#34d399', 'trophy']].map(([c, i]) => `<div class="w-tile" style="--c:${c}">${ic(i)}</div>`).join('');
    show('ovLobby', `<div class="ov-card wide ${first ? '' : 'still'}" style="--k:#f59e0b">
      <div class="ov-icon">${ic('tavern')}</div>
      <div class="ov-title">${r.name ? esc(r.name) + (r.is_public ? ' · public' : '') : 'Room code'}</div>
      <div class="code-big">${esc(r.join_code)}</div>
      <div class="ov-sub">Friends open <b>${esc(location.host)}</b> → <b>Join</b>, or tap Share.</div>
      <div class="tgt-row">${active.map(chip).join('')}</div>
      <div class="wait"><div class="w-track" id="waitTrack">${tiles}<div class="w-tok" id="waitTok"><div><img id="waitAv" src="${C.avatarBase}/${(active[0] && AV.test(active[0].avatar || '')) ? active[0].avatar : 'f1-3'}.svg" alt=""></div></div></div>
        <div class="w-txt">${active.length < 2 ? 'Waiting for players to join' : (iHost ? 'Ready when you are' : 'Waiting for the host to start')}…</div></div>
      <div class="ov-sub">${active.length} / ${r.max_players} players</div>
      <div class="ov-btns">
        <button class="obtn vio" id="lobbyShare">${ic('share')} Share invite</button>
        ${iHost ? `<button class="obtn" id="lobbyStart" ${active.length < 2 ? 'disabled' : ''}>${ic('play')} Start game</button>` : ''}
        <button class="obtn alt" id="lobbyLeave">Leave</button>
      </div></div>`);
    $('lobbyShare').onclick = shareInvite;
    $('lobbyLeave').onclick = () => confirmLeave(C.meId);
    const st = $('lobbyStart');
    if (st) st.onclick = async () => { st.disabled = true; snd('toggle'); await post(C.urls.start); };
    startWaitLoader();
  }

  // ── finished ──
  function renderWin() {
    const r = S.room;
    if (r.status !== 'completed') { hide('ovWin'); return; }
    const rc = S.recap || { ranking: [], awards: [] };
    const winner = P(r.winner_player_id);
    const rankHtml = rc.ranking.map((p, i) =>
      `<div class="rank"><span class="n">${i + 1}</span><span style="width:1.5em;text-align:center;">${p.status === 'won' ? ic('crown') : ''}</span><span style="flex:1;">${esc(p.name)}</span><span style="color:#9ca3af;">${p.status === 'left' ? 'left' : 'sq ' + p.position}</span></div>`).join('');
    const awardsHtml = rc.awards.map((a) =>
      `<div class="award">${ic(a.icon)}<span><b>${esc(a.name)}</b> · ${esc(a.title)} (${a.value})</span></div>`).join('');
    show('ovWin', `<div class="ov-card wide" style="--k:#fbbf24">
      <div class="ov-icon">${ic('trophy')}</div>
      <div class="ov-title">${r.winner_player_id ? 'We have a winner' : 'Game over'}</div>
      <div class="ov-text">${r.winner_player_id ? esc(winner.name) + ' wins!' : 'That\'s a wrap.'}</div>
      <div class="ov-sub">${rc.rounds || r.round} rounds played</div>
      <div style="text-align:left;margin:.3rem 0;">${rankHtml}</div>
      ${awardsHtml ? `<div style="text-align:left;margin:.4rem 0;border-top:1px solid rgba(255,255,255,.1);padding-top:.3rem;">${awardsHtml}</div>` : ''}
      <div class="ov-btns">
        <button class="obtn" id="winAgain">${ic('replay')} ${r.rematch_token ? 'Join rematch' : 'Play again'}</button>
        <button class="obtn vio" id="winShare">${ic('share')} Share</button>
        <a class="obtn alt" href="${C.urls.home}">${ic('home')} Home</a>
      </div>
      <div class="ov-sub" style="margin-top:.5rem;">Thanks for playing — drink responsibly and never drive after drinking.</div></div>`);
    $('winShare').onclick = async () => {
      const w = P(r.winner_player_id);
      const data = { title: 'Snakes & Liquor', text: `${w.name} just won our game of Snakes & Liquor! Play free on your phone:`, url: location.origin + '/' };
      try { if (navigator.share) { await navigator.share(data); return; } } catch (_) { return; }
      try { await navigator.clipboard.writeText(`${data.text} ${data.url}`); toast(`${ic('check')} Link copied — paste it anywhere`, 2600, '#34d399'); } catch (_) { toast(esc(data.url), 5000); }
    };
    $('winAgain').onclick = async () => {
      if (r.rematch_token) { location.href = C.urls.base + '/' + r.rematch_token; return; }
      if (C.local) { SNLLocal.act('rematch'); location.reload(); return; }
      $('winAgain').disabled = true;
      const res = await fetch(C.urls.rematch, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': C.csrf } }).then((x) => x.json()).catch(() => ({}));
      if (res.redirect) location.href = res.redirect; else { $('winAgain').disabled = false; toast(`${ic('info')} ${esc(res.error || 'Could not start a rematch')}`, 3000, '#f87171'); }
    };
    // Others at an online table follow the host into the rematch automatically.
    if (r.rematch_token && !C.isParty && !rematchRedirected) {
      rematchRedirected = true;
      toast(`${ic('replay')} Rematch is ready — taking you there…`, 2400);
      setTimeout(() => { location.href = C.urls.base + '/' + r.rematch_token; }, 1800);
    }
  }

  // ───────────────────────── menu / leave / share ─────────────────────────
  function openMenu() {
    const r = latest.room;
    const row = (v) => `<div class="htp">${ic(v.icon)}<span><b>${esc(v.label)}</b> — ${esc(v.help)}</span></div>`;
    const kinds = Object.values(C.kinds).map(row).join('');
    const pw = Object.values(C.powersCfg).map(row).join('');
    const roster = latest.players.map((p) => {
      const can = p.status === 'active' && (C.isParty || p.id === C.meId);
      return `<div class="rank">${chip(p)}<span style="flex:1;"></span><span style="color:#9ca3af;">${p.status === 'left' ? 'left' : p.position + '/' + r.tile_count}</span>
        ${can ? `<button class="obtn alt" style="padding:.2rem .55rem;font-size:.66rem;" data-out="${p.id}">${C.isParty ? 'Remove' : "I'm out"}</button>` : ''}</div>`;
    }).join('');
    show('ovMenu', `<div class="ov-card wide" style="text-align:left;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.3rem;">
        <b style="font-size:.95rem;">${esc(C.boardName)}</b><button class="obtn alt" id="menuClose" style="padding:.25rem .7rem;">Close</button></div>
      <div class="ov-title" style="text-align:left;">How to play</div>
      <div class="htp">${ic('dice')}<span>Roll to move. <b>First to the finish wins.</b> Roll too high and you bounce back.</span></div>
      ${kinds}${pw}
      ${r.active_rule ? `<div class="htp">${ic('rule')}<span><b>Table rule now:</b> ${esc(r.active_rule)}</span></div>` : ''}
      <div class="ov-title" style="text-align:left;display:flex;align-items:center;justify-content:space-between;">Players${C.isParty && r.status === 'active' ? `<button class="obtn ok" id="addPlayerBtn" style="padding:.25rem .7rem;">${ic('team')} Add a player</button>` : ''}</div>${roster}
      ${C.isParty ? '<div class="ov-sub" style="text-align:left;">Someone had enough? Remove them — the game carries on.</div>' : ''}
      <div class="htp">${ic('speaker')}<span>Meme reactions: <a href="#" id="memeTgl" style="color:#f9a8d4;font-weight:800;">${(typeof SNLAudio !== 'undefined' && SNLAudio.memes) ? 'On' : 'Off'}</a> <span style="color:#9ca3af;">— funny sounds on picks, snakes, ladders, wins…</span></span></div>
      <div class="htp" id="appRow" style="display:none;margin-top:.4rem;">${ic('info')}<span>App version <b id="appVer">…</b> · <a href="#" id="chkUpd" style="color:#f9a8d4;font-weight:800;">Check for updates</a><span id="updMsg" style="display:block;color:#9ca3af;"></span></span></div>
      <div class="ov-sub" style="text-align:left;margin-top:.5rem;">Any drink can be swapped for water. Never drive after drinking.</div>
      <div class="ov-btns" style="justify-content:flex-start;"><a class="obtn alt" href="${C.urls.home}">${ic('home')} Leave to home</a></div></div>`);
    $('menuClose').onclick = () => hide('ovMenu');
    const ap = $('addPlayerBtn'); if (ap) ap.onclick = openAddPlayer;
    const mt = $('memeTgl'); if (mt) mt.onclick = (ev) => { ev.preventDefault(); const on = !SNLAudio.memes; SNLAudio.setMemes(on); mt.textContent = on ? 'On' : 'Off'; if (on) SNLAudio.react('win'); };
    if (window.SNLUpdate) {                      // inside the Android app: show the version and let people look for a newer one
      SNLUpdate.info().then((mine) => {
        if (!mine) return;
        $('appRow').style.display = 'flex'; $('appVer').textContent = mine.versionName;
        $('chkUpd').onclick = (ev) => {
          ev.preventDefault();
          const msg = $('updMsg'); msg.textContent = 'Checking…';
          SNLUpdate.check().then((res) => {
            if (!res.remote) msg.textContent = 'Could not reach the site — connect to the internet and try again.';
            else if (res.newer && SNLUpdate.show) { msg.textContent = 'Version ' + (res.remote.versionName || '') + ' is ready.'; hide('ovMenu'); SNLUpdate.show(res); }
            else if (res.newer) msg.innerHTML = `New version <b>${esc(res.remote.versionName || '')}</b> is ready — <a href="${esc(res.me.site)}${window.SNLDesktop ? '/download/windows' : '/download/android'}" style="color:#6ee7b7;font-weight:900;">download &amp; install it</a> (install over this one; your game is kept).`;
            else msg.textContent = 'You have the latest version.';
          });
        };
      });
    }
    $('ovMenu').querySelectorAll('[data-out]').forEach((b) => (b.onclick = () => confirmLeave(+b.dataset.out)));
  }

  /** Hamburger menu → Add a player: a new seat while the game is running. */
  function openAddPlayer() {
    hide('ovMenu');
    if (typeof SNLAvatars === 'undefined') return;
    let av = SNLAvatars.random();
    show('ovConfirm', cardShell({
      color: '#10b981', head: 'Add a player', icon: 'team', cls: 'wide',
      mid: `<div class="addp"><button type="button" class="avbtn lg" id="apAv" title="Choose avatar"><img id="apImg" src="${SNLAvatars.url(av)}" alt=""></button><input id="apName" class="apname" maxlength="24" placeholder="Name" autocomplete="off"></div>`,
      sub: 'They join at the start zone and play last in the order. Up to 12 players.',
      buttons: `<button class="obtn alt" id="apNo">Cancel</button><button class="obtn ok" id="apYes">${ic('check')} Add to game</button>`,
    }));
    $('apAv').onclick = () => SNLAvatars.open(av, (k) => { av = k; $('apImg').src = SNLAvatars.url(av); });
    $('apNo').onclick = () => hide('ovConfirm');
    setTimeout(() => { const n = $('apName'); if (n) n.focus(); }, 700);
    $('apYes').onclick = async () => {
      const name = $('apName').value.trim();
      if (!name) { $('apName').focus(); toast(`${ic('info')} Give the new player a name`, 2200, '#f87171'); return; }
      $('apYes').disabled = true;
      const res = await post(C.urls.addplayer, { name, avatar: av });
      if (res) hide('ovConfirm'); else $('apYes').disabled = false;
    };
  }

  function confirmLeave(id) {
    const p = P(id);
    const self = !C.isParty && id === C.meId;
    show('ovConfirm', `<div class="ov-card">
      <div class="ov-icon">${ic('exit')}</div>
      <div class="ov-text">${self ? 'Leave this game?' : `Remove ${esc(p.name)}?`}</div>
      <div class="ov-sub">${self ? 'The game carries on without you.' : 'They drop out and the game carries on.'}</div>
      <div class="ov-btns"><button class="obtn alt" id="cfNo">Cancel</button><button class="obtn" id="cfYes" style="background:#dc2626;">${self ? "I'm out" : 'Remove'}</button></div></div>`);
    $('cfNo').onclick = () => hide('ovConfirm');
    $('cfYes').onclick = async () => {
      hide('ovConfirm');
      hide('ovMenu');
      const res = await post(C.urls.leave, { player_id: id });
      if (self && res) location.href = C.urls.home;
    };
  }

  async function shareInvite() {
    const code = latest.room.join_code;
    const url = location.origin + C.urls.join.replace(location.origin, '');
    const text = `Join my Snakes & Liquor game${latest.room.name ? ' "' + latest.room.name + '"' : ''}! Code: ${code}`;
    try {
      if (navigator.share) { await navigator.share({ title: 'Snakes & Liquor', text, url }); return; }
    } catch (_) { return; }
    try { await navigator.clipboard.writeText(`${text}\n${url}`); toast(`${ic('check')} Invite copied — paste it to your friends`, 2600, '#34d399'); }
    catch (_) { toast(`Code: <b>${esc(code)}</b>`, 5000); }
  }

  // ───────────────────────── controls ─────────────────────────
  DIE.addEventListener('click', async () => {
    if (DIE.disabled || rolling || busy) return;
    rolling = true;
    DIE.disabled = true;
    try { ArcadeSound.unlock(); SNLAudio.unlock(); } catch (_) {}
    spinDie();
    const res = await post(C.urls.roll);
    if (!res) { rolling = false; CUBE.classList.remove('spinning'); render(); }
    // On success ingest() → pump() plays the roll and finishes with render().
  });
  $('menuBtn').onclick = openMenu;

  const soundOn = (() => { try { return localStorage.getItem('snl_sound') !== '0'; } catch (_) { return true; } })();
  function setSound(on) {
    try { SNLAudio.setSfx(on); } catch (_) {}
    $('soundBtn').innerHTML = ic(on ? 'speaker' : 'mute');
    try { localStorage.setItem('snl_sound', on ? '1' : '0'); } catch (_) {}
  }
  setSound(soundOn);
  $('soundBtn').onclick = () => setSound(!SNLAudio.sfx);
  const musicBtn = $('musicBtn');
  if (musicBtn) {
    const musicOn = (() => { try { return localStorage.getItem('snl_music') !== '0'; } catch (_) { return true; } })();
    const setMusic = (on) => { SNLAudio.setMusic(on); musicBtn.classList.toggle('off', !on); musicBtn.innerHTML = ic('music'); try { localStorage.setItem('snl_music', on ? '1' : '0'); } catch (_) {} };
    setMusic(musicOn);
    musicBtn.onclick = () => setMusic(!SNLAudio.music);
  }

  const rt = $('reactToggle');
  if (rt) {
    rt.onclick = () => $('reactBar').classList.toggle('show');
    $('reactBar').querySelectorAll('button').forEach((b) => (b.onclick = () => { post(C.urls.react, { reaction: b.dataset.react }); }));
  }

  // ───────────────────────── connection watch (online rooms) ─────────────────────────
  let netFails = 0;
  let netBar = null;
  function netLost() {
    if (C.isParty || netBar || !latest || latest.room.status !== 'active') return;
    netBar = document.createElement('div');
    netBar.className = 'netbar';
    const can = typeof SNLLocal !== 'undefined' && SNLLocal.hasEngine();
    netBar.innerHTML = `<span>${ic('info')} Connection lost — reconnecting…</span>${can ? '<button type="button" id="carryOn">Carry on on this phone</button>' : ''}`;
    ST.appendChild(netBar);
    const b = netBar.querySelector('#carryOn'); if (b) b.onclick = carryOnLocally;
  }
  function netBack() {
    netFails = 0;
    if (netBar) { netBar.remove(); netBar = null; toast(`${ic('check')} Back online`, 1800, '#34d399'); }
  }
  /** No signal for good? Turn this room into a pass-the-phone game, right where everyone is. */
  function carryOnLocally() {
    show('ovConfirm', `<div class="pcard" style="--k:#f59e0b"><div class="pc-head">Carry on offline?</div><div class="pc-body"><div class="pc-mid"><div class="pc-text">Everyone plays on THIS phone from where you all are. The room on the other phones stops.</div></div><div class="ov-btns"><button class="obtn alt" id="coNo">Not yet</button><button class="obtn" id="coYes">Carry on here</button></div></div></div>`);
    $('coNo').onclick = () => hide('ovConfirm');
    $('coYes').onclick = async () => {
      try { await SNLLocal.carryOn(latest); location.href = C.urls.local || '/play/local'; }
      catch (e) { hide('ovConfirm'); toast(`${ic('info')} ${esc(e.message || 'Could not switch to offline play')}`, 4000, '#f87171'); }
    };
  }
  window.addEventListener('offline', () => { netFails = 3; netLost(); });
  window.addEventListener('online', () => { fetch(C.urls.state, { headers: { Accept: 'application/json' } }).then((r) => { if (r.ok) { netBack(); return r.json().then(ingest); } }).catch(() => {}); });

  // ───────────────────────── polling & clocks ─────────────────────────
  async function poll() {
    if (!C.isParty) {
      try {
        const r = await fetch(C.urls.state, { headers: { Accept: 'application/json' } });
        if (r.ok) { netBack(); ingest(await r.json()); }
      } catch (_) { if (++netFails >= 3) netLost(); /* a missed poll is fine — the next one catches up from the log */ }
    }
    setTimeout(poll, document.hidden ? 4000 : (latest.room.status === 'completed' ? 4000 : 1400));
  }
  document.addEventListener('visibilitychange', () => { if (!document.hidden && !C.isParty) fetch(C.urls.state, { headers: { Accept: 'application/json' } }).then((r) => r.json()).then(ingest).catch(() => {}); });

  setInterval(() => {
    if (busy) return;
    let changed = false;
    if (turnLeft !== null && turnLeft > 0) { turnLeft -= 1; changed = true; }
    if (pendingLeft !== null && pendingLeft > 0) { pendingLeft -= 1; changed = true; }
    if (!changed) return;
    document.querySelectorAll('[data-secs]').forEach((el) => {
      const v = el.closest('[data-pend]') || S.pending ? pendingLeft : turnLeft;
      el.textContent = Math.max(0, v ?? 0);
      el.classList.toggle('low', (v ?? 99) <= 8);
    });
  }, 1000);

  // Keep a phone on the table awake during party games.
  if (C.isParty && 'wakeLock' in navigator) {
    const lock = () => navigator.wakeLock.request('screen').catch(() => {});
    lock();
    document.addEventListener('visibilitychange', () => { if (!document.hidden) lock(); });
  }

  // ───────────────────────── go ─────────────────────────
  ensureTokens();
  render();
  layoutSide();
  setTimeout(layoutSide, 400);
  if (window.SNLLoader) SNLLoader.ready();      // the table is drawn — let the loader go
  if (!C.isParty) setTimeout(poll, 1400);
})();
