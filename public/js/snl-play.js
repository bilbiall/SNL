/* ═══════════════════════════════════════════════════════════════════════
   Snakes & Liquor — table client.

   The server is the referee: every roll, card and prompt is written to the room's
   action log. This script only ever (1) sends the player's intent, (2) replays new
   log entries IN ORDER so every device animates the same story, and (3) draws the
   latest state once the animations have finished. Party mode (one device) and online
   mode (many devices) are the same code path — only "who may act" differs.
   ═══════════════════════════════════════════════════════════════════════ */
(() => {
  'use strict';
  const C = window.SNL;
  const $ = (id) => document.getElementById(id);
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  // ArcadeSound is a top-level `const` in arcade-sounds.js (not a window property).
  const snd = (n) => { try { if (typeof ArcadeSound !== 'undefined') ArcadeSound.play(n); } catch (_) {} };

  const HOP_MS = C.timing.hop_ms;
  let S = C.state;            // what the screen currently shows
  let latest = C.state;       // newest state from the server
  let lastSeq = S.room.event_seq;
  const queue = [];
  let busy = false;           // an animation / card is on screen
  let rolling = false;        // I tapped the die and am waiting for the server
  let turnLeft = null, pendingLeft = null;
  let rematchRedirected = false;
  const tokens = {};          // playerId -> element
  const shown = {};           // playerId -> square the token is displayed on

  const ST = $('stage'), BOARD = $('boardWrap'), DIE = $('dieBtn'), CUBE = $('dieCube');
  const players = () => latest.players;
  const P = (id) => players().find((p) => p.id === id) || { id, name: 'Someone', color: '#64748b', emoji: '🙂', powers: {}, status: 'active' };
  const iAmActor = (id) => C.isParty || id === C.meId;
  const kindInfo = (k) => C.kinds[k] || { label: k, icon: '🃏', help: '' };

  // ───────────────────────── small UI helpers ─────────────────────────
  function toast(text, ms = 2600) {
    const box = $('toasts');
    while (box.children.length >= 3) box.firstChild.remove();
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = text;
    box.appendChild(t);
    setTimeout(() => { t.classList.add('out'); setTimeout(() => t.remove(), 300); }, ms);
  }
  const chip = (p, extra = '') => `<span class="tgt static ${extra}"><span class="av" style="background:${p.color}">${p.emoji || '🙂'}</span>${esc(p.name)}</span>`;
  function show(id, html) { const el = $(id); if (html !== undefined) el.innerHTML = html; el.classList.add('show'); return el; }
  function hide(id) { $(id).classList.remove('show'); }

  const isRotated = () => innerWidth < 1024 && matchMedia('(orientation: portrait)').matches;
  function xy(n) {
    const p = C.positions[n] || C.positions[0] || { left: 8, top: 90, left_m: 8, top_m: 90 };
    return isRotated() ? { l: p.left_m, t: p.top_m } : { l: p.left, t: p.top };
  }
  function place(el, n) { const { l, t } = xy(n); el.style.left = l + '%'; el.style.top = t + '%'; }

  // ───────────────────────── tokens ─────────────────────────
  function ensureTokens() {
    players().forEach((p) => {
      if (tokens[p.id]) return;
      const el = document.createElement('div');
      el.className = 'token';
      el.style.background = p.color;
      el.innerHTML = `<span>${p.emoji || '🙂'}</span><span class="tname">${esc(p.name)}</span>`;
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
    Object.values(groups).forEach((ids) => {
      ids.forEach((id, i) => {
        const el = tokens[id];
        if (ids.length === 1) { el.style.setProperty('--ox', '0px'); el.style.setProperty('--oy', '0px'); return; }
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
  addEventListener('resize', () => { players().forEach((p) => tokens[p.id] && place(tokens[p.id], shown[p.id])); });
  addEventListener('orientationchange', () => setTimeout(() => { players().forEach((p) => tokens[p.id] && place(tokens[p.id], shown[p.id])); }, 250));

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
  }

  // ───────────────────────── the die ─────────────────────────
  const FACE = { 1: [0, 0], 2: [-90, 0], 3: [0, -90], 4: [0, 90], 5: [90, 0], 6: [0, 180] };
  let dieTimer = null;
  function spinDie() { CUBE.style.transition = 'none'; CUBE.classList.add('spinning'); }
  function settleDie(v) {
    CUBE.classList.remove('spinning');
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
    try {
      const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': C.csrf },
        body: JSON.stringify(body),
      });
      const data = await r.json().catch(() => ({}));
      if (r.status === 419) { // the page's session token expired — a refresh gets a fresh one (party links and online seats survive it)
        toast('🔄 Refreshing…', 1500);
        setTimeout(() => location.reload(), 900);
        return null;
      }
      if (!r.ok) {
        if (data.error) toast('⚠️ ' + esc(data.error), 3400);
        else toast(r.status === 429 ? '⏳ Slow down a little…' : '⚠️ Something went wrong — try again.', 3000);
        if (data.room) ingest(data);
        return null;
      }
      ingest(data);
      return data;
    } catch (_) {
      toast('⚠️ Connection problem — try again.', 3000);
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
  async function play(a) {
    switch (a.type) {
      case 'start': toast('🎲 Game on!'); snd('notify'); await sleep(450); break;

      case 'roll': {
        spinDie();
        snd('roll');
        await sleep(620);
        settleDie(a.roll);
        await sleep(560);
        toast(`${esc(P(a.player_id).emoji)} <b>${esc(P(a.player_id).name)}</b> rolled a <b>${a.roll}</b>`, 1800);
        await hop(a.player_id, a.hop_path || []);
        if (a.overshoot) { toast(`Needed exactly ${a.overshoot.needed} — bounced back to ${a.overshoot.bounced_to}`, 2400); await sleep(500); }
        await sleep(280);
        break;
      }

      case 'reroll':
        toast(`🎲 <b>${esc(P(a.player_id).name)}</b> used a Reroll: ${a.from_roll} → ${a.new_roll}`, 2200);
        snd('boostArm');
        break;

      case 'move': {
        const snake = a.via === 'snake_head';
        toast(snake ? `🐍 Snake! ${esc(P(a.player_id).name)} slides to ${a.to}` : `🪜 Ladder! ${esc(P(a.player_id).name)} climbs to ${a.to}`, 2200);
        snd(snake ? 'snake' : 'ladder');
        await sleep(350);
        await glide(a.player_id, a.to);
        await sleep(250);
        break;
      }

      case 'card': await cardOverlay(a); break;

      case 'shield': {
        const t = tokens[a.player_id];
        t && t.classList.add('shieldfx');
        snd('shieldUp');
        toast(`🛡️ <b>${esc(P(a.player_id).name)}</b> blocked it!`, 2200);
        await sleep(1500);
        t && t.classList.remove('shieldfx');
        break;
      }

      case 'duel': await duelOverlay(a); break;

      case 'swap': {
        toast(`🔀 <b>${esc(P(a.a_id).name)}</b> swapped places with <b>${esc(P(a.b_id).name)}</b>`, 2600);
        snd('mystery');
        await Promise.all([glide(a.a_id, a.a_pos), glide(a.b_id, a.b_pos)]);
        await sleep(250);
        break;
      }

      case 'power': {
        const name = esc(P(a.player_id).name);
        if (a.power) {
          const cfg = C.powersCfg[a.power];
          toast(`⚡ <b>${name}</b> grabbed a ${cfg.icon} <b>${cfg.label}</b>${a.spin ? ' (spin)' : ''}`, 2400);
          snd('boostArm');
        } else {
          toast(`⚡ ${name} found a power-up — but is already full!`, 2400);
        }
        await sleep(900);
        break;
      }

      case 'rule': await ruleOverlay(a); break;

      case 'win': {
        snd('win');
        confetti();
        toast(`🏆 <b>${esc(P(a.player_id).name)}</b> reached the finish!`, 3200);
        await sleep(1400);
        break;
      }

      case 'left': toast(`👋 ${esc(P(a.player_id).name)} left the game`, 2400); await sleep(350); break;
      case 'skip': toast(`⏰ ${esc(P(a.player_id).name)} ran out of time`, 2200); await sleep(250); break;
      case 'react': floater(a.player_id, a.emoji); snd('reaction'); break;
      default: break;
    }
  }

  /** Every device shows the card; party mode waits for a tap, online auto-closes. */
  function holdOverlay(id, html, { seconds = 7, buttonLabel = 'Got it' } = {}) {
    return new Promise((resolve) => {
      const auto = !C.isParty;
      show(id, `<div class="ov-card">${html}
        <div class="ov-btns"><button class="obtn" data-ok>${buttonLabel}</button></div>
        ${auto ? `<div class="autobar" style="animation-duration:${seconds}s"></div>` : ''}</div>`);
      let timer = null;
      const done = () => { clearTimeout(timer); hide(id); resolve(); };
      $(id).querySelector('[data-ok]').onclick = done;
      if (auto) timer = setTimeout(done, seconds * 1000);
    });
  }

  function cardOverlay(a) {
    const src = P(a.source_id);
    const targets = (a.target_ids || []).map(P);
    const spin = a.spin ? '🎰 Mystery spin · ' : '';
    let title;
    let who = '';
    switch (a.kind) {
      case 'self': title = `${spin}${esc(src.name)}`; who = chip(src); break;
      case 'pick': title = `${spin}${esc(src.name)} picked`; who = targets.map((t) => chip(t)).join(''); break;
      case 'everyone': title = `${spin}Everybody!`; who = ''; break;
      case 'duel': title = `${esc(targets[0]?.name || 'Someone')} lost the duel`; who = targets.map((t) => chip(t)).join(''); break;
      case 'fact': title = `${spin}Fun fact`; break;
      case 'blank': title = '🎰 Mystery spin'; break;
      default: title = 'Card';
    }
    snd(a.kind === 'fact' ? 'notify' : (a.kind === 'self' || a.kind === 'duel' || a.kind === 'pick') ? 'decisionPing' : 'mystery');
    return holdOverlay('ovCard', `
      <div class="ov-icon">${esc(a.icon || '🃏')}</div>
      <div class="ov-title">${title}</div>
      ${who ? `<div class="tgt-row">${who}</div>` : ''}
      <div class="ov-text">${esc(a.text)}</div>`);
  }

  function ruleOverlay(a) {
    snd('mystery');
    return holdOverlay('ovCard', `
      <div class="ov-icon">${esc(a.icon || '📜')}</div>
      <div class="ov-title">${a.spin ? '🎰 Mystery spin · ' : ''}New table rule</div>
      <div class="ov-text">${esc(a.text)}</div>
      <div class="ov-sub">Applies to everyone until the next rule square.</div>`, { seconds: 8 });
  }

  async function duelOverlay(a) {
    const A = P(a.a_id), B = P(a.b_id), loser = P(a.loser_id);
    snd('roll');
    show('ovCard', `<div class="ov-card">
      <div class="ov-icon">⚔️</div><div class="ov-title">Duel!</div>
      <div class="tgt-row">${chip(A)}<b style="align-self:center;font-size:1.4rem;">${a.a_roll}</b><span style="align-self:center;">vs</span><b style="align-self:center;font-size:1.4rem;">${a.b_roll}</b>${chip(B)}</div>
      <div class="ov-text">${esc(loser.name)} loses!</div></div>`);
    await sleep(2400);
    hide('ovCard');
  }

  // ───────────────────────── fx ─────────────────────────
  function floater(playerId, emoji) {
    const el = document.createElement('div');
    el.className = 'floater';
    el.textContent = emoji;
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

  // ───────────────────────── drawing the table ─────────────────────────
  function render() {
    S = latest;
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
    const lv = { 1: '😌 Chill', 2: '😎 Normal', 3: '🤪 Wild' }[r.intensity];
    let h = `<span class="chip">${C.mode.emoji} ${esc(C.mode.label)} · ${lv}</span>`;
    if (r.status === 'active') h += `<span class="chip">🎲 Round ${r.round}</span>`;
    if (r.join_code) h += `<button class="chip" type="button" id="codeChip" style="cursor:pointer;">🔑 ${esc(r.join_code)} ⧉</button>`;
    if (r.active_rule && r.status === 'active') h += `<button class="chip rule" type="button" id="ruleChip">📜 ${esc(r.active_rule)}</button>`;
    $('hudTl').innerHTML = h;
    const cc = $('codeChip'); if (cc) cc.onclick = shareInvite;
    const rc = $('ruleChip'); if (rc) rc.onclick = () => toast('📜 ' + esc(r.active_rule), 5000);
  }

  function renderPlayers() {
    const r = S.room;
    $('players').innerHTML = S.players.map((p) => {
      const cur = r.status === 'active' && p.id === r.current_player_id;
      const out = p.status === 'left';
      const won = p.status === 'won';
      const pw = (p.powers.shield ? `🛡️${p.powers.shield} ` : '') + (p.powers.reroll ? `🎲${p.powers.reroll}` : '');
      return `<div class="pl ${cur ? 'cur' : ''} ${out ? 'out' : ''}" style="${cur ? '' : ''}">
        <div class="av" style="background:${p.color}">${won ? '🏆' : (p.emoji || '🙂')}</div>
        <span class="nm">${esc(p.name)}${p.id === C.meId ? ' (you)' : ''}</span>
        <span class="ps">${p.position}/${r.tile_count}</span>
        <span class="pw">${pw}</span></div>`;
    }).join('');
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
      html = '🎉 Waiting to start…';
    } else if (r.status === 'completed') {
      html = '🏁 Game over';
    } else if (S.pending) {
      const who = P(S.pending.actor_id);
      const verb = { pick: 'choosing', shield: 'deciding on a Shield', reroll: 'deciding on a Reroll' }[S.pending.type] || 'deciding';
      html = iAmActor(S.pending.actor_id) && !C.isParty ? '👆 Your move!' : `⏳ <b>${esc(who.name)}</b> is ${verb}…`;
      if (!C.isParty) html += secs(pendingLeft);
    } else {
      const cur = P(r.current_player_id);
      mine = C.isParty || r.current_player_id === C.meId;
      if (C.isParty) { html = `<span style="color:${cur.color}">●</span> <b>${esc(cur.name)}</b>'s turn — roll!`; cls = 'mine'; }
      else if (mine) { html = '🎲 Your turn — roll!' + secs(turnLeft); cls = 'mine'; }
      else html = `⏳ <b>${esc(cur.name)}</b> is playing…` + secs(turnLeft);
    }
    b.className = 'turn-banner ' + cls;
    b.innerHTML = html;
    DIE.disabled = !(r.status === 'active' && !S.pending && mine && !rolling && !busy);
    if (S.pending && S.pending.type === 'reroll') settleDie(S.pending.roll);
    const glow = $('glow');
    if (S.pending && S.pending.type === 'reroll' && iAmActor(S.pending.actor_id)) {
      const { l, t } = xy(S.pending.preview_tile);
      glow.style.left = l + '%'; glow.style.top = t + '%'; glow.style.boxShadow = '';
      glow.classList.add('show');
    } else glow.classList.remove('show');
  }

  function renderPowers() {
    const r = S.room;
    const me = C.isParty ? P(r.current_player_id) : P(C.meId);
    if (r.status !== 'active' || !me || !me.powers) { $('powers').innerHTML = ''; return; }
    $('powers').innerHTML = Object.entries(C.powersCfg).map(([k, cfg]) =>
      `<span class="pwr ${me.powers[k] ? '' : 'empty'}" title="${esc(cfg.help)}">${cfg.icon} ${me.powers[k] || 0}</span>`).join('');
  }

  // ── prompts that wait on a player ──
  function renderPrompt() {
    const p = S.pending;
    if (!p || S.room.status !== 'active' || !iAmActor(p.actor_id)) { hide('ovPrompt'); return; }
    const actor = P(p.actor_id);
    const cfg = C.powersCfg;
    const left = !C.isParty && pendingLeft !== null ? `<div class="ov-sub" data-pend>⏱ <span data-secs>${Math.max(0, pendingLeft)}</span>s</div>` : '';

    if (p.type === 'reroll') {
      const k = p.preview_kind === 'finish' ? { label: 'the finish', icon: '🏁' } : kindInfo(p.preview_kind);
      const n = actor.powers.reroll || 0;
      show('ovPrompt', `<div class="ov-card">
        <div class="ov-icon">🎲</div>
        <div class="ov-title">${esc(actor.name)} rolled</div>
        <div class="ov-text" style="font-size:clamp(2rem,14cqh,4rem)">${p.roll}</div>
        <div class="ov-sub">That lands on square <b>${p.preview_tile}</b> — ${k.icon} ${esc(k.label)}. Use a Reroll?</div>
        <div class="ov-btns">
          <button class="obtn" data-act="use">${cfg.reroll.icon} Reroll (${n} left)</button>
          <button class="obtn alt" data-act="skip">Keep it</button></div>${left}</div>`);
      $('ovPrompt').querySelectorAll('[data-act]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.act })));
      return;
    }

    if (p.type === 'shield') {
      const src = P(p.source_id);
      const n = actor.powers.shield || 0;
      const own = p.source_id === p.actor_id; // a card the player landed on themselves
      show('ovPrompt', `<div class="ov-card">
        <div class="ov-icon">🛡️</div>
        <div class="ov-title">${own ? 'Your card' : (C.isParty ? `Pass the phone to ${esc(actor.name)}` : 'Incoming!')}</div>
        <div class="tgt-row">${chip(actor)}</div>
        <div class="ov-text">${esc(p.card.text)}</div>
        <div class="ov-sub">${own ? 'Spend a Shield to dodge it?' : `${esc(src.name)} landed this on you. Block it with a Shield?`}</div>
        <div class="ov-btns">
          <button class="obtn vio" data-act="use">${cfg.shield.icon} Block it (${n} left)</button>
          <button class="obtn alt" data-act="skip">Take it</button></div>${left}</div>`);
      $('ovPrompt').querySelectorAll('[data-act]').forEach((b) => (b.onclick = () => answer({ choice: b.dataset.act })));
      return;
    }

    if (p.type === 'pick') {
      const head = { pick: 'Choose a friend', duel: '⚔️ Choose your opponent', swap: '🔀 Choose who to swap with' }[p.sub] || 'Choose a player';
      const btns = p.eligible.map((id) => {
        const t = P(id);
        return `<button class="tgt" data-id="${id}"><span class="av" style="background:${t.color}">${t.emoji || '🙂'}</span>${esc(t.name)} <span style="color:#9ca3af;font-weight:700;">· ${t.position}</span></button>`;
      }).join('');
      show('ovPrompt', `<div class="ov-card wide">
        <div class="ov-icon">${esc(p.card.icon || '👉')}</div>
        <div class="ov-title">${esc(actor.name)} — ${head}</div>
        <div class="ov-text">${esc(p.card.text)}</div>
        <div class="tgt-row">${btns}</div>${left}</div>`);
      $('ovPrompt').querySelectorAll('[data-id]').forEach((b) => (b.onclick = () => answer({ choice: 'target', target_id: +b.dataset.id })));
    }
  }

  let answering = false;
  async function answer(body) {
    if (answering) return;
    answering = true;
    $('ovPrompt').querySelectorAll('button').forEach((b) => (b.disabled = true));
    snd('toggle');
    const res = await post(C.urls.decide, body);
    answering = false;
    if (!res) renderPrompt(); // failed — let them try again
  }

  // ── lobby (online, before the host starts) ──
  function renderLobby() {
    const r = S.room;
    if (r.status !== 'open') { hide('ovLobby'); return; }
    const active = S.players.filter((p) => p.status === 'active');
    const iHost = r.host_player_id === C.meId;
    show('ovLobby', `<div class="ov-card wide">
      <div class="ov-icon">🎉</div>
      <div class="ov-title">Room code</div>
      <div class="code-big">${esc(r.join_code)}</div>
      <div class="ov-sub">Friends open <b>${esc(location.host)}</b> → <b>Join a room</b>, or tap Share.</div>
      <div class="tgt-row">${active.map((p) => chip(p, '') + '').join('')}</div>
      <div class="ov-sub">${active.length} / ${r.max_players} players ${iHost ? '' : '· waiting for the host to start'}</div>
      <div class="ov-btns">
        <button class="obtn vio" id="lobbyShare">📨 Share invite</button>
        ${iHost ? `<button class="obtn" id="lobbyStart" ${active.length < 2 ? 'disabled' : ''}>▶ Start game</button>` : ''}
        <button class="obtn alt" id="lobbyLeave">Leave</button>
      </div></div>`);
    $('lobbyShare').onclick = shareInvite;
    $('lobbyLeave').onclick = () => confirmLeave(C.meId);
    const st = $('lobbyStart');
    if (st) st.onclick = async () => { st.disabled = true; snd('toggle'); await post(C.urls.start); };
  }

  // ── finished ──
  function renderWin() {
    const r = S.room;
    if (r.status !== 'completed') { hide('ovWin'); return; }
    const rc = S.recap || { ranking: [], awards: [] };
    const winner = P(r.winner_player_id);
    const rankHtml = rc.ranking.map((p, i) =>
      `<div class="rank"><span class="n">${i + 1}</span><span style="width:1.4em;text-align:center;">${p.status === 'won' ? '🏆' : (p.emoji || '')}</span><span style="flex:1;">${esc(p.name)}</span><span style="color:#9ca3af;">${p.status === 'left' ? 'left' : 'sq ' + p.position}</span></div>`).join('');
    const awardsHtml = rc.awards.map((a) =>
      `<div class="award"><span style="font-size:1.2em;">${a.icon}</span><span><b>${esc(a.name)}</b> · ${esc(a.title)} (${a.value})</span></div>`).join('');
    show('ovWin', `<div class="ov-card wide">
      <div class="ov-icon">🏆</div>
      <div class="ov-title">${r.winner_player_id ? 'We have a winner' : 'Game over'}</div>
      <div class="ov-text">${r.winner_player_id ? esc(winner.name) + ' wins!' : 'That\'s a wrap.'}</div>
      <div class="ov-sub">${rc.rounds || r.round} rounds played</div>
      <div style="text-align:left;margin:.4rem 0;">${rankHtml}</div>
      ${awardsHtml ? `<div style="text-align:left;margin:.5rem 0;border-top:1px solid rgba(255,255,255,.1);padding-top:.4rem;">${awardsHtml}</div>` : ''}
      <div class="ov-btns">
        <button class="obtn" id="winAgain">${r.rematch_token ? '🔁 Join rematch' : '🔁 Play again'}</button>
        <a class="obtn alt" href="${C.urls.home}" style="text-decoration:none;display:inline-block;">Home</a>
      </div>
      <div class="ov-sub" style="margin-top:.6rem;">🍹 Thanks for playing — drink responsibly and never drive after drinking.</div></div>`);
    $('winAgain').onclick = async () => {
      if (r.rematch_token) { location.href = C.urls.base + '/' + r.rematch_token; return; }
      $('winAgain').disabled = true;
      const res = await fetch(C.urls.rematch, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': C.csrf } }).then((x) => x.json()).catch(() => ({}));
      if (res.redirect) location.href = res.redirect; else { $('winAgain').disabled = false; toast('⚠️ ' + esc(res.error || 'Could not start a rematch')); }
    };
    // Others at an online table follow the host into the rematch automatically.
    if (r.rematch_token && !C.isParty && !rematchRedirected) {
      rematchRedirected = true;
      toast('🔁 Rematch is ready — taking you there…', 2400);
      setTimeout(() => { location.href = C.urls.base + '/' + r.rematch_token; }, 1800);
    }
  }

  // ───────────────────────── menu / leave / share ─────────────────────────
  function openMenu() {
    const r = latest.room;
    const kinds = Object.entries(C.kinds).map(([k, v]) => `<div class="htp"><span style="font-size:1.1em;">${v.icon}</span><span><b>${esc(v.label)}</b> — ${esc(v.help)}</span></div>`).join('');
    const pw = Object.values(C.powersCfg).map((v) => `<div class="htp"><span style="font-size:1.1em;">${v.icon}</span><span><b>${esc(v.label)}</b> — ${esc(v.help)}</span></div>`).join('');
    const roster = latest.players.map((p) => {
      const can = p.status === 'active' && (C.isParty || p.id === C.meId);
      return `<div class="rank">${chip(p)}<span style="flex:1;"></span><span style="color:#9ca3af;">${p.status === 'left' ? 'left' : p.position + '/' + r.tile_count}</span>
        ${can ? `<button class="obtn alt" style="padding:.25rem .6rem;font-size:.7rem;" data-out="${p.id}">${C.isParty ? 'Remove' : "I'm out"}</button>` : ''}</div>`;
    }).join('');
    show('ovMenu', `<div class="ov-card wide" style="text-align:left;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
        <b style="font-size:1.05rem;">${esc(C.boardName)}</b><button class="obtn alt" id="menuClose" style="padding:.3rem .8rem;">✕ Close</button></div>
      <div class="ov-title" style="text-align:left;">How to play</div>
      <div class="htp"><span>🎲</span><span>Roll to move. <b>First to the finish square wins.</b> Roll too high and you bounce back.</span></div>
      ${kinds}${pw}
      ${r.active_rule ? `<div class="htp"><span>📜</span><span><b>Table rule now:</b> ${esc(r.active_rule)}</span></div>` : ''}
      <div class="ov-title" style="text-align:left;">Players</div>${roster}
      ${C.isParty ? '<div class="ov-sub" style="text-align:left;">Someone had enough? Remove them — the game carries on without them.</div>' : ''}
      <div class="ov-sub" style="text-align:left;margin-top:.6rem;">🍹 Any drink can be swapped for water. Never drive after drinking.</div>
      <div class="ov-btns" style="justify-content:flex-start;"><a class="obtn alt" href="${C.urls.home}" style="text-decoration:none;">🏠 Leave to home</a></div></div>`);
    $('menuClose').onclick = () => hide('ovMenu');
    $('ovMenu').querySelectorAll('[data-out]').forEach((b) => (b.onclick = () => confirmLeave(+b.dataset.out)));
  }

  function confirmLeave(id) {
    const p = P(id);
    const self = !C.isParty && id === C.meId;
    show('ovConfirm', `<div class="ov-card">
      <div class="ov-icon">👋</div>
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
    const text = `Join my Snakes & Liquor game! Code: ${code}`;
    try {
      if (navigator.share) { await navigator.share({ title: 'Snakes & Liquor', text, url }); return; }
    } catch (_) { return; }
    try { await navigator.clipboard.writeText(`${text}\n${url}`); toast('📋 Invite copied — paste it to your friends'); }
    catch (_) { toast(`Code: <b>${esc(code)}</b>`, 5000); }
  }

  // ───────────────────────── controls ─────────────────────────
  DIE.addEventListener('click', async () => {
    if (DIE.disabled || rolling || busy) return;
    rolling = true;
    DIE.disabled = true;
    try { ArcadeSound.unlock(); } catch (_) {}
    spinDie();
    const res = await post(C.urls.roll);
    if (!res) { rolling = false; CUBE.classList.remove('spinning'); render(); }
    // On success ingest() → pump() plays the roll and finishes with render().
  });
  $('menuBtn').onclick = openMenu;

  const soundOn = (() => { try { return localStorage.getItem('snl_sound') !== '0'; } catch (_) { return true; } })();
  function setSound(on) {
    try { ArcadeSound.toggle(on); } catch (_) {}
    $('soundBtn').textContent = on ? '🔊' : '🔇';
    try { localStorage.setItem('snl_sound', on ? '1' : '0'); } catch (_) {}
  }
  setSound(soundOn);
  $('soundBtn').onclick = () => setSound(!ArcadeSound.on);

  const rt = $('reactToggle');
  if (rt) {
    rt.onclick = () => $('reactBar').classList.toggle('show');
    $('reactBar').querySelectorAll('button').forEach((b) => (b.onclick = () => { post(C.urls.react, { emoji: b.dataset.emoji }); }));
  }

  // ───────────────────────── polling & clocks ─────────────────────────
  async function poll() {
    if (!C.isParty) {
      try {
        const r = await fetch(C.urls.state, { headers: { Accept: 'application/json' } });
        if (r.ok) ingest(await r.json());
      } catch (_) { /* a missed poll is fine — the next one catches up from the log */ }
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
  if (!C.isParty) setTimeout(poll, 1400);
})();
