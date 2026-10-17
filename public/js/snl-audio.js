/* ═══════════════════════════════════════════════════════════════
   SNL AUDIO — every game sound goes through SNLAudio.play(name).
   • short recorded clips live in /public/audio (open, close, arrive, badge, bonus, quest-complete, notification)
   • everything else is synthesised with WebAudio, so there is nothing more to download
   • the old ArcadeSound module still supplies roll / move / ladder / snake / mystery / win / toggle…
   • background music: /public/audio/playback.mp3 (one continuous loop) — it plays on its own volume + button.
   ═══════════════════════════════════════════════════════════════ */
const SNLAudio = (() => {
  let ctx = null;
  let sfxOn = true;
  let musicOn = true;
  let bg = null;
  let started = false;
  const base = (window.SNL && window.SNL.audioBase) || '/audio';
  const clips = {};

  const CLIP = { open: ['open.mp3', 0.55], close: ['close.mp3', 0.45], arrive: ['arrive.mp3', 0.5], badge: ['badge.mp3', 0.6], bonus: ['bonus.mp3', 0.6], quest: ['quest-complete.mp3', 0.6], notify: ['notification.mp3', 0.55] };

  function ac() {
    if (!ctx) { try { ctx = new (window.AudioContext || window.webkitAudioContext)(); } catch (_) { return null; } }
    if (ctx.state === 'suspended') ctx.resume().catch(() => {});
    return ctx;
  }

  function osc(type, f0, f1, at, dur, vol) {
    const c = ac(); if (!c) return;
    const g = c.createGain(); g.connect(c.destination);
    g.gain.setValueAtTime(0.0001, at); g.gain.linearRampToValueAtTime(vol, at + 0.01); g.gain.exponentialRampToValueAtTime(0.0001, at + dur);
    const o = c.createOscillator(); o.type = type; o.frequency.setValueAtTime(f0, at);
    if (f1 && f1 !== f0) o.frequency.exponentialRampToValueAtTime(f1, at + dur);
    o.connect(g); o.start(at); o.stop(at + dur + 0.03);
  }
  function noise(at, dur, vol, lo = 4000, type = 'lowpass') {
    const c = ac(); if (!c) return;
    const n = Math.ceil(c.sampleRate * dur), buf = c.createBuffer(1, n, c.sampleRate), d = buf.getChannelData(0);
    for (let i = 0; i < n; i++) d[i] = Math.random() * 2 - 1;
    const s = c.createBufferSource(); s.buffer = buf;
    const f = c.createBiquadFilter(); f.type = type; f.frequency.value = lo;
    const g = c.createGain(); g.gain.setValueAtTime(vol, at); g.gain.exponentialRampToValueAtTime(0.0001, at + dur);
    s.connect(f); f.connect(g); g.connect(c.destination); s.start(at); s.stop(at + dur + 0.03);
  }
  function bell(freq, at, dur, vol) {
    const c = ac(); if (!c) return;
    const g = c.createGain(); g.gain.setValueAtTime(vol, at); g.gain.exponentialRampToValueAtTime(0.0001, at + dur); g.connect(c.destination);
    [1, 2.76, 5.4].forEach((r, i) => {
      const o = c.createOscillator(); o.type = i ? 'sine' : 'triangle'; o.frequency.value = freq * r;
      const h = c.createGain(); h.gain.value = i === 0 ? 1 : i === 1 ? 0.32 : 0.1; o.connect(h); h.connect(g); o.start(at); o.stop(at + dur + 0.05);
    });
  }
  const now = () => { const c = ac(); return c ? c.currentTime : 0; };

  // synthesised effects — each reads like the thing that happened
  const SYN = {
    tick() { osc('square', 1500, 900, now(), 0.04, 0.07); },
    clink() { const t = now(); bell(2300, t, 0.5, 0.16); bell(3100, t + 0.012, 0.35, 0.1); },
    cheers() { const t = now(); [0, 0.16, 0.34].forEach((d, i) => { bell(2100 + i * 260, t + d, 0.5, 0.15); noise(t + d, 0.03, 0.1, 6000, 'highpass'); }); },
    gulp() { const t = now(); [0, 0.13, 0.27].forEach((d, i) => osc('sine', 300 - i * 40, 120, t + d, 0.11, 0.2)); noise(t + 0.4, 0.12, 0.05, 1200); },
    whoosh() { const t = now(), c = ac(); if (!c) return; noise(t, 0.45, 0.2, 900, 'bandpass'); osc('sine', 260, 900, t, 0.4, 0.07); },
    clash() { const t = now(); noise(t, 0.09, 0.3, 7000, 'highpass'); bell(1500, t, 0.5, 0.18); bell(2250, t + 0.03, 0.4, 0.12); },
    buzz() { const t = now(); osc('sawtooth', 190, 110, t, 0.35, 0.2); osc('square', 95, 60, t + 0.02, 0.35, 0.1); },
    gavel() { const t = now(); osc('triangle', 180, 70, t, 0.16, 0.4); noise(t, 0.05, 0.28, 2500); osc('triangle', 180, 70, t + 0.16, 0.16, 0.34); noise(t + 0.16, 0.05, 0.22, 2500); },
    sparkle() { const t = now(); [1320, 1760, 2093, 2637].forEach((f, i) => bell(f, t + i * 0.07, 0.4, 0.13)); },
    heart() { const t = now(); [523, 659, 784].forEach((f, i) => bell(f, t + i * 0.1, 0.6, 0.18)); },
    drum() { const t = now(); for (let i = 0; i < 6; i++) osc('triangle', 150, 90, t + i * 0.07, 0.08, 0.2); },
    // a card being dealt: a quick air-whoosh, a soft thump when it lands, and a little sparkle
    deal() { const t = now(); noise(t, 0.32, 0.16, 1400, 'bandpass'); osc('sine', 340, 880, t, 0.28, 0.05); osc('sine', 110, 55, t + 0.22, 0.16, 0.22); bell(1760, t + 0.3, 0.35, 0.06); },
    // one word landing on a card
    pip() { osc('sine', 880 + Math.random() * 220, 1200, now(), 0.045, 0.035); },
    // buttons
    press() { const t = now(); osc('triangle', 520, 330, t, 0.07, 0.12); },
    unlock() { ac(); },
  };

  // sounds delegated to the shared ArcadeSound module
  const LEGACY = ['roll', 'move', 'ladder', 'snake', 'mystery', 'win', 'toggle', 'boostArm', 'shieldUp', 'decisionPing', 'reaction', 'bust', 'cashout', 'coinGain', 'coinLoss'];

  function clip(name) {
    const spec = CLIP[name]; if (!spec) return false;
    try {
      if (!clips[name]) { clips[name] = new Audio(base + '/' + spec[0]); clips[name].preload = 'auto'; }
      const a = clips[name].cloneNode(); a.volume = spec[1];
      a.play().catch(() => SYN.sparkle && name === 'bonus' && SYN.sparkle());
    } catch (_) { return false; }
    return true;
  }

  // ── background music: one continuous loop that dips briefly under every effect, then swells back ──
  const MUSIC_VOL = 0.30, DUCK_VOL = 0.05;
  let duckUntil = 0, holdDuck = false, fader = null;
  // effects that are too small or too frequent to dip the music for
  const NO_DUCK = { tick: 1, pip: 1, press: 1, toggle: 1, move: 1, reaction: 1 };
  const LONG = { win: 2600, deal: 1500, quest: 1800, cheers: 1500, bonus: 1600, badge: 1500 };

  function fadeLoop() {
    if (!bg) { fader = null; return; }
    const target = (holdDuck || performance.now() < duckUntil) ? DUCK_VOL : MUSIC_VOL;
    const v = bg.volume;
    const next = target < v ? Math.max(target, v - 0.06) : Math.min(target, v + 0.012);   // dips fast, swells back slowly
    bg.volume = Math.max(0, Math.min(1, next));
    if (Math.abs(next - target) < 0.002) { bg.volume = target; fader = null; return; }
    fader = requestAnimationFrame(fadeLoop);
  }
  function kick() { if (bg && !fader) fader = requestAnimationFrame(fadeLoop); }
  function dip(ms) { duckUntil = Math.max(duckUntil, performance.now() + ms); kick(); }

  function play(name) {
    if (!sfxOn) return;
    try {
      if (!NO_DUCK[name]) dip(LONG[name] || 1000);
      if (CLIP[name]) { clip(name); return; }
      if (SYN[name]) { SYN[name](); return; }
      if (LEGACY.includes(name) && typeof ArcadeSound !== 'undefined') ArcadeSound.play(name);
    } catch (_) { /* sound is never worth breaking the game */ }
  }

  // ── meme / reaction sounds: picked by moment, different every time (never a repeat within the last ten plays) ──
  let memeOn = true, lastMeme = 0; const recentMemes = [];
  try { memeOn = localStorage.getItem('snl_memes') !== '0'; } catch (_) {}
  function react(moment) {
    if (!sfxOn || !memeOn) return false;
    const C = window.SNL || {}, M = C.memeSounds, cfg = C.memeCfg || {};
    if (!M) return false;
    const now = Date.now();
    if (now - lastMeme < (cfg.cooldown_ms || 3000)) return false;                      // a quiet gap between memes
    const chance = (cfg.chance || {})[moment];
    if (chance != null && Math.random() > chance) return false;                      // not every time
    let pool = (M[moment] || []).slice();
    if (!pool.length) pool = (M.random || []).slice();
    if (!pool.length) return false;
    let fresh = pool.filter((u) => recentMemes.indexOf(u) === -1);
    if (!fresh.length) fresh = pool.filter((u) => u !== recentMemes[recentMemes.length - 1]);
    if (!fresh.length) fresh = pool;
    const url = fresh[Math.floor(Math.random() * fresh.length)];
    recentMemes.push(url); if (recentMemes.length > 10) recentMemes.shift();
    lastMeme = now;
    dip(2200);
    setTimeout(() => { try { const a = new Audio(url); a.volume = cfg.volume || 0.85; a.play().catch(() => {}); } catch (_) {} }, 220);   // lands just after the move's own sound
    return url;   // which file was chosen (handy for tests)
  }

  function startMusic() {
    const url = window.SNL && window.SNL.music;
    if (!url || bg) { if (bg && musicOn) bg.play().catch(() => {}); return; }
    try {
      bg = new Audio(url); bg.loop = true; bg.preload = 'auto'; bg.volume = MUSIC_VOL;
      if (musicOn) bg.play().catch(() => {});
    } catch (_) { bg = null; }
  }
  // polite: pause when the game is in the background, resume when it's back
  document.addEventListener('visibilitychange', () => {
    if (!bg || !musicOn) return;
    if (document.hidden) bg.pause(); else bg.play().catch(() => {});
  });
  // closing the tab / app, or leaving the page, stops the music immediately (and a page restored from the back-forward cache resumes it)
  window.addEventListener('pagehide', () => { if (bg) bg.pause(); });
  window.addEventListener('pageshow', () => { if (bg && musicOn && !document.hidden) bg.play().catch(() => {}); });
  document.addEventListener('pause', () => { if (bg) bg.pause(); });      // Cordova/Capacitor: app sent to the background
  document.addEventListener('resume', () => { if (bg && musicOn) bg.play().catch(() => {}); });

  /** Browsers only allow sound after a tap — call this from the first one. */
  function unlock() {
    ac();
    if (!started) { started = true; startMusic(); }
  }
  document.addEventListener('pointerdown', unlock, { once: true });

  return {
    play, unlock, react,
    get memes() { return memeOn; },
    setMemes(on) { memeOn = !!on; try { localStorage.setItem('snl_memes', on ? '1' : '0'); } catch (_) {} },
    get sfx() { return sfxOn; },
    get music() { return musicOn; },
    setSfx(on) { sfxOn = !!on; try { ArcadeSound.toggle(on); } catch (_) {} },
    setMusic(on) {
      musicOn = !!on;
      if (!bg) { if (musicOn && started) startMusic(); return; }
      if (musicOn) bg.play().catch(() => {}); else bg.pause();
    },
    /** Hold the music low while a card is on screen (true), release it when it's gone (false). */
    duck(on) { holdDuck = !!on; if (!on) dip(250); kick(); },
  };
})();
