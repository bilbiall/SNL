/* ═══════════════════════════════════════════════════════════════
   SNL LOCAL — runs a whole party game inside the browser/app, saved on the device after every move.
   Works with no network at all once the game pack has been downloaded once (the service worker keeps it).
   • SNLLocal.boot(C)          fills window.SNL for the table page from the saved game (used by /play/local)
   • SNLLocal.act(name, body)  roll / decide / leave / rematch → { state } or { error, state }
   • SNLLocal.create(setup)    new game from the setup form
   • SNLLocal.carryOn(state)   turns an online room's last snapshot into a local game ("carry on on this phone")
   • SNLLocal.summary()        a saved, unfinished game (for the "Continue" buttons)
   ═══════════════════════════════════════════════════════════════ */
const SNLLocal = (() => {
  'use strict';
  const KEY = 'snl_game_v1';
  const PACK_KEY = 'snl_pack_v1';
  let pack = null;
  let game = null;
  let packPromise = null;

  // inside the Android app the pack is a plain file next to the pages
  const packUrl = () => (window.SNL && window.SNL.packUrl) || ((window.Capacitor || window.SNLDesktop || (location.hostname === 'localhost' && !location.port)) ? '/pack.json' : '/offline/pack.json');

  /** The game pack (rules config, boards, decks, words). Cache first via the service worker; a copy in localStorage is the safety net. */
  function loadPack() {
    if (packPromise) return packPromise;
    packPromise = fetch(packUrl())
      .then((r) => { if (!r.ok) throw new Error('pack'); return r.json(); })
      .then((p) => { pack = p; try { localStorage.setItem(PACK_KEY, JSON.stringify(p)); } catch (_) {} return p; })
      .catch(() => {
        try { const s = localStorage.getItem(PACK_KEY); if (s) { pack = JSON.parse(s); return pack; } } catch (_) {}
        return null;
      });
    return packPromise;
  }

  function read() {
    try { const s = localStorage.getItem(KEY); return s ? JSON.parse(s) : null; } catch (_) { return null; }
  }
  function save() {
    if (!game) return;
    game.savedAt = Date.now();
    try { localStorage.setItem(KEY, JSON.stringify(game)); } catch (_) { /* storage full — the game still runs in memory */ }
  }
  function clear() { game = null; try { localStorage.removeItem(KEY); } catch (_) {} }

  /** A saved game that is still being played — for "Continue" buttons. */
  function summary() {
    const g = read();
    if (!g || !g.room || g.room.status !== 'active') return null;
    const active = g.players.filter((p) => p.status === 'active');
    return { players: active.length, names: active.map((p) => p.name), round: g.room.round, mode: g.room.content_mode, savedAt: g.savedAt || 0 };
  }

  const stateNow = () => SNLEngine.state(pack, game);

  /** Runs one action atomically: any failure restores the game exactly as it was. */
  function act(name, body) {
    body = body || {};
    const before = JSON.stringify(game);
    try {
      switch (name) {
        case 'roll': SNLEngine.roll(pack, game); break;
        case 'decide': SNLEngine.decide(pack, game, body.choice, body.target_id ? +body.target_id : null); break;
        case 'leave': SNLEngine.leave(pack, game, +body.player_id); break;
        case 'addplayer': SNLEngine.addPlayer(pack, game, { name: body.name, avatar: body.avatar }); break;
        case 'react': case 'state': case 'start': return { state: stateNow() };
        case 'rematch': game = SNLEngine.rematch(pack, game); save(); return { redirect: 'reload', state: stateNow() };
        default: return { error: 'Unknown action.', state: stateNow() };
      }
      save();
      return { state: stateNow() };
    } catch (e) {
      game = JSON.parse(before);
      if (e && (e.name === 'GameError' || (SNLEngine.GameError && e instanceof SNLEngine.GameError))) return { error: e.message, state: stateNow() };
      if (window.console) console.error(e);
      return { error: 'Something went wrong — try again.', state: stateNow() };
    }
  }

  async function create(setup) {
    const p = await loadPack();
    if (!p) throw new Error('The game files are not on this device yet — connect once to download them.');
    game = SNLEngine.create(p, setup);
    save();
    return game;
  }

  async function carryOn(onlineState) {
    const p = await loadPack();
    if (!p) throw new Error('The game files are not on this device yet.');
    game = SNLEngine.fromOnline(p, onlineState);
    save();
  }

  const positionsFor = (board) => {
    const out = {};
    board.tiles.forEach((t) => {
      out[t.number] = {
        left: t.pos_left == null ? 50 : t.pos_left, top: t.pos_top == null ? 50 : t.pos_top,
        left_m: t.pos_left_mobile == null ? (t.pos_left == null ? 50 : t.pos_left) : t.pos_left_mobile,
        top_m: t.pos_top_mobile == null ? (t.pos_top == null ? 50 : t.pos_top) : t.pos_top_mobile,
      };
    });
    out[0] = Object.assign({}, out[1] || { left: 8, top: 90, left_m: 8, top_m: 90 });
    if (board.start_zone) out[0].zone = board.start_zone;   // players wait spread out inside the start zone
    return out;
  };
  const kindsOnBoard = (board, cfg) => {
    const present = new Set(board.tiles.map((t) => t.kind));
    const out = {};
    Object.keys(cfg.tile_kinds).forEach((key) => {
      const k = cfg.tile_kinds[key];
      if (key !== 'blank' && present.has(key)) out[key] = { label: k.label, icon: k.icon, help: k.help };
    });
    return out;
  };

  function fatal(msg) {
    document.body.innerHTML = '<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;text-align:center;background:#0b0a16;color:#fff;font-family:Figtree,system-ui,sans-serif"><div><h2 style="margin:0 0 .5rem">' + msg + '</h2><a href="/" style="color:#fbbf24;font-weight:800">Back to home</a></div></div>';
  }

  /** Prepares window.SNL for snl-play.js from the saved game. Resolves false when it redirected / failed. */
  async function boot(C) {
    const p = await loadPack();
    if (!p) { fatal('The game files are not downloaded yet — connect once, then you can play offline.'); return false; }
    game = read();
    if (!game || !game.room) { location.replace(C.urls.setup); return false; }
    const board = p.boards.find((b) => b.id === game.room.board_id) || p.boards[0];
    if (!board) { fatal('No board is available.'); return false; }
    C.positions = positionsFor(board);
    C.state = stateNow();
    C.mode = p.config.modes[game.room.content_mode];
    C.kinds = kindsOnBoard(board, p.config);
    C.boardName = board.name;
    C.timing = p.config.timing || C.timing;
    document.documentElement.style.setProperty('--bw', board.width);
    document.documentElement.style.setProperty('--bh', board.height);
    const img = /^https?:/.test(board.image) ? board.image : board.image;
    const el = document.getElementById('boardImg'); if (el) { el.src = img; el.alt = board.name + ' board'; }
    const bg = document.getElementById('ambientBg'); if (bg) bg.style.backgroundImage = "url('" + img + "')";
    window.addEventListener('pagehide', save);
    return true;
  }

  return { boot, act, create, carryOn, summary, clear, save, loadPack, get pack() { return pack; }, hasEngine: () => typeof SNLEngine !== 'undefined' };
})();
