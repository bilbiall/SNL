<style>
    html, body { height: 100%; margin: 0; overflow: hidden; overscroll-behavior: none; background: #0b0a16; }
    body { font-family: 'Figtree', system-ui, sans-serif; color: #fff; -webkit-tap-highlight-color: transparent; user-select: none; -webkit-user-select: none; }
    button { font-family: inherit; color: inherit; }

    /* ── The stage: always laid out as a LANDSCAPE screen. On a portrait phone the whole thing is rotated 90°
       (so every overlay, the die and the board stay upright relative to each other). Container units (cqw/cqh)
       let children size themselves off the stage, rotated or not. ── */
    .stage { position: fixed; inset: 0; overflow: hidden; container-type: size; background: #0b0a16; }
    @media (max-width: 1023px) and (orientation: portrait) {
        .stage {
            inset: auto; top: 0; left: 0; width: 100vh; height: 100vw; width: 100dvh; height: 100dvw;
            transform-origin: top left; transform: rotate(90deg) translateY(-100%);
        }
    }

    /* Atmosphere: the board's own art, blurred and dimmed, fills the space around the board. */
    .ambient { position: absolute; inset: -12%; background-size: cover; background-position: center; filter: blur(34px) saturate(1.5) brightness(.5); transform: scale(1.15); z-index: 0; animation: ambientDrift 40s ease-in-out infinite alternate; }
    @keyframes ambientDrift { from { transform: scale(1.15) translate(-1.5%, -1%); } to { transform: scale(1.25) translate(1.5%, 1%); } }
    .vignette { position: absolute; inset: 0; z-index: 1; pointer-events: none; background: radial-gradient(ellipse at center, transparent 45%, rgba(5,4,12,.72) 100%); }

    /* The board is exactly the art's size, so tile positions (% of the board) land on the right squares. */
    .board-wrap {
        position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 2;
        width: min(100cqw, calc(100cqh * var(--bw) / var(--bh))); aspect-ratio: var(--bw) / var(--bh);
        border-radius: .9rem; overflow: hidden; box-shadow: 0 0 0 2px rgba(255,255,255,.12), 0 0 60px rgba(245,158,11,.22), 0 30px 70px rgba(0,0,0,.6);
    }
    .board-wrap > img { width: 100%; height: 100%; display: block; object-fit: fill; pointer-events: none; -webkit-user-drag: none; }

    /* ── Tokens: glossy, with a bobbing marker over whoever's turn it is ── */
    .token {
        --d: clamp(22px, 5.6cqh, 42px);
        position: absolute; width: var(--d); height: var(--d); border-radius: 50%; border: 3px solid #fff; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: calc(var(--d) * .56); line-height: 1;
        background: radial-gradient(circle at 30% 24%, rgba(255,255,255,.55), transparent 46%), var(--c, #f59e0b);
        transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px)));
        box-shadow: 0 5px 10px rgba(0,0,0,.6), 0 0 0 2px var(--c, #f59e0b); z-index: 20; transition: left .22s linear, top .22s linear;
    }
    .token .ic { filter: drop-shadow(0 1px 1px rgba(0,0,0,.5)); }
    .avimg { width: 100%; height: 100%; display: block; border-radius: 50%; object-fit: cover; image-rendering: pixelated; }
    .token .avimg { position: absolute; inset: 0; }
    .splash .avimg { width: 1.5em; height: 1.5em; flex-shrink: 0; }
    .token.glide { transition: left 1s cubic-bezier(.34,1.2,.64,1), top 1s cubic-bezier(.34,1.2,.64,1); }
    .token.active { z-index: 25; }
    .token.active::before { content: ''; position: absolute; top: -.62em; left: 50%; width: 0; height: 0; border: .38em solid transparent; border-top: .5em solid #fff; border-bottom: 0; transform: translateX(-50%); filter: drop-shadow(0 2px 3px rgba(0,0,0,.6)); animation: markerBob .8s ease-in-out infinite; }
    @keyframes markerBob { 0%,100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(-5px); } }
    .token.gone { display: none; }
    .token .tname { display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); margin-top: 2px; font-size: calc(var(--d) * .3); font-weight: 900; white-space: nowrap; padding: 0 .4em; border-radius: .4em; background: rgba(8,7,16,.8); pointer-events: none; }
    .token.active .tname { display: block; }
    .token.shieldfx { filter: drop-shadow(0 0 8px #38bdf8) drop-shadow(0 0 16px #38bdf8); }
    .token.landed { animation: tokenLand .45s ease; }
    @keyframes tokenLand {
        0%, 100% { transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px))) scale(1); }
        40% { transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px))) scale(1.35, .75); }
        70% { transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px))) scale(.9, 1.15); }
    }
    .ripple { position: absolute; width: 4%; aspect-ratio: 1; border-radius: 50%; border: 3px solid rgba(255,255,255,.9); transform: translate(-50%,-50%) scale(.4); pointer-events: none; z-index: 18; animation: rippleOut .9s ease-out forwards; }
    @keyframes rippleOut { to { transform: translate(-50%,-50%) scale(2.6); opacity: 0; border-width: 1px; } }
    .tile-glow { --d: clamp(22px, 5.6cqh, 42px); position: absolute; width: calc(var(--d) * 1.2); height: calc(var(--d) * 1.2); border-radius: 50%; transform: translate(-50%,-50%); pointer-events: none; z-index: 15; opacity: 0; transition: opacity .25s; }
    .tile-glow.show { opacity: 1; animation: glowPulse 1.5s ease-in-out infinite; }
    @keyframes glowPulse { 0%,100% { box-shadow: 0 0 10px 4px rgba(245,158,11,.5); } 50% { box-shadow: 0 0 20px 9px rgba(245,158,11,.85); } }
    .rollpop {
        position: absolute; z-index: 46; pointer-events: none; transform: translate(-50%,-50%); font-weight: 900; font-size: clamp(1.3rem, 7cqh, 2.3rem); color: #fff;
        text-shadow: 0 0 14px var(--c, #f59e0b), 0 3px 8px rgba(0,0,0,.8); animation: rollPop 1.2s ease-out forwards; white-space: nowrap; display: flex; align-items: center; gap: .25em;
    }
    @keyframes rollPop { 0% { opacity: 0; transform: translate(-50%,-20%) scale(.3); } 25% { opacity: 1; transform: translate(-50%,-90%) scale(1.25); } 100% { opacity: 0; transform: translate(-50%,-220%) scale(1); } }

    /* ── HUD ── */
    .hud { position: absolute; z-index: 30; pointer-events: none; }
    .hud > * { pointer-events: auto; }
    .hud-tl { top: 6px; left: 6px; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; max-width: min(34cqw, 230px); }
    .hud-tr { top: 6px; right: 6px; display: flex; gap: 5px; }
    .hud-left { left: 6px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: clamp(3px, 1.3cqh, 7px); max-height: 66cqh; }
    .hud-br { right: 8px; bottom: 8px; display: flex; flex-direction: column; align-items: center; gap: 5px; width: clamp(92px, 26cqh, 150px); }
    .hud-bl { left: 6px; bottom: 6px; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
    .chip {
        display: inline-flex; align-items: center; gap: .35rem; padding: .22rem .6rem; border-radius: 999px; font-size: clamp(.6rem, 2.3cqh, .74rem); font-weight: 800;
        background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.14); backdrop-filter: blur(6px); white-space: nowrap; color: #fff;
    }
    .chip .ic { opacity: .9; }
    .chip.rule { white-space: normal; line-height: 1.25; border-radius: .8rem; background: rgba(168,85,247,.28); border-color: rgba(168,85,247,.55); max-width: 100%; cursor: pointer; text-align: left; }
    .icon-btn {
        width: clamp(28px, 8.5cqh, 38px); height: clamp(28px, 8.5cqh, 38px); border-radius: 50%; border: 1px solid rgba(255,255,255,.16);
        background: rgba(8,7,16,.72); backdrop-filter: blur(6px); cursor: pointer; font-size: clamp(.95rem, 3.6cqh, 1.25rem);
        display: inline-flex; align-items: center; justify-content: center; color: #fff;
    }
    .icon-btn:active { transform: scale(.92); }

    .pl { display: flex; align-items: center; gap: 6px; padding: 2px 8px 2px 2px; border-radius: 999px; background: linear-gradient(135deg, rgba(20,18,36,.85), rgba(8,7,16,.7)); border: 1.5px solid rgba(255,255,255,.1); backdrop-filter: blur(6px); box-shadow: 0 4px 12px rgba(0,0,0,.35); transition: all .2s; }
    .pl .ring { --d: clamp(26px, 7.4cqh, 40px); width: var(--d); height: var(--d); border-radius: 50%; padding: 3px; box-sizing: border-box; flex-shrink: 0; background: conic-gradient(var(--c) calc(var(--p) * 1%), rgba(255,255,255,.16) 0); }
    .pl .ring .av { width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 30%, #14121f); font-size: calc(var(--d) * .5); color: #fff; }
    .pl .info { display: none; flex-direction: column; gap: 2px; min-width: 0; }
    .pl .nm { font-size: clamp(.55rem, 2.1cqh, .7rem); font-weight: 800; max-width: 12cqw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pl .bar { height: 4px; width: 9cqw; max-width: 90px; border-radius: 3px; background: rgba(255,255,255,.14); overflow: hidden; }
    .pl .bar b { display: block; height: 100%; background: var(--c); border-radius: 3px; transition: width .6s ease; }
    .pl .pw { display: none; font-size: clamp(.52rem, 2cqh, .64rem); font-weight: 900; color: #7dd3fc; gap: 3px; align-items: center; }
    .pl .pw span { display: inline-flex; align-items: center; gap: 1px; }
    .pl.cur { box-shadow: 0 0 0 1px var(--c), 0 0 18px color-mix(in srgb, var(--c) 55%, transparent); background: linear-gradient(135deg, color-mix(in srgb, var(--c) 28%, #14121f), rgba(8,7,16,.8)); border-color: var(--c); transform: translateX(4px); }
    .pl.cur .info, .pl.cur .pw { display: flex; }
    .pl.out { opacity: .35; }
    @media (min-width: 720px) and (min-height: 380px) { .pl .info, .pl .pw { display: flex; } }

    /* ── Turn banner, die, powers ── */
    .turn-banner { font-size: clamp(.58rem, 2.2cqh, .74rem); font-weight: 900; text-align: center; padding: .3rem .55rem; border-radius: .8rem; width: 100%; box-sizing: border-box; line-height: 1.25; backdrop-filter: blur(6px); border-left: 4px solid var(--c, rgba(255,255,255,.2)); }
    .turn-banner.mine { background: rgba(16,185,129,.22); border: 1px solid rgba(16,185,129,.55); border-left: 4px solid var(--c, #10b981); color: #6ee7b7; }
    .turn-banner.wait { background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.15); border-left: 4px solid var(--c, rgba(255,255,255,.2)); color: #e5e7eb; }
    .turn-banner .secs { display: inline-flex; min-width: 1.3em; justify-content: center; font-variant-numeric: tabular-nums; }
    .turn-banner .secs.low { color: #f87171; }
    .dot { display: inline-block; width: .7em; height: .7em; border-radius: 50%; background: var(--c); box-shadow: 0 0 6px var(--c); vertical-align: -.05em; }

    .die-badge {
        position: relative; width: clamp(64px, 20cqh, 112px); height: clamp(64px, 20cqh, 112px); border-radius: 50%; border: none; cursor: pointer; padding: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .15rem;
        background: rgba(8,7,16,.7); backdrop-filter: blur(6px);
        box-shadow: 0 0 0 1.5px rgba(255,255,255,.18), 0 10px 26px rgba(0,0,0,.45); transition: transform .15s, filter .2s;
    }
    .die-badge:not(:disabled) { box-shadow: 0 0 0 2px rgba(245,158,11,.75), 0 0 22px rgba(245,158,11,.45), 0 10px 26px rgba(0,0,0,.45); animation: dieReady 1.6s ease-in-out infinite; }
    .die-badge:disabled { filter: grayscale(.8) brightness(.65); cursor: not-allowed; }
    .die-badge:active:not(:disabled) { transform: scale(.94); }
    .die-badge.tossing { animation: dieToss .62s ease-in-out; }
    @keyframes dieReady { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    @keyframes dieToss { 0% { transform: translateY(0) rotate(0); } 30% { transform: translateY(-16px) rotate(-14deg) scale(1.1); } 60% { transform: translateY(0) rotate(10deg); } 80% { transform: translateY(-6px) rotate(-4deg); } 100% { transform: none; } }
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
    .die-val { position: absolute; top: -3px; right: -3px; width: clamp(20px, 6cqh, 28px); height: clamp(20px, 6cqh, 28px); border-radius: 50%; background: #1a1a2e; border: 2px solid #fbbf24; color: #fbbf24; font-weight: 900; font-size: clamp(.65rem, 2.6cqh, .85rem); display: none; align-items: center; justify-content: center; }
    .die-val.show { display: flex; }
    .powers { display: flex; gap: 5px; }
    .pwr { display: flex; align-items: center; gap: 4px; padding: .2rem .55rem; border-radius: 999px; font-size: clamp(.6rem, 2.3cqh, .78rem); font-weight: 900; background: rgba(8,7,16,.72); border: 1px solid rgba(255,255,255,.16); backdrop-filter: blur(6px); }
    .pwr .ic { color: #7dd3fc; font-size: 1.15em; }
    .pwr.empty { opacity: .4; }

    /* ── Toasts ── */
    .toasts { position: absolute; left: 50%; bottom: 7cqh; transform: translateX(-50%); z-index: 40; display: flex; flex-direction: column; align-items: center; gap: 5px; pointer-events: none; width: min(80cqw, 460px); }
    .toast {
        padding: .42rem .9rem; border-radius: .9rem; font-size: clamp(.66rem, 2.5cqh, .85rem); font-weight: 800; text-align: center; max-width: 100%; border-left: 4px solid var(--c, rgba(255,255,255,.35));
        background: rgba(8,7,16,.9); border-top: 1px solid rgba(255,255,255,.14); border-right: 1px solid rgba(255,255,255,.14); border-bottom: 1px solid rgba(255,255,255,.14);
        box-shadow: 0 8px 22px rgba(0,0,0,.5); animation: toastIn .25s ease both;
    }
    .toast .ic { color: var(--c, #fff); font-size: 1.15em; }
    .toast.out { animation: toastOut .3s ease both; }
    @keyframes toastIn { from { opacity: 0; transform: translateY(8px) scale(.96); } to { opacity: 1; transform: none; } }
    @keyframes toastOut { to { opacity: 0; transform: translateY(-6px); } }

    /* Turn splash */
    .splash { position: absolute; left: 0; right: 0; top: 38%; z-index: 48; text-align: center; pointer-events: none; animation: splashIn 1.25s cubic-bezier(.2,.9,.3,1) forwards; }
    .splash b { display: inline-flex; align-items: center; gap: .5em; padding: .35em 1.2em; font-size: clamp(.95rem, 5.4cqh, 1.7rem); font-weight: 900; color: #fff; background: linear-gradient(90deg, transparent, rgba(8,7,16,.9) 16%, rgba(8,7,16,.9) 84%, transparent); border-top: 2px solid var(--c); border-bottom: 2px solid var(--c); text-shadow: 0 2px 8px rgba(0,0,0,.6); }
    .splash .ic { color: var(--c); font-size: 1.2em; }
    @keyframes splashIn { 0% { opacity: 0; transform: translateX(-30%) scale(.9); } 22% { opacity: 1; transform: none; } 78% { opacity: 1; } 100% { opacity: 0; transform: translateX(30%); } }

    /* ── Dialogs: standard, compact sizes ── */
    .ov { position: absolute; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; padding: 8px; box-sizing: border-box; background: rgba(5,4,12,.72); backdrop-filter: blur(3px); }
    .ov.show { display: flex; animation: ovIn .2s ease both; }
    #ovCard { z-index: 62; } #ovWin { z-index: 64; } #ovConfirm { z-index: 66; }
    @keyframes ovIn { from { opacity: 0; } to { opacity: 1; } }
    .ov-card {
        width: min(86cqw, 340px); max-height: 92cqh; overflow-y: auto; text-align: center; border-radius: 1.1rem; padding: clamp(.6rem, 2.8cqh, 1rem);
        background: linear-gradient(165deg, #211d36, #14121f 60%); border: 1px solid rgba(255,255,255,.18); box-shadow: 0 20px 50px rgba(0,0,0,.6); animation: cardPop .28s cubic-bezier(.34,1.4,.64,1) both;
    }
    .ov-card.wide { width: min(92cqw, 430px); }
    @keyframes cardPop { from { opacity: 0; transform: scale(.9) translateY(10px); } to { opacity: 1; transform: none; } }
    .ov-icon { width: clamp(34px, 12cqh, 52px); height: clamp(34px, 12cqh, 52px); border-radius: 50%; margin: 0 auto .3rem; display: flex; align-items: center; justify-content: center; font-size: clamp(1.1rem, 6.4cqh, 1.7rem); background: rgba(255,255,255,.07); border: 2px solid var(--k, rgba(255,255,255,.25)); color: var(--k, #fff); }
    .ov-title { font-size: clamp(.58rem, 2.2cqh, .72rem); font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; margin: .25rem 0 .15rem; }
    .ov-text { font-size: clamp(.9rem, 4.2cqh, 1.25rem); font-weight: 900; line-height: 1.25; margin: .15rem 0 .45rem; }
    .ov-sub { font-size: clamp(.62rem, 2.4cqh, .8rem); color: #9ca3af; font-weight: 600; margin-bottom: .4rem; }
    .ov-btns { display: flex; gap: .45rem; justify-content: center; flex-wrap: wrap; margin-top: .35rem; }
    .obtn {
        display: inline-flex; align-items: center; justify-content: center; gap: .4em; border: none; cursor: pointer; font-weight: 900; font-size: clamp(.7rem, 2.8cqh, .88rem);
        padding: clamp(.4rem, 1.8cqh, .6rem) clamp(.8rem, 3cqh, 1.15rem); border-radius: .8rem; color: #fff; background: linear-gradient(135deg, #f59e0b, #ec4899); text-decoration: none;
    }
    .obtn.alt { background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.18); color: #e5e7eb; }
    .obtn.vio { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
    .obtn:disabled { opacity: .4; }
    .obtn:active { transform: scale(.96); }
    .tgt-row { display: flex; flex-wrap: wrap; gap: .35rem; justify-content: center; margin: .25rem 0 .4rem; }
    .tgt { display: flex; align-items: center; gap: .4rem; padding: .22rem .65rem .22rem .22rem; border-radius: 999px; background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.16); font-weight: 800; font-size: clamp(.66rem, 2.6cqh, .84rem); cursor: pointer; color: #fff; }
    .tgt .av { width: clamp(22px, 7cqh, 30px); height: clamp(22px, 7cqh, 30px); border-radius: 50%; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 45%, #14121f); border: 2px solid var(--c); font-size: .95rem; }
    .tgt:active { transform: scale(.95); }
    .tgt.static { cursor: default; padding-right: .5rem; }
    .autobar { height: 3px; border-radius: 2px; background: linear-gradient(90deg, #f59e0b, #ec4899); transform-origin: left; margin-top: .5rem; animation: shrink linear forwards; }
    @keyframes shrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    .code-big { font-size: clamp(1.5rem, 9cqh, 2.4rem); font-weight: 900; letter-spacing: .28em; padding-left: .28em; background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .rank { display: flex; align-items: center; gap: .5rem; padding: .22rem .5rem; border-radius: .7rem; background: rgba(255,255,255,.05); margin-bottom: .22rem; font-size: clamp(.64rem, 2.5cqh, .82rem); font-weight: 800; text-align: left; }
    .rank .n { width: 1.3em; color: #9ca3af; }
    .award { display: flex; align-items: center; gap: .5rem; font-size: clamp(.6rem, 2.3cqh, .76rem); font-weight: 700; text-align: left; padding: .18rem 0; }
    .award .ic { color: #fbbf24; font-size: 1.3em; }
    .htp { display: flex; align-items: flex-start; gap: .5rem; font-size: clamp(.62rem, 2.4cqh, .78rem); font-weight: 600; color: #d1d5db; padding: .18rem 0; text-align: left; }
    .htp .ic { font-size: 1.25em; margin-top: .05em; color: #fbbf24; }
    .htp b { color: #fff; }

    /* ── The card pop-up: a standard playing-card shape (5:7), tinted by kind ── */
    .pcard {
        --k: #f59e0b; position: relative; width: min(60cqw, 244px); aspect-ratio: 5 / 7; max-height: 90cqh; display: flex; flex-direction: column; overflow: hidden; text-align: center;
        border-radius: 1rem; background: linear-gradient(165deg, #221e38, #12101e 62%); border: 2px solid var(--k);
        box-shadow: 0 0 0 4px rgba(255,255,255,.04), 0 0 34px color-mix(in srgb, var(--k) 42%, transparent), 0 22px 50px rgba(0,0,0,.65);
        animation: cardFlip .55s cubic-bezier(.2,.9,.3,1.15) both;
    }
    @keyframes cardFlip { from { opacity: 0; transform: perspective(900px) rotateY(-100deg) scale(.8); } to { opacity: 1; transform: none; } }
    .pcard::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 50% -8%, color-mix(in srgb, var(--k) 36%, transparent), transparent 55%); pointer-events: none; }
    .pcard .pc-head { position: relative; padding: .35rem .4rem; font-size: clamp(.5rem, 2cqh, .66rem); font-weight: 900; letter-spacing: .14em; text-transform: uppercase; color: #fff; background: linear-gradient(135deg, var(--k), color-mix(in srgb, var(--k) 55%, #000)); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pcard .pc-body { position: relative; flex: 1; min-height: 0; display: flex; flex-direction: column; align-items: center; justify-content: space-between; gap: .3rem; padding: clamp(.4rem, 2cqh, .8rem) clamp(.5rem, 2.4cqh, .9rem) clamp(.4rem, 2cqh, .7rem); }
    .pcard .pc-icon { width: clamp(38px, 15cqh, 62px); height: clamp(38px, 15cqh, 62px); flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: clamp(1.3rem, 7.4cqh, 2.2rem); color: var(--k); background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.22), transparent 55%), color-mix(in srgb, var(--k) 22%, #14121f); border: 2.5px solid var(--k); box-shadow: 0 0 20px color-mix(in srgb, var(--k) 55%, transparent); animation: iconPulse 1.8s ease-in-out infinite; }
    @keyframes iconPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.07); } }
    .pcard .pc-mid { flex: 1; min-height: 0; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow-y: auto; }
    .pcard .pc-text { font-size: clamp(.78rem, 3.5cqh, 1.05rem); font-weight: 800; line-height: 1.25; text-shadow: 0 2px 8px rgba(0,0,0,.4); }
    .pcard .tgt-row { margin: 0 0 .3rem; }
    .pcard .tgt { font-size: clamp(.58rem, 2.2cqh, .74rem); }
    .pcard .ov-sub { margin: .25rem 0 0; font-size: clamp(.54rem, 2cqh, .68rem); }
    .pcard .ov-btns { margin: 0; }
    .pcard .obtn { padding: clamp(.3rem, 1.4cqh, .45rem) clamp(.8rem, 3cqh, 1.1rem); font-size: clamp(.64rem, 2.5cqh, .8rem); }
    .pcard .spark { position: absolute; color: var(--k); font-size: clamp(.7rem, 3cqh, 1rem); opacity: .9; animation: twinkle 1.6s ease-in-out infinite; pointer-events: none; }
    @keyframes twinkle { 0%,100% { transform: scale(.6) rotate(0); opacity: .3; } 50% { transform: scale(1.15) rotate(25deg); opacity: 1; } }

    .floater { position: absolute; z-index: 45; font-size: clamp(1.3rem, 6.5cqh, 2.2rem); pointer-events: none; animation: floatUp 1.7s ease-out forwards; transform: translate(-50%, -50%); filter: drop-shadow(0 3px 6px rgba(0,0,0,.6)); color: var(--c, #fbbf24); }
    @keyframes floatUp { 0% { opacity: 0; transform: translate(-50%, -30%) scale(.4); } 15% { opacity: 1; transform: translate(-50%, -70%) scale(1.15); } 100% { opacity: 0; transform: translate(-50%, -190%) scale(1.8); } }
    .confetti { position: absolute; top: -12px; width: 8px; height: 14px; z-index: 70; pointer-events: none; animation: confettiFall linear forwards; }
    @keyframes confettiFall { to { transform: translateY(120cqh) rotate(720deg); opacity: .9; } }
    .reactbar { display: none; flex-wrap: wrap; gap: 3px; max-width: 46cqw; }
    .reactbar.show { display: flex; }
    .reactbar button { font-size: clamp(.95rem, 4cqh, 1.25rem); border: 1px solid rgba(255,255,255,.15); background: rgba(8,7,16,.8); border-radius: .6rem; padding: .22rem .38rem; cursor: pointer; color: #fff; display: inline-flex; }
    .reactbar button:active { transform: scale(.88); }
</style>
