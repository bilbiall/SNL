/* ═══════════════════════════════════════════════════════════════════════════
   Snakes & Liquor — offline table engine (party mode, runs entirely in the browser).

   A faithful JavaScript port of app/Services/GameEngine.php. Same turn pipeline, same prompts,
   same action log, same state() shape — so the table client (snl-play.js) can't tell the
   difference between a server game and a local one.

   Pack:  JSON from `php artisan snl:pack` (rules config, boards + squares, card decks, word lists, avatars).
   Game:  a plain JSON object (see create()). Every public call works on a copy and only commits to the
          game you pass in if it succeeds — an error never leaves a half-played turn behind.

   UMD: window.SNLEngine in browsers, module.exports in node.
   ═══════════════════════════════════════════════════════════════════════════ */
(function (root, factory) {
  if (typeof module === 'object' && module && module.exports) module.exports = factory();
  else root.SNLEngine = factory();
})(typeof globalThis !== 'undefined' ? globalThis : (typeof self !== 'undefined' ? self : this), function () {
  'use strict';

  var LOG_KEEP = 24;

  var FALLBACK = {
    drinks: {
      self: 'Take a sip.', pick: 'Pick someone to take a sip.', everyone: 'Everybody take a sip.',
      duel: 'The loser takes a sip.', fact: 'Share a fun fact with the table.', rule: 'No pointing for the rest of the game.',
      swap: 'Swap places with a player of your choice!', neutral: 'Free square — you are safe. Sip of water?', heart: 'Give {other} a genuine compliment.',
      quickfire: 'Name five fruits — 10 seconds!', rollagain: 'Lucky break — roll again!', gift: 'Lucky find — a free shield!', groupgame: 'Everybody name a cocktail, round the table. First to stall takes a sip.',
    },
    family: {
      self: 'Do your best victory dance!', pick: 'Pick someone to do ten star jumps.', everyone: 'Everybody cheer as loud as you can!',
      duel: 'The loser does a silly walk.', fact: 'Share a fun fact with the table.', rule: 'Everyone must speak in a whisper until the next rule.',
      swap: 'Swap places with a player of your choice!', neutral: 'Free square — you are safe. Take a breath!', heart: 'Give {other} a genuine compliment.',
      quickfire: 'Name five fruits — 10 seconds!', rollagain: 'Lucky break — roll again!', gift: 'Lucky find — a free shield!', groupgame: 'Everybody name an animal, round the table. First to stall does a silly dance.',
    },
  };

  var AWARDS = [
    { key: 'hits', icon: 'skull', title: 'Took the most hits' },
    { key: 'dodges', icon: 'shield', title: 'Master dodger' },
    { key: 'picks', icon: 'pick', title: 'Chief mischief-maker' },
    { key: 'snakes', icon: 'snake', title: 'Snake magnet' },
    { key: 'ladders', icon: 'ladder', title: 'Lucky climber' },
    { key: 'duels_won', icon: 'duel', title: 'Duel champion' },
    { key: 'facts_right', icon: 'fact', title: 'Know-it-all' },
    { key: 'bluffs_won', icon: 'jester', title: 'Best poker face' },
    { key: 'bluffs_caught', icon: 'search', title: 'Lie detector' },
    { key: 'spots_nailed', icon: 'mic', title: 'Crowd favourite' },
  ];

  function GameError(message) {
    this.name = 'GameError';
    this.message = message;
    if (Error.captureStackTrace) Error.captureStackTrace(this, GameError);
    else this.stack = (new Error(message)).stack;
  }
  GameError.prototype = Object.create(Error.prototype);
  GameError.prototype.constructor = GameError;

  // ───────────────────────────── randomness ─────────────────────────────
  var _rand = null;
  function rnd() {
    if (_rand) return _rand();
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
      var a = new Uint32Array(1); crypto.getRandomValues(a); return a[0] / 4294967296;
    }
    return Math.random();
  }
  function randInt(a, b) { return a + Math.floor(rnd() * (b - a + 1)); }
  function arrayRand(arr) { return arr[Math.floor(rnd() * arr.length)]; }
  function setRandom(fn) { _rand = fn || null; }

  // ───────────────────────────── small helpers ─────────────────────────────
  function clone(o) { return JSON.parse(JSON.stringify(o)); }

  /** Shuffle bag: work through a deck before anything repeats; Spicy rooms lean towards Spicy cards. state lives in game.deck_state. */
  function bagPick(g, key, pool, intensity, bias) {
    if (!pool.length) return null;
    var st = g.deck_state || (g.deck_state = {}), used = (st.used || (st.used = {}))[key] || [];
    var fresh = pool.filter(function (p) { return used.indexOf(p.id) === -1; });
    if (!fresh.length) {                                   // lap finished — start again, but not on the last few
      used = used.slice(-Math.min(4, Math.floor(pool.length / 2)));
      fresh = pool.filter(function (p) { return used.indexOf(p.id) === -1; });
      if (!fresh.length) { fresh = pool.slice(); used = []; }
    }
    var exact = fresh.filter(function (p) { return p.intensity === intensity; });
    var from = (exact.length && rnd() < (bias == null ? 0.65 : bias)) ? exact : fresh;
    var pick = from[Math.floor(rnd() * from.length)];
    used.push(pick.id);
    st.used[key] = used.slice(-600);
    return pick;
  }
  var FALLBACK_POOL = {
    quickfire: ['Name five animals — 10 seconds!', 'Name five countries — go!', 'Name five songs — quick!', 'Name five foods that are red.', 'Name five things in a bathroom.', 'Name five singers — fast!', 'Name five things that fly.', 'Name five brands of shoes.'],
    rollagain: ['Lucky break — roll again!', 'Bonus turn — roll again!', 'The dice want another go!', 'Go on, roll again!'],
    gift: ['A little gift from the board!', 'You found something shiny!', 'Lucky find — enjoy it!', 'The board slips you a present.'],
    groupgame: ['Everybody name a movie, round the table — first to stall pays.', 'Everybody name a city, round the table — no repeats.', 'Everybody name a song — first to stall pays.', 'Everybody name a snack, round the table.'],
    neutral: ['Free square — breathe.', 'Nothing happens. Enjoy it.', 'Safe square — sip of water?', 'A quiet moment on the board.', 'Free square — stretch your legs.', 'The board lets you off this time.'],
  };
  function isPlainEmpty(v) { return Array.isArray(v) && v.length === 0; }
  function obj(v) { return (v && !isPlainEmpty(v)) ? v : {}; }
  function cp(s) { return Array.from(String(s)); }
  function substr(s, start, len) { return cp(s).slice(start, start + len).join(''); }
  function rangeUp(a, b) { var out = []; for (var i = a; i <= b; i++) out.push(i); return out; }
  function rangeDown(a, b) { var out = []; for (var i = a; i >= b; i--) out.push(i); return out; }

  // ───────────────────────────── avatars (App\Support\Avatars) ─────────────────────────────
  function allLooks(pack) {
    var out = [];
    (pack.avatars.collections || []).forEach(function (c) { c.looks.forEach(function (l) { out.push(l); }); });
    return out;
  }
  function avatarValid(pack, key) {
    if (!key) return false;
    var m = /^([a-z]\d+)-(\d+)$/.exec(String(key));
    if (!m) return false;
    var skin = parseInt(m[2], 10);
    return allLooks(pack).some(function (l) { return l.id === m[1]; })
      && pack.avatars.skins.some(function (s) { return s.id === skin; });
  }
  function avatarDefault(pack, order) {
    var retro = pack.avatars.collections[0].looks;
    var women = retro.filter(function (l) { return l.gender === 'f'; });
    var men = retro.filter(function (l) { return l.gender === 'm'; });
    var pool = order % 2 === 0 ? men : women;
    var look = pool[Math.floor(order / 2) % pool.length].id;
    var skins = pack.avatars.skins;
    return look + '-' + skins[(order * 3 + 1) % skins.length].id;
  }

  // ───────────────────────────── the engine context ─────────────────────────────
  // One Ctx per public call; it holds the working copy of the game plus the per-call scratch state.
  function Ctx(pack, game) {
    this.pack = pack;
    this.cfg = pack.config;
    this.g = game;
    this.turnActor = null;
    this.fillTarget = null;
    this.extraTurn = false;
  }
  var P = Ctx.prototype;

  P.room = function () { return this.g.room; };
  P.board = function () {
    var id = this.g.room.board_id;
    for (var i = 0; i < this.pack.boards.length; i++) if (this.pack.boards[i].id === id) return this.pack.boards[i];
    throw new GameError('That board is not available.');
  };
  P.tile = function (number) {
    if (!this._tiles) {
      this._tiles = {};
      var t = this.board().tiles;
      for (var i = 0; i < t.length; i++) this._tiles[t[i].number] = t[i];
    }
    return this._tiles[number] || null;
  };
  P.player = function (id) {
    var ps = this.g.players;
    for (var i = 0; i < ps.length; i++) if (ps[i].id === id) return ps[i];
    return null;
  };
  P.activePlayers = function () {
    return this.g.players.filter(function (p) { return p.status === 'active'; })
      .sort(function (a, b) { return a.turn_order - b.turn_order; });
  };
  P.powers = function () { return obj(this.cfg.powers); };

  // Player helpers (Player::power/addPower/bump/stat)
  P.power = function (p, key) { return parseInt((p.powers || {})[key] || 0, 10) || 0; };
  P.addPower = function (p, key, delta) {
    var def = this.powers()[key];
    var max = def && def.max != null ? def.max : 3;
    var cur = parseInt((p.powers || {})[key] || 0, 10) || 0;
    p.powers = p.powers || {};
    p.powers[key] = Math.max(0, Math.min(max, cur + delta));
  };
  P.bump = function (p, stat, by) {
    p.stats = p.stats || {};
    p.stats[stat] = (parseInt(p.stats[stat] || 0, 10) || 0) + (by == null ? 1 : by);
  };
  P.stat = function (p, key) { return parseInt((p.stats || {})[key] || 0, 10) || 0; };

  // Room::log
  P.log = function (entry) {
    var r = this.g.room;
    r.event_seq = (parseInt(r.event_seq, 10) || 0) + 1;
    var e = Object.assign({ seq: r.event_seq }, entry);
    this.g.actions.push(e);
    if (this.g.actions.length > LOG_KEEP) this.g.actions = this.g.actions.slice(-LOG_KEEP);
    return e;
  };

  P.assertPlaying = function () {
    if (this.g.room.status === 'completed') throw new GameError('This game is over.');
    if (this.g.room.status !== 'active') throw new GameError('The game has not started yet.');
  };

  // ───────────────────────────── cards ─────────────────────────────
  // Card::draw — a random live card of this kind for the room's mode (or "all" modes), never wilder than the
  // room's intensity; falls back to ignoring intensity before giving up. Pick cards written with {target} are the wheel's deck.
  P.draw = function (kind, random) {
    var room = this.g.room, mode = room.content_mode, boardId = room.board_id, intensity = room.intensity;
    var base = this.pack.cards.filter(function (c) {
      if (c.kind !== kind) return false;
      if (c.mode !== 'all' && c.mode !== mode) return false;
      if (c.board_id != null && c.board_id !== boardId) return false;
      if (kind === 'pick') {
        var has = String(c.text).indexOf('{target}') !== -1;
        if (random ? !has : has) return false;
      }
      return true;
    });
    var ok = base.filter(function (c) { return c.intensity <= intensity; });
    var list = ok.length ? ok : base;
    if (!list.length) return null;
    var byId = {};
    var pool = list.map(function (c) { var id = c.kind + '|' + c.text; byId[id] = c; return { id: id, intensity: c.intensity }; });
    var picked = bagPick(this.g, kind + (random ? '#r' : ''), pool, intensity, 0.65);
    return picked ? byId[picked.id] : null;
  };

  // WordList::pick
  P.pickWord = function (slot) {
    var room = this.g.room, mode = room.content_mode, intensity = room.intensity;
    var base = this.pack.words.filter(function (w) { return w.slot === slot && (w.mode === 'all' || w.mode === mode); });
    var ok = base.filter(function (w) { return w.intensity <= intensity; });
    var list = ok.length ? ok : base;
    if (!list.length) return null;
    var pool = list.map(function (w) { return { id: w.text, intensity: w.intensity }; });
    var picked = bagPick(this.g, 'w:' + slot, pool, intensity, 0.5);
    return picked ? picked.id : null;
  };

  P.fill = function (text) {
    var self = this;
    if (String(text).indexOf('{') === -1) return text;
    return String(text).replace(/\{([a-z_]+)\}/g, function (m0, slot) {
      if (slot === 'target') return self.fillTarget ? self.fillTarget.name : 'someone';
      if (slot === 'you') return self.turnActor ? self.turnActor.name : 'you';
      if (slot === 'other') {
        var meId = self.turnActor ? self.turnActor.id : null;
        var others = self.activePlayers().filter(function (p) { return p.id !== meId; });
        return others.length ? arrayRand(others).name : 'someone';
      }
      var w = self.pickWord(slot);
      return w == null ? 'something' : w;
    });
  };

  P.cardFor = function (kind, tile, spin, preDrawn) {
    var cfg = this.cfg, room = this.g.room;
    var icon = (tile && tile.icon)
      || (cfg.event_icons && cfg.event_icons[kind])
      || (cfg.mode_icons && cfg.mode_icons[room.content_mode] && cfg.mode_icons[room.content_mode][kind])
      || (cfg.tile_kinds[kind] && cfg.tile_kinds[kind].icon)
      || 'cards';
    var text = preDrawn ? preDrawn.text : (tile ? tile.label : null);
    var drawn = preDrawn || null;
    if (!preDrawn && (!text || spin)) {
      // A spin ignores the tile's own fixed text — it's a different effect than the tile normally shows.
      drawn = this.draw(kind, null);
      var fp = FALLBACK_POOL[kind];
      var fb = fp ? fp[Math.floor(rnd() * fp.length)] : ((FALLBACK[room.content_mode] && FALLBACK[room.content_mode][kind]) || FALLBACK.family[kind] || 'Enjoy the moment!');
      text = drawn && drawn.text != null ? drawn.text : fb;
    }
    return {
      kind: kind, text: this.fill(text), icon: icon, spin: !!spin,
      // Only meaningful for True-or-False / Bluff / Spotlight cards — never sent to clients as-is (see publicCard()).
      answer: drawn ? (drawn.answer == null ? null : drawn.answer) : null,
      explain: drawn ? (drawn.explain == null ? null : drawn.explain) : null,
      penalty: parseInt(drawn && drawn.penalty ? drawn.penalty : 1, 10) || 1,
      timer: drawn ? (drawn.timer == null ? null : drawn.timer) : null,
      intensity: drawn && drawn.intensity != null ? drawn.intensity : room.intensity,
    };
  };

  /** What clients may see of a card: never the answer or the explanation. */
  P.publicCard = function (card) {
    return { kind: card.kind, text: card.text, icon: card.icon, spin: card.spin || false, intensity: card.intensity != null ? card.intensity : this.g.room.intensity };
  };

  P.penaltyText = function (units) {
    var pen = this.cfg.penalties, room = this.g.room;
    var table = pen[room.content_mode] || pen.family;
    var level = table[room.intensity] || table[2];
    var u = Math.max(1, Math.min(2, units));
    return level[u] != null ? level[u] : level[1];
  };

  // ───────────────────────────── pending prompts ─────────────────────────────
  P.setPending = function (p) { this.g.pending = p; };
  P.clearPending = function () { this.g.pending = null; };

  // ───────────────────────────── hits ─────────────────────────────
  P.penalize = function (source, target, units, tile) {
    var room = this.g.room, mi = this.cfg.mode_icons || {};
    var icon = (mi[room.content_mode] && mi[room.content_mode].self) || 'self';
    this.hit(source, [target], { kind: 'self', icon: icon, text: this.penaltyText(units), spin: false, units: units }, tile);
  };

  /** A hit on a single player — pauses for their Shield if they hold one. */
  P.hit = function (source, targets, card, tile) {
    if (targets.length === 1 && this.power(targets[0], 'shield') > 0) {
      this.setPending({ type: 'shield', actor_id: targets[0].id, source_id: source.id, card: card, tile: tile });
      return;
    }
    this.commitHit(source, targets, card, tile);
  };

  P.commitHit = function (source, targets, card, tile) {
    var self = this;
    targets.forEach(function (t) {
      self.bump(t, 'hits');
      if (card.kind !== 'heart' && card.kind !== 'groupgame') self.bump(t, 'buzz', Math.max(1, parseInt(card.units || 1, 10) || 1));   // the Buzz Meter
    });
    if ((card.kind === 'pick' || card.kind === 'duel') && !(targets.length === 1 && targets[0].id === source.id)) this.bump(source, 'picks');
    this.log({
      type: 'card', kind: card.kind, text: card.text, icon: card.icon, spin: card.spin || false,
      intensity: card.intensity != null ? card.intensity : this.g.room.intensity, random: card.random || false,
      source_id: source.id, target_ids: targets.map(function (t) { return t.id; }), tile: tile == null ? null : tile,
    });
  };

  // ───────────────────────────── roll resolution ─────────────────────────────
  P.previewLanding = function (from, value) {
    var n = this.board().tile_count, to = from + value;
    if (to > n) to = n - (value - (n - from));
    if (to >= n) return [n, 'finish'];
    var t = this.tile(to);
    return [to, t ? t.kind : 'blank'];
  };

  P.roll = function () {
    var room = this.g.room;
    this.assertPlaying();
    if (this.g.pending) throw new GameError('Finish the current prompt first.');
    var actorId = room.current_player_id;
    var actor = this.player(actorId);
    if (!actor) throw new GameError("It's not your turn yet.");
    if (actor.status !== 'active') throw new GameError('You are no longer in this game.');

    var value = randInt(1, 6);
    var pv = this.previewLanding(actor.position, value);

    // Only offer a Reroll when the landing would hurt — keeps the flow quick. (Reroll is switched off in the config.)
    if (this.powers().reroll && this.power(actor, 'reroll') > 0 && ['self', 'duel', 'snake_head'].indexOf(pv[1]) !== -1) {
      this.setPending({ type: 'reroll', actor_id: actorId, roll: value, from: actor.position, preview_tile: pv[0], preview_kind: pv[1] });
      return;
    }
    this.resolveRoll(actor, value, actor.position);
  };

  P.resolveRoll = function (actor, value, from) {
    var room = this.g.room, n = this.board().tile_count, target = from + value, hop, overshoot = null;
    if (target > n) {
      var needed = n - from, bounced = n - (value - needed);
      hop = rangeUp(from + 1, n).concat(rangeDown(n - 1, bounced));
      overshoot = { needed: needed, bounced_to: bounced };
      actor.position = bounced;
    } else {
      hop = rangeUp(from + 1, target);
      actor.position = target;
    }
    this.bump(actor, 'rolls');
    this.log({ type: 'roll', player_id: actor.id, roll: value, from: from, hop_path: hop, to: actor.position, overshoot: overshoot });

    if (actor.position >= n) { this.win(actor); return; }

    this.resolveTile(actor, this.tile(actor.position), null, false);
    if (!this.g.pending && !this.extraTurn) this.endTurn();
  };

  P.resolveTile = function (actor, tile, forceKind, spin) {
    var room = this.g.room;
    var kind = forceKind || (tile ? tile.kind : 'blank');
    var number = tile ? tile.number : null;
    var card, eligible, timer;
    this.turnActor = actor;

    switch (kind) {
      case 'snake_head':
      case 'ladder_bottom':
        if (tile && (tile.kind === 'snake_head' || tile.kind === 'ladder_bottom') && tile.target_number) {
          this.bump(actor, kind === 'snake_head' ? 'snakes' : 'ladders');
          this.log({ type: 'move', player_id: actor.id, via: kind, from: actor.position, to: tile.target_number });
          actor.position = tile.target_number;
        }
        break;

      case 'self':
        card = this.publicCard(this.cardFor('self', tile, spin));
        this.hit(actor, [actor], card, number);
        break;

      case 'heart': // love ladder — a kind moment, never a penalty
        card = this.publicCard(this.cardFor('heart', tile, spin));
        this.commitHit(actor, [actor], card, number);
        break;

      case 'everyone':
        card = this.publicCard(this.cardFor('everyone', tile, spin));
        this.commitHit(actor, this.activePlayers(), card, number);
        break;

      case 'pick':
      case 'duel':
      case 'swap':
        if (kind === 'pick' && this.tryRandomPick(actor, tile, number, spin)) break;
        card = this.publicCard(this.cardFor(kind, tile, spin));
        eligible = this.activePlayers().filter(function (p) { return p.id !== actor.id; }).map(function (p) { return p.id; });
        if (!eligible.length) break;
        this.setPending({ type: 'pick', actor_id: actor.id, sub: kind, card: card, eligible: eligible, tile: number });
        break;

      case 'fact': // True or false: the game states a fact, the roller calls it
        card = this.cardFor('fact', tile, spin);
        if (card.answer === null) { // a plain read-aloud fact (no answer on the card) still works
          this.log(Object.assign({ type: 'card' }, this.publicCard(card), { source_id: actor.id, target_ids: [], tile: number }));
          break;
        }
        this.setPending({
          type: 'tf', actor_id: actor.id, card: this.publicCard(card),
          answer: !!card.answer, explain: card.explain, penalty: card.penalty, tile: number,
        });
        break;

      case 'bluff': // Fact or bluff: the roller states a fact (true or invented); anyone may call bluff
        if (this.activePlayers().length < 2) break;
        card = this.cardFor('bluff', tile, spin);
        this.setPending({ type: 'bluff_say', actor_id: actor.id, card: this.publicCard(card), penalty: card.penalty, tile: number });
        break;

      case 'spotlight': // a creative prompt against the clock
        card = this.cardFor('spotlight', tile, spin);
        timer = parseInt(card.timer || 15, 10) || 15;
        this.setPending({
          type: 'spot', actor_id: actor.id, card: Object.assign(this.publicCard(card), { timer: timer }),
          penalty: card.penalty, tile: number, ttl: timer + 14,
        });
        break;

      case 'rule':
        card = this.cardFor('rule', tile, spin);
        room.active_rule = substr(card.text, 0, 220);
        this.log({ type: 'rule', player_id: actor.id, text: card.text, icon: card.icon, spin: spin, intensity: card.intensity != null ? card.intensity : room.intensity });
        break;

      case 'power':
        this.grantPower(actor, spin);
        break;

      case 'spin':
        this.resolveTile(actor, tile, this.spinKind(), true);
        break;

      default: // blank / start
        if (!spin && kind === 'blank' && tile && this.freeEvent(actor, number)) break;
        if (!spin && kind === 'blank' && tile) {
          // A free square: no action, but a little flavour so it feels deliberate (a breather, a hydration nudge…).
          card = this.cardFor('neutral', tile, false);
          this.log({ type: 'neutral', player_id: actor.id, text: card.text, icon: 'hourglass', tile: number });
        }
        if (spin) {
          this.log({
            type: 'card', kind: 'blank', text: 'The wheel stops on… nothing! Lucky you.', icon: 'mystery', spin: true,
            source_id: actor.id, target_ids: [], tile: number,
          });
        }
        break;
    }
  };

  /** Some free squares do something small and fun (the rest stay calm). Returns true when an event ran. */
  P.freeEvent = function (actor, number) {
    var st = this.g.deck_state || (this.g.deck_state = {});
    if ((st.cool || 0) > 0) { st.cool -= 1; return false; }     // a calm square follows every event
    if (randInt(1, 100) > (parseInt(this.cfg.free_event_chance || 0, 10) || 0)) return false;
    var self = this, defs = this.powers();
    var table = Object.assign({}, obj(this.cfg.free_events));
    var hasOpen = Object.keys(defs).some(function (k) { return self.power(actor, k) < defs[k].max; });
    if (!hasOpen) delete table.gift;                              // a gift of a full power would be wasted
    var recent = (st.events || []).slice(-2);
    var fresh = Object.keys(table).filter(function (k) { return recent.indexOf(k) === -1; });
    var keys = fresh.length ? fresh : Object.keys(table), total = 0, i;
    for (i = 0; i < keys.length; i++) total += table[keys[i]];
    var roll = randInt(1, Math.max(1, total)), event = null;
    for (i = 0; i < keys.length; i++) { roll -= table[keys[i]]; if (roll <= 0) { event = keys[i]; break; } }
    st.events = (st.events || []).concat([event]).slice(-3);
    st.cool = 1;
    var card;
    switch (event) {
      case 'quickfire':
        card = this.cardFor('quickfire', null, false);
        var timer = parseInt(card.timer || 10, 10) || 10;
        var pc = this.publicCard(card); pc.timer = timer;
        this.setPending({ type: 'spot', actor_id: actor.id, card: pc, penalty: parseInt(card.penalty, 10) || 1, tile: number, ttl: timer + 14 });
        return true;
      case 'rollagain':
        card = this.cardFor('rollagain', null, false);
        this.log({ type: 'extra_turn', player_id: actor.id, text: card.text, icon: card.icon, tile: number });
        this.extraTurn = true;
        return true;
      case 'gift':
        card = this.cardFor('gift', null, false);
        this.log({ type: 'neutral', player_id: actor.id, text: card.text, icon: card.icon, tile: number });
        this.grantPower(actor, false);
        return true;
      case 'groupgame':
        card = this.publicCard(this.cardFor('groupgame', null, false));
        this.log({
          type: 'card', kind: 'groupgame', text: card.text, icon: card.icon, spin: false, intensity: card.intensity, random: false,
          source_id: actor.id, target_ids: this.activePlayers().map(function (p) { return p.id; }), tile: number,
        });
        return true;
    }
    return false;
  };

  /** Sometimes the wheel - not the roller - picks the victim. Uses cards written with {target}; otherwise falls back to a normal pick. */
  P.tryRandomPick = function (actor, tile, number, spin) {
    var eligible = this.activePlayers().filter(function (p) { return p.id !== actor.id; }).map(function (p) { return p.id; });
    if (!eligible.length || randInt(1, 100) > (parseInt(this.cfg.random_pick_chance || 0, 10) || 0)) return false;
    var drawn = this.draw('pick', true);
    if (!drawn) return false;
    var target = this.player(arrayRand(eligible));
    this.fillTarget = target;
    var card = this.publicCard(this.cardFor('pick', tile, spin, drawn));
    card.random = true;
    this.fillTarget = null;
    this.log({ type: 'random_pick', player_id: actor.id, candidates: eligible, target_id: target.id, tile: number });
    this.hit(actor, [target], card, number);
    return true;
  };

  P.spinKind = function () {
    var table = obj(this.cfg.spin_table), keys = Object.keys(table), sum = 0, i;
    for (i = 0; i < keys.length; i++) sum += table[keys[i]];
    var roll = randInt(1, sum);
    for (i = 0; i < keys.length; i++) {
      roll -= table[keys[i]];
      if (roll <= 0) return keys[i];
    }
    return 'blank';
  };

  P.grantPower = function (actor, spin) {
    var self = this, defs = this.powers(), keys = Object.keys(defs);
    // Prefer a power the player isn't maxed on.
    var open = keys.filter(function (k) { return self.power(actor, k) < defs[k].max; });
    if (!open.length) {
      this.log({ type: 'power', player_id: actor.id, power: null, spin: spin });
      return;
    }
    var key = arrayRand(open);
    this.addPower(actor, key, 1);
    this.log({ type: 'power', player_id: actor.id, power: key, spin: spin });
  };

  // ───────────────────────────── answering prompts ─────────────────────────────
  P.decide = function (choice, targetId) {
    this.assertPlaying();
    var pending = this.g.pending;
    if (!pending) throw new GameError('There is nothing to decide right now.');
    var actorId = parseInt(pending.actor_id, 10);
    this.answerPending(pending, choice, targetId == null ? null : parseInt(targetId, 10), actorId);
  };

  P.answerPending = function (pending, choice, targetId, by) {
    var room = this.g.room, self = this, actor, target, source, roller, caller, others, judges, value, nw;
    var turnOwner = this.player(room.current_player_id);

    switch (pending.type) {
      case 'reroll':
        if (choice !== 'use' && choice !== 'skip') throw new GameError('Choose use or skip.');
        actor = this.player(pending.actor_id);
        value = parseInt(pending.roll, 10);
        this.clearPending();
        if (choice === 'use' && this.power(actor, 'reroll') > 0) {
          this.addPower(actor, 'reroll', -1);
          nw = randInt(1, 6);
          this.log({ type: 'reroll', player_id: actor.id, from_roll: value, new_roll: nw });
          value = nw;
        }
        this.resolveRoll(actor, value, parseInt(pending.from, 10));
        return;

      case 'shield':
        if (choice !== 'use' && choice !== 'skip') throw new GameError('Choose use or skip.');
        target = this.player(pending.actor_id);
        source = this.player(pending.source_id) || turnOwner;
        this.clearPending();
        if (choice === 'use' && this.power(target, 'shield') > 0) {
          this.addPower(target, 'shield', -1);
          this.bump(target, 'dodges');
          this.log({ type: 'shield', player_id: target.id, source_id: source.id, text: pending.card.text, icon: pending.card.icon });
        } else {
          this.commitHit(source, [target], pending.card, pending.tile == null ? null : pending.tile);
        }
        break;

      case 'pick':
        if (choice !== 'target' || !targetId || pending.eligible.indexOf(targetId) === -1) throw new GameError('Pick one of the players.');
        actor = this.player(pending.actor_id);
        target = this.player(targetId);
        this.clearPending();
        this.resolvePick(pending, actor, target);
        break;

      case 'tf': { // True or false
        if (choice !== 'true' && choice !== 'false') throw new GameError('Say true or false.');
        actor = this.player(pending.actor_id);
        var guess = choice === 'true', correct = guess === !!pending.answer;
        this.clearPending();
        this.bump(actor, correct ? 'facts_right' : 'facts_wrong');
        this.log({
          type: 'factcheck', player_id: actor.id, text: pending.card.text, icon: pending.card.icon,
          answer: !!pending.answer, guess: guess, correct: correct, explain: pending.explain,
          penalty: this.penaltyText(parseInt(pending.penalty, 10)), spin: pending.card.spin || false,
        });
        if (!correct) this.penalize(actor, actor, parseInt(pending.penalty, 10), pending.tile == null ? null : pending.tile);
        break;
      }

      case 'bluff_say': // the roller has said their "fact" out loud
        if (choice !== 'done') throw new GameError("Tap 'Said it' once you've told the table.");
        roller = this.player(pending.actor_id);
        others = this.activePlayers().filter(function (p) { return p.id !== roller.id; }).map(function (p) { return p.id; });
        this.clearPending();
        this.bump(roller, 'bluffs');
        this.log({ type: 'bluff_said', player_id: roller.id });
        this.setPending({
          type: 'bluff_call', actor_id: roller.id,
          // One shared device in party mode (the table says who is calling).
          actors: [roller.id],
          eligible: others, card: pending.card, penalty: pending.penalty, tile: pending.tile == null ? null : pending.tile, ttl: 12,
        });
        break;

      case 'bluff_call': {
        roller = this.player(pending.actor_id);
        if (choice === 'none') { // nobody doubts them
          this.clearPending();
          this.log({ type: 'bluff_pass', player_id: roller.id });
          break;
        }
        if (choice !== 'call') throw new GameError('Call bluff, or let it pass.');
        var callerId = parseInt(targetId || 0, 10);
        if (pending.eligible.indexOf(callerId) === -1) throw new GameError('Pick who is calling bluff.');
        caller = this.player(callerId);
        this.clearPending();
        this.bump(caller, 'bluffs_called');
        this.log({ type: 'bluff_called', player_id: roller.id, caller_id: caller.id });
        // Neutral judges decide if there are any; with only two players the doubter judges (party: the table, via the roller's phone).
        judges = this.activePlayers().filter(function (p) { return p.id !== roller.id && p.id !== caller.id; }).map(function (p) { return p.id; });
        this.setPending({
          type: 'bluff_verdict', actor_id: roller.id, caller_id: caller.id, actors: [roller.id],
          card: pending.card, penalty: pending.penalty, tile: pending.tile == null ? null : pending.tile, ttl: 40,
        });
        break;
      }

      case 'bluff_verdict':
        if (choice !== 'proved' && choice !== 'busted' && !(choice === 'void' && by === null)) throw new GameError('Was it proven, or was the bluff caught?');
        roller = this.player(pending.actor_id);
        caller = this.player(pending.caller_id);
        this.clearPending();
        this.log({ type: 'bluff_result', player_id: roller.id, caller_id: caller.id, result: choice, penalty: this.penaltyText(parseInt(pending.penalty, 10)) });
        if (choice === 'proved') { // the roller backed it up → the doubter pays
          this.bump(roller, 'bluffs_won');
          this.penalize(roller, caller, parseInt(pending.penalty, 10), pending.tile == null ? null : pending.tile);
        } else if (choice === 'busted') { // caught bluffing → the roller pays
          this.bump(caller, 'bluffs_caught');
          this.penalize(caller, roller, parseInt(pending.penalty, 10), pending.tile == null ? null : pending.tile);
        }
        break;

      case 'spot': { // Spotlight: did they pull it off before the clock ran out?
        if (choice !== 'nailed' && choice !== 'failed') throw new GameError('Nailed it, or not?');
        actor = this.player(pending.actor_id);
        this.clearPending();
        var nailed = choice === 'nailed';
        this.bump(actor, nailed ? 'spots_nailed' : 'spots_failed');
        this.log({ type: 'spot_result', player_id: actor.id, nailed: nailed, text: pending.card.text, penalty: this.penaltyText(parseInt(pending.penalty, 10)) });
        if (!nailed) this.penalize(actor, actor, parseInt(pending.penalty, 10), pending.tile == null ? null : pending.tile);
        break;
      }

      default:
        this.clearPending();
    }

    // A prompt can chain into another (pick → shield). Only finish the turn once nothing is waiting.
    if (!this.g.pending && room.status === 'active') this.endTurn();
  };

  P.resolvePick = function (pending, actor, target) {
    var card = pending.card, tile = pending.tile == null ? null : pending.tile, ra, rb, i, loser, winner;
    switch (pending.sub) {
      case 'swap': {
        var a = actor.position, b = target.position;
        actor.position = b;
        target.position = a;
        this.bump(actor, 'swaps');
        this.log({ type: 'swap', a_id: actor.id, b_id: target.id, a_pos: b, b_pos: a, text: card.text, icon: card.icon, spin: card.spin || false });
        break;
      }
      case 'duel':
        for (i = 0; i < 5; i++) {
          ra = randInt(1, 6);
          rb = randInt(1, 6);
          if (ra !== rb) break;
        }
        loser = ra === rb ? (randInt(0, 1) ? actor : target) : (ra < rb ? actor : target);
        winner = loser.id === actor.id ? target : actor;
        this.bump(winner, 'duels_won');
        this.log({ type: 'duel', a_id: actor.id, b_id: target.id, a_roll: ra, b_roll: rb, loser_id: loser.id });
        this.hit(winner, [loser], card, tile);
        break;
      default: // pick
        this.hit(actor, [target], card, tile);
    }
  };

  /** Timed-out prompts resolve themselves in the safest way (never burns a power, never skips a hit unfairly). */
  P.autoResolve = function () {
    var p = this.g.pending;
    if (!p) return;
    switch (p.type) {
      case 'pick': this.answerPending(p, 'target', arrayRand(p.eligible), null); break;
      case 'tf': this.answerPending(p, randInt(0, 1) ? 'true' : 'false', null, null); break; // a coin-flip keeps the game moving
      case 'bluff_say': this.answerPending(p, 'done', null, null); break;
      case 'bluff_call': this.answerPending(p, 'none', null, null); break;      // nobody called in time
      case 'bluff_verdict': this.answerPending(p, 'void', null, null); break;   // no ruling → nobody drinks
      case 'spot': this.answerPending(p, 'nailed', null, null); break;         // an absent player isn't punished
      default: this.answerPending(p, 'skip', null, null);
    }
  };

  // ───────────────────────────── turn flow ─────────────────────────────
  P.endTurn = function () {
    var room = this.g.room;
    this.clearPending();
    var active = this.activePlayers();
    if (active.length < 2) { this.finishByAttrition(active[0] || null); return; }

    var cur = this.player(room.current_player_id);
    if (cur) cur.missed_turns = 0;
    var curOrder = cur ? cur.turn_order : -1;
    var next = null;
    for (var i = 0; i < active.length; i++) if (active[i].turn_order > curOrder) { next = active[i]; break; }
    if (!next) {
      next = active[0];
      room.round = (parseInt(room.round, 10) || 0) + 1;
    }
    room.current_player_id = next.id;
  };

  P.win = function (winner) {
    var room = this.g.room;
    winner.status = 'won';
    room.status = 'completed';
    room.winner_player_id = winner.id;
    room.current_player_id = null;
    this.clearPending();
    this.log({ type: 'win', player_id: winner.id });
  };

  /** Everyone else bowed out — the last player standing takes it. */
  P.finishByAttrition = function (last) {
    var room = this.g.room;
    room.status = 'completed';
    room.current_player_id = null;
    this.clearPending();
    if (last) {
      last.status = 'won';
      room.winner_player_id = last.id;
      this.log({ type: 'win', player_id: last.id, by_default: true });
    }
  };

  P.removePlayer = function (p) {
    var room = this.g.room;
    var wasCurrent = room.current_player_id === p.id;
    p.status = 'left';
    this.log({ type: 'left', player_id: p.id });

    if (room.status !== 'active') return;
    if (this.g.pending && parseInt(this.g.pending.actor_id, 10) === p.id) this.clearPending();
    if (this.activePlayers().length < 2) { this.finishByAttrition(this.activePlayers()[0] || null); return; }
    if (room.host_player_id === p.id) room.host_player_id = this.activePlayers()[0].id;
    if (wasCurrent || !room.current_player_id) this.endTurn();
  };

  // ───────────────────────────── state snapshot ─────────────────────────────
  P.state = function () {
    var g = this.g, room = g.room, self = this, pending = g.pending, view = null;
    if (pending) {
      switch (pending.type) {
        case 'reroll':
          view = { type: 'reroll', actor_id: pending.actor_id, roll: pending.roll, preview_tile: pending.preview_tile, preview_kind: pending.preview_kind }; break;
        case 'shield':
          view = { type: 'shield', actor_id: pending.actor_id, source_id: pending.source_id, card: pending.card }; break;
        case 'pick':
          view = { type: 'pick', actor_id: pending.actor_id, sub: pending.sub, card: pending.card, eligible: pending.eligible }; break;
        case 'tf': case 'bluff_say': case 'spot':
          view = { type: pending.type, actor_id: pending.actor_id, card: pending.card }; break;
        case 'bluff_call':
          view = { type: 'bluff_call', actor_id: pending.actor_id, actors: pending.actors, eligible: pending.eligible, card: pending.card }; break;
        case 'bluff_verdict':
          view = { type: 'bluff_verdict', actor_id: pending.actor_id, actors: pending.actors, caller_id: pending.caller_id, card: pending.card }; break;
        default: view = null;
      }
    }
    var players = g.players.slice().sort(function (a, b) { return a.turn_order - b.turn_order; }).map(function (p) {
      return {
        id: p.id, name: p.name, color: p.color, avatar: p.avatar, position: p.position, status: p.status, turn_order: p.turn_order,
        powers: { shield: self.power(p, 'shield'), reroll: self.power(p, 'reroll') },
        is_host: p.id === room.host_player_id, missed: p.missed_turns, buzz: (p.stats && p.stats.buzz) || 0,
      };
    });
    return {
      room: {
        token: room.token, type: room.type, status: room.status,
        content_mode: room.content_mode, intensity: room.intensity,
        join_code: room.join_code, round: room.round, active_rule: room.active_rule,
        current_player_id: room.current_player_id, winner_player_id: room.winner_player_id,
        host_player_id: room.host_player_id, max_players: room.max_players,
        tile_count: room.tile_count, event_seq: room.event_seq,
        rematch_token: room.rematch_token,
        turn_seconds_left: null, pending_seconds_left: null,
      },
      players: players,
      pending: view,
      actions: g.actions || [],
      me: null,
      recap: room.status === 'completed' ? this.recap() : null,
    };
  };

  /** Fun end-of-night awards from the per-player counters. */
  P.recap = function () {
    var self = this, room = this.g.room;
    var ps = this.g.players.slice().sort(function (a, b) { return a.turn_order - b.turn_order; });
    var out = [];
    AWARDS.forEach(function (a) {
      var sorted = ps.slice().sort(function (x, y) { return self.stat(y, a.key) - self.stat(x, a.key); });
      var best = sorted[0];
      if (best && self.stat(best, a.key) > 0) out.push({ icon: a.icon, title: a.title, player_id: best.id, name: best.name, value: self.stat(best, a.key) });
    });
    var ranking = ps.slice().sort(function (x, y) {
      var kx = x.status === 'won' ? Number.MAX_SAFE_INTEGER : x.position, ky = y.status === 'won' ? Number.MAX_SAFE_INTEGER : y.position;
      return ky - kx;
    }).map(function (p) { return { id: p.id, name: p.name, color: p.color, avatar: p.avatar, position: p.position, status: p.status }; });
    return { awards: out, ranking: ranking, rounds: room.round };
  };

  // ───────────────────────────── setup ─────────────────────────────
  function validateSetup(pack, boardId, mode, intensity) {
    var cfg = pack.config.modes[mode];
    if (!cfg) throw new GameError('Unknown game mode.');
    if (!cfg.enabled) throw new GameError(cfg.label + " mode isn't available yet.");
    if (!pack.config.intensities[intensity]) throw new GameError('Unknown intensity.');
    var board = null;
    for (var i = 0; i < pack.boards.length; i++) if (pack.boards[i].id === boardId) board = pack.boards[i];
    if (!board) throw new GameError('That board is not available.');
    return board;
  }

  function addPlayer(pack, game, e, order) {
    var name = String(e.name == null ? '' : e.name).replace(/\s+/g, ' ').trim() || 'Player ' + (order + 1);
    name = substr(name, 0, 24);
    // Same name twice at one table gets a number so the picker is unambiguous.
    var base = name, n = 1;
    var taken = function (nm) { return game.players.some(function (p) { return p.name === nm; }); };
    while (taken(name)) name = substr(base, 0, 21) + ' ' + (++n);
    var colors = pack.config.colors, powers = {}, defs = obj(pack.config.powers);
    Object.keys(defs).forEach(function (k) { powers[k] = defs[k].start; });
    // the table's chosen number of blocks (shields) — default 1
    if (powers.shield !== undefined && game.room && game.room.start_shields != null) powers.shield = Math.max(0, Math.min(defs.shield.max, parseInt(game.room.start_shields, 10) || 0));
    var p = {
      id: game.nextPlayerId++, name: name, turn_order: order,
      color: e.color || colors[order % colors.length],
      avatar: avatarValid(pack, e.avatar) ? e.avatar : avatarDefault(pack, order),
      position: 0, status: 'active', powers: powers, stats: {}, missed_turns: 0,
    };
    game.players.push(p);
    return p;
  }

  function newGame(pack, board, mode, intensity, maxPlayers, startShields) {
    return {
      v: 1, packVersion: pack.version || null, nextPlayerId: 1,
      room: {
        token: 'local', type: 'party', board_id: board.id, content_mode: mode, intensity: intensity,
        status: 'active', current_player_id: null, winner_player_id: null, host_player_id: null,
        round: 1, active_rule: null, event_seq: 0, rematch_token: null, join_code: null,
        max_players: maxPlayers, tile_count: board.tile_count, start_shields: startShields,
      },
      players: [], pending: null, actions: [],
    };
  }

  /** One device, 2–12 named players, game starts immediately. */
  function create(pack, opts) {
    var mode = opts.mode, intensity = parseInt(opts.intensity, 10);
    var board = validateSetup(pack, opts.boardId, mode, intensity);
    var entries = opts.entries || [], lim = pack.config.players;
    if (entries.length < lim.min || entries.length > lim.max) throw new GameError('Party games need ' + lim.min + '–' + lim.max + ' players.');
    var sh = opts.startShields == null || opts.startShields === '' ? null : Math.max(0, Math.min(((pack.config.powers || {}).shield || { max: 5 }).max, parseInt(opts.startShields, 10) || 0));
    var g = newGame(pack, board, mode, intensity, lim.max, sh);
    entries.forEach(function (e, i) { addPlayer(pack, g, e, i); });
    var first = g.players[0];
    g.room.current_player_id = first.id;
    g.room.host_player_id = first.id;
    new Ctx(pack, g).log({ type: 'start' });
    return g;
  }

  /** Same table, fresh game (players copy over). */
  function rematch(pack, old) {
    if (old.room.status !== 'completed') throw new GameError('The game is still going.');
    var board = validateSetup(pack, old.room.board_id, old.room.content_mode, old.room.intensity);
    var g = newGame(pack, board, old.room.content_mode, old.room.intensity, old.room.max_players, old.room.start_shields);
    old.players.filter(function (p) { return p.status !== 'left'; })
      .sort(function (a, b) { return a.turn_order - b.turn_order; })
      .forEach(function (p, i) { addPlayer(pack, g, { name: p.name, color: p.color, avatar: p.avatar }, i); });
    if (g.players.length) {
      g.room.current_player_id = g.players[0].id;
      g.room.host_player_id = g.players[0].id;
    }
    new Ctx(pack, g).log({ type: 'start' });
    return g;
  }

  /**
   * An online room lost its connection: turn the last server snapshot into a local party game so the group can
   * carry on on one phone. Players keep seat order, names, colours, avatars, positions and powers; stats start fresh;
   * any half-answered prompt is dropped.
   */
  function fromOnline(pack, onlineState, boardId) {
    var rs = onlineState.room, board = null, i;
    if (boardId != null) for (i = 0; i < pack.boards.length; i++) if (pack.boards[i].id === boardId) board = pack.boards[i];
    if (!board) for (i = 0; i < pack.boards.length; i++) if (pack.boards[i].tile_count === rs.tile_count) { board = pack.boards[i]; break; }
    if (!board) board = pack.boards[0];
    if (!board) throw new GameError('That board is not available.');
    var mode = pack.config.modes[rs.content_mode] ? rs.content_mode : 'family';
    var intensity = pack.config.intensities[rs.intensity] ? rs.intensity : 2;
    var g = newGame(pack, board, mode, intensity, pack.config.players.max);
    var map = {};
    onlineState.players.slice().sort(function (a, b) { return a.turn_order - b.turn_order; }).forEach(function (op, idx) {
      var np = {
        id: g.nextPlayerId++, name: op.name, turn_order: idx, color: op.color,
        avatar: avatarValid(pack, op.avatar) ? op.avatar : avatarDefault(pack, idx),
        position: Math.max(0, Math.min(board.tile_count, parseInt(op.position, 10) || 0)), status: op.status === 'left' ? 'left' : (op.status === 'won' ? 'won' : 'active'),
        powers: {}, stats: {}, missed_turns: 0,
      };
      var defs = obj(pack.config.powers);
      Object.keys(defs).forEach(function (k) { np.powers[k] = Math.max(0, Math.min(defs[k].max, parseInt((op.powers || {})[k], 10) || 0)); });
      g.players.push(np);
      map[op.id] = np.id;
    });
    g.room.round = Math.max(1, parseInt(rs.round, 10) || 1);
    g.room.active_rule = rs.active_rule || null;
    var ctx = new Ctx(pack, g);
    var active = ctx.activePlayers();
    if (rs.status === 'completed' && map[rs.winner_player_id] != null) {
      g.room.status = 'completed';
      g.room.winner_player_id = map[rs.winner_player_id];
      g.room.current_player_id = null;
      var w = ctx.player(g.room.winner_player_id); if (w) w.status = 'won';
    } else if (active.length < 2) {
      g.room.host_player_id = active[0] ? active[0].id : null;
      ctx.finishByAttrition(active[0] || null);
    } else {
      var cur = map[rs.current_player_id];
      var curP = cur != null ? ctx.player(cur) : null;
      g.room.current_player_id = curP && curP.status === 'active' ? curP.id : active[0].id;
      g.room.host_player_id = active[0].id;
      g.room.status = 'active';
    }
    ctx.log({ type: 'start' });
    return g;
  }

  // ───────────────────────────── public API ─────────────────────────────
  /** Runs fn(ctx) on a copy of the game; commits it into `game` only if nothing threw. */
  function run(pack, game, fn) {
    var work = clone(game);
    var ctx = new Ctx(pack, work);
    var res = fn(ctx);
    Object.keys(game).forEach(function (k) { delete game[k]; });
    Object.assign(game, work);
    return res;
  }

  return {
    version: 1,
    GameError: GameError,
    setRandom: setRandom,
    create: create,
    rematch: rematch,
    fromOnline: fromOnline,
    roll: function (pack, game) { return run(pack, game, function (c) { c.roll(); }); },
    decide: function (pack, game, choice, targetId) { return run(pack, game, function (c) { c.decide(choice, targetId); }); },
    leave: function (pack, game, playerId) {
      return run(pack, game, function (c) {
        var p = c.player(parseInt(playerId, 10));
        if (!p || p.status === 'left') return;
        c.removePlayer(p);
      });
    },
    /** Party: add one more player while the game is running. They start off the board and play last. */
    addPlayer: function (pack, game, entry) {
      return run(pack, game, function (c) {
        var g = c.g;
        if (g.room.status !== 'active') throw new GameError(g.room.status === 'completed' ? 'This game is over.' : 'The game has not started yet.');
        var seated = g.players.filter(function (p) { return p.status !== 'left'; }).length;
        if (seated >= pack.config.players.max) throw new GameError('The table is full (' + pack.config.players.max + ' players).');
        var order = g.players.reduce(function (m, p) { return Math.max(m, p.turn_order); }, -1) + 1;
        var p = addPlayer(pack, g, entry || {}, order);
        c.log({ type: 'joined', player_id: p.id });
        return p.id;
      });
    },
    autoResolve: function (pack, game) { return run(pack, game, function (c) { c.autoResolve(); }); },
    state: function (pack, game) { return new Ctx(pack, game).state(); },
  };
});
