<style>
    html, body { height: 100%; margin: 0; overflow: hidden; overscroll-behavior: none; background: #0b0a16; }
    body { font-family: 'Figtree', system-ui, sans-serif; color: #fff; -webkit-tap-highlight-color: transparent; user-select: none; -webkit-user-select: none; }
    button { font-family: inherit; color: inherit; }

    /* ── The stage: always laid out as a LANDSCAPE screen. On a portrait phone the whole thing is
       rotated 90° (so every overlay, the die and the board stay upright relative to each other).
       Container units (cqw/cqh) let children size themselves off the stage, rotated or not. ── */
    .stage {
        position: fixed; inset: 0; overflow: hidden; container-type: size;
        background:
            radial-gradient(ellipse 60% 70% at 15% 0%, rgba(245,158,11,.14), transparent 70%),
            radial-gradient(ellipse 60% 70% at 90% 100%, rgba(236,72,153,.14), transparent 70%), #0b0a16;
    }
    @media (max-width: 1023px) and (orientation: portrait) {
        .stage {
            inset: auto; top: 0; left: 0; width: 100vh; height: 100vw; width: 100dvh; height: 100dvw;
            transform-origin: top left; transform: rotate(90deg) translateY(-100%);
        }
    }

    /* The board is exactly the art's size, so tile positions (% of the board) land on the right squares. */
    .board-wrap {
        position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
        width: min(100cqw, calc(100cqh * var(--bw) / var(--bh))); aspect-ratio: var(--bw) / var(--bh);
        border-radius: .9rem; overflow: hidden; box-shadow: 0 0 0 1px rgba(245,158,11,.2), 0 20px 50px rgba(0,0,0,.45);
    }
    .board-wrap > img { width: 100%; height: 100%; display: block; object-fit: fill; pointer-events: none; -webkit-user-drag: none; }

    /* ── Tokens ── */
    .token {
        --d: clamp(20px, 5.4cqh, 40px);
        position: absolute; width: var(--d); height: var(--d); border-radius: 50%; border: 2px solid #fff;
        display: flex; align-items: center; justify-content: center; font-size: calc(var(--d) * .56); line-height: 1;
        transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px)));
        box-shadow: 0 2px 8px rgba(0,0,0,.55); z-index: 20; transition: left .22s linear, top .22s linear;
    }
    .token.glide { transition: left 1s cubic-bezier(.34,1.2,.64,1), top 1s cubic-bezier(.34,1.2,.64,1); }
    .token.active { z-index: 25; animation: tokPulse 1.4s ease-in-out infinite; }
    .token.gone { display: none; }
    .token .tname {
        position: absolute; top: 100%; left: 50%; transform: translateX(-50%); margin-top: 1px; font-size: calc(var(--d) * .3); font-weight: 900;
        white-space: nowrap; padding: 0 .3em; border-radius: .4em; background: rgba(8,7,16,.78); pointer-events: none;
    }
    .token .tname { display: none; }
    .token.active .tname { display: block; }
    .token.shieldfx { filter: drop-shadow(0 0 8px #38bdf8) drop-shadow(0 0 16px #38bdf8); }
    @keyframes tokPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(255,255,255,.55), 0 2px 8px rgba(0,0,0,.55); } 50% { box-shadow: 0 0 0 9px rgba(255,255,255,0), 0 2px 8px rgba(0,0,0,.55); } }
    .tile-glow {
        position: absolute; width: calc(var(--d, 30px) * 1.2); height: calc(var(--d, 30px) * 1.2); border-radius: 50%; --d: clamp(20px, 5.4cqh, 40px);
        transform: translate(-50%, -50%); pointer-events: none; z-index: 15; opacity: 0; transition: opacity .25s;
    }
    .tile-glow.show { opacity: 1; animation: glowPulse 1.5s ease-in-out infinite; }
    @keyframes glowPulse { 0%,100% { box-shadow: 0 0 10px 4px rgba(245,158,11,.5); } 50% { box-shadow: 0 0 20px 9px rgba(245,158,11,.85); } }

    /* ── HUD pieces ── */
    .hud { position: absolute; z-index: 30; pointer-events: none; }
    .hud > * { pointer-events: auto; }
    .hud-tl { top: 6px; left: 6px; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; max-width: min(34cqw, 230px); }
    .hud-tr { top: 6px; right: 6px; display: flex; gap: 5px; }
    .hud-left { left: 6px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: clamp(3px, 1.3cqh, 7px); max-height: 66cqh; }
    .hud-br { right: 8px; bottom: 8px; display: flex; flex-direction: column; align-items: center; gap: 5px; width: clamp(92px, 26cqh, 150px); }
    .hud-bl { left: 6px; bottom: 6px; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
    .chip {
        display: inline-flex; align-items: center; gap: .3rem; padding: .22rem .55rem; border-radius: 999px; font-size: clamp(.6rem, 2.3cqh, .74rem); font-weight: 800;
        background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.14); backdrop-filter: blur(6px); white-space: nowrap;
    }
    .chip.rule { white-space: normal; line-height: 1.25; border-radius: .8rem; background: rgba(168,85,247,.28); border-color: rgba(168,85,247,.55); max-width: 100%; cursor: pointer; }
    .icon-btn {
        width: clamp(28px, 8.5cqh, 38px); height: clamp(28px, 8.5cqh, 38px); border-radius: 50%; border: 1px solid rgba(255,255,255,.16);
        background: rgba(8,7,16,.72); backdrop-filter: blur(6px); cursor: pointer; font-size: clamp(.8rem, 3cqh, 1.05rem);
        display: inline-flex; align-items: center; justify-content: center;
    }
    .icon-btn:active { transform: scale(.92); }

    .pl { display: flex; align-items: center; gap: 5px; padding: 2px 6px 2px 2px; border-radius: 999px; background: rgba(8,7,16,.66); border: 1.5px solid rgba(255,255,255,.1); backdrop-filter: blur(6px); transition: all .2s; }
    .pl .av { --d: clamp(20px, 6.4cqh, 34px); width: var(--d); height: var(--d); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: calc(var(--d) * .56); flex-shrink: 0; border: 2px solid #fff; }
    .pl .nm { font-size: clamp(.55rem, 2.1cqh, .7rem); font-weight: 800; max-width: 12cqw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: none; }
    .pl .ps { font-size: clamp(.5rem, 1.9cqh, .62rem); font-weight: 900; color: #9ca3af; display: none; }
    .pl.cur { background: rgba(245,158,11,.2); border-color: rgba(245,158,11,.85); }
    .pl.out { opacity: .35; }
    .pl.cur .nm, .pl.cur .ps { display: block; }
    @media (min-width: 720px) and (min-height: 380px) { .pl .nm, .pl .ps { display: block; } .pl .ps { display: none; } .pl.cur .ps { display: block; } }
    .pw { font-size: clamp(.5rem, 1.9cqh, .62rem); font-weight: 900; color: #7dd3fc; letter-spacing: .02em; display: none; }
    @media (min-width: 720px) and (min-height: 380px) { .pw { display: inline; } }

    /* ── Turn banner, die, powers ── */
    .turn-banner { font-size: clamp(.58rem, 2.2cqh, .74rem); font-weight: 900; text-align: center; padding: .3rem .55rem; border-radius: .8rem; width: 100%; box-sizing: border-box; line-height: 1.25; backdrop-filter: blur(6px); }
    .turn-banner.mine { background: rgba(16,185,129,.22); border: 1px solid rgba(16,185,129,.55); color: #6ee7b7; }
    .turn-banner.wait { background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.15); color: #e5e7eb; }
    .turn-banner .secs { display: inline-flex; min-width: 1.3em; justify-content: center; font-variant-numeric: tabular-nums; }
    .turn-banner .secs.low { color: #f87171; }

    .die-badge {
        position: relative; width: clamp(64px, 20cqh, 112px); height: clamp(64px, 20cqh, 112px); border-radius: 50%; border: none; cursor: pointer; padding: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .15rem;
        background: rgba(8,7,16,.7); backdrop-filter: blur(6px);
        box-shadow: 0 0 0 1.5px rgba(255,255,255,.18), 0 10px 26px rgba(0,0,0,.45); transition: transform .15s, filter .2s;
    }
    .die-badge:not(:disabled) { box-shadow: 0 0 0 2px rgba(245,158,11,.75), 0 0 22px rgba(245,158,11,.45), 0 10px 26px rgba(0,0,0,.45); animation: dieReady 1.6s ease-in-out infinite; }
    .die-badge:disabled { filter: grayscale(.8) brightness(.65); cursor: not-allowed; }
    .die-badge:active:not(:disabled) { transform: scale(.94); }
    @keyframes dieReady { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    .die-label { font-size: clamp(.45rem, 1.7cqh, .6rem); font-weight: 900; letter-spacing: .08em; color: #e5e7eb; text-transform: uppercase; }
    .die-scene { --s: clamp(28px, 8.6cqh, 48px); width: var(--s); height: var(--s); perspective: 420px; }
    .die-cube { --s: clamp(28px, 8.6cqh, 48px); width: var(--s); height: var(--s); position: relative; transform-style: preserve-3d; transform: rotateX(-18deg) rotateY(22deg); transition: transform .5s cubic-bezier(.2,.8,.2,1); }
    .die-cube.spinning { animation: dieTumble .55s linear infinite; transition: none; }
    @keyframes dieTumble { from { transform: rotateX(0) rotateY(0) rotateZ(0); } to { transform: rotateX(720deg) rotateY(540deg) rotateZ(180deg); } }
    .die-face { position: absolute; width: var(--s); height: var(--s); border-radius: 22%; display: grid; grid-template-columns: repeat(3,1fr); grid-template-rows: repeat(3,1fr); padding: 17%; box-sizing: border-box; backface-visibility: hidden; }
    .pip { background: radial-gradient(circle at 35% 30%, #4b4b66, #14121f); border-radius: 50%; width: 70%; height: 70%; align-self: center; justify-self: center; }
    .f1 { transform: translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#fff,#eef0f4 60%,#dde1e8); }
    .f6 { transform: rotateY(180deg) translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#c7cbd3,#aeb3bd 60%,#969ba7); }
    .f2 { transform: rotateX(90deg) translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#fdfdfe,#e6e9ee 60%,#d3d7de); }
    .f5 { transform: rotateX(-90deg) translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#d6d9df,#bfc3cc 60%,#a8adb8); }
    .f3 { transform: rotateY(90deg) translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#f2f4f7,#dde1e8 60%,#c9ced7); }
    .f4 { transform: rotateY(-90deg) translateZ(calc(var(--s) / 2)); background: linear-gradient(155deg,#e2e5eb,#cbd0da 60%,#b7bcc7); }
    .die-val {
        position: absolute; top: -3px; right: -3px; width: clamp(20px, 6cqh, 28px); height: clamp(20px, 6cqh, 28px); border-radius: 50%; background: #1a1a2e; border: 2px solid #fbbf24;
        color: #fbbf24; font-weight: 900; font-size: clamp(.65rem, 2.6cqh, .85rem); display: none; align-items: center; justify-content: center;
    }
    .die-val.show { display: flex; }
    .powers { display: flex; gap: 5px; }
    .pwr {
        display: flex; align-items: center; gap: 3px; padding: .2rem .5rem; border-radius: 999px; font-size: clamp(.6rem, 2.3cqh, .78rem); font-weight: 900;
        background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.16); backdrop-filter: blur(6px);
    }
    .pwr.empty { opacity: .4; }

    /* ── Toasts ── */
    .toasts { position: absolute; left: 50%; bottom: 7cqh; transform: translateX(-50%); z-index: 40; display: flex; flex-direction: column; align-items: center; gap: 5px; pointer-events: none; width: min(80cqw, 460px); }
    .toast {
        padding: .45rem .9rem; border-radius: 999px; font-size: clamp(.68rem, 2.6cqh, .9rem); font-weight: 800; text-align: center; max-width: 100%;
        background: rgba(8,7,16,.88); border: 1px solid rgba(255,255,255,.18); box-shadow: 0 8px 22px rgba(0,0,0,.5); animation: toastIn .25s ease both;
    }
    .toast.out { animation: toastOut .3s ease both; }
    @keyframes toastIn { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes toastOut { to { opacity: 0; transform: translateY(-6px); } }

    /* ── Overlays ── */
    .ov {
        position: absolute; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; padding: 8px; box-sizing: border-box;
        background: rgba(5,4,12,.74); backdrop-filter: blur(3px);
    }
    .ov.show { display: flex; animation: ovIn .2s ease both; }
    #ovCard { z-index: 62; } #ovWin { z-index: 64; } #ovConfirm { z-index: 66; }
    @keyframes ovIn { from { opacity: 0; } to { opacity: 1; } }
    .ov-card {
        width: min(94cqw, 480px); max-height: 94cqh; overflow-y: auto; text-align: center; background: #14121f; border: 1px solid rgba(255,255,255,.16);
        border-radius: 1.3rem; padding: clamp(.7rem, 3.4cqh, 1.4rem); box-shadow: 0 24px 60px rgba(0,0,0,.6); animation: cardPop .3s cubic-bezier(.34,1.4,.64,1) both;
    }
    .ov-card.wide { width: min(94cqw, 640px); }
    @keyframes cardPop { from { opacity: 0; transform: scale(.88) translateY(12px); } to { opacity: 1; transform: none; } }
    .ov-icon { font-size: clamp(1.8rem, 11cqh, 3.4rem); line-height: 1; }
    .ov-title { font-size: clamp(.62rem, 2.5cqh, .8rem); font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin: .35rem 0 .2rem; }
    .ov-text { font-size: clamp(1rem, 5.4cqh, 1.7rem); font-weight: 900; line-height: 1.25; margin: .2rem 0 .6rem; }
    .ov-sub { font-size: clamp(.68rem, 2.6cqh, .85rem); color: #9ca3af; font-weight: 600; margin-bottom: .5rem; }
    .ov-btns { display: flex; gap: .5rem; justify-content: center; flex-wrap: wrap; margin-top: .4rem; }
    .obtn {
        border: none; cursor: pointer; font-weight: 900; font-size: clamp(.75rem, 3cqh, .95rem); padding: clamp(.45rem, 2cqh, .75rem) clamp(.8rem, 3cqh, 1.3rem); border-radius: .9rem; color: #fff;
        background: linear-gradient(135deg, #f59e0b, #ec4899);
    }
    .obtn.alt { background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.18); color: #e5e7eb; }
    .obtn.vio { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
    .obtn:disabled { opacity: .4; }
    .obtn:active { transform: scale(.96); }
    .tgt-row { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: center; margin: .3rem 0 .5rem; }
    .tgt {
        display: flex; align-items: center; gap: .4rem; padding: .3rem .7rem .3rem .3rem; border-radius: 999px; background: rgba(255,255,255,.07);
        border: 1.5px solid rgba(255,255,255,.16); font-weight: 800; font-size: clamp(.7rem, 2.8cqh, .9rem); cursor: pointer;
    }
    .tgt .av { width: clamp(24px, 8cqh, 34px); height: clamp(24px, 8cqh, 34px); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; font-size: 1rem; }
    .tgt:active { transform: scale(.95); }
    .tgt.static { cursor: default; padding-right: .5rem; }
    .autobar { height: 3px; border-radius: 2px; background: linear-gradient(90deg, #f59e0b, #ec4899); transform-origin: left; margin-top: .5rem; animation: shrink linear forwards; }
    @keyframes shrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    .code-big { font-size: clamp(1.6rem, 11cqh, 3rem); font-weight: 900; letter-spacing: .28em; padding-left: .28em; background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .rank { display: flex; align-items: center; gap: .5rem; padding: .25rem .5rem; border-radius: .7rem; background: rgba(255,255,255,.05); margin-bottom: .25rem; font-size: clamp(.68rem, 2.6cqh, .88rem); font-weight: 800; text-align: left; }
    .rank .n { width: 1.4em; color: #9ca3af; }
    .award { display: flex; align-items: center; gap: .5rem; font-size: clamp(.64rem, 2.4cqh, .8rem); font-weight: 700; text-align: left; padding: .2rem 0; }
    .htp { display: flex; align-items: flex-start; gap: .5rem; font-size: clamp(.64rem, 2.5cqh, .82rem); font-weight: 600; color: #d1d5db; padding: .2rem 0; text-align: left; }
    .htp b { color: #fff; }
    .floater { position: absolute; z-index: 45; font-size: clamp(1.4rem, 7cqh, 2.4rem); pointer-events: none; animation: floatUp 1.7s ease-out forwards; transform: translate(-50%, -50%); }
    @keyframes floatUp { 0% { opacity: 0; transform: translate(-50%, -30%) scale(.4); } 15% { opacity: 1; transform: translate(-50%, -70%) scale(1.15); } 100% { opacity: 0; transform: translate(-50%, -190%) scale(1.8); } }
    .confetti { position: absolute; top: -12px; width: 8px; height: 14px; z-index: 70; pointer-events: none; animation: confettiFall linear forwards; }
    @keyframes confettiFall { to { transform: translateY(120cqh) rotate(720deg); opacity: .9; } }
    .reactbar { display: none; flex-wrap: wrap; gap: 3px; max-width: 46cqw; }
    .reactbar.show { display: flex; }
    .reactbar button { font-size: clamp(.95rem, 4cqh, 1.3rem); border: 1px solid rgba(255,255,255,.15); background: rgba(8,7,16,.78); border-radius: .6rem; padding: .1rem .3rem; cursor: pointer; }
</style>
