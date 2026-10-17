<style>
    html, body { height: 100%; margin: 0; overflow: hidden; overscroll-behavior: none; background: #0b0a16; }
    body { font-family: 'Figtree', system-ui, sans-serif; color: #fff; -webkit-tap-highlight-color: transparent; user-select: none; -webkit-user-select: none; }
    button { font-family: inherit; color: inherit; }

    /* ── The stage: always laid out as a LANDSCAPE screen. On a portrait phone the whole thing is rotated 90°
       (so every overlay, the die and the board stay upright relative to each other). Container units (cqw/cqh)
       let children size themselves off the stage, rotated or not. ── */
    .stage { position: fixed; inset: 0; overflow: hidden; container-type: size; background: #0b0a16; --nav: clamp(30px, 8.6cqh, 52px); }
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
        position: absolute; left: 50%; top: calc(50% + var(--nav) / 2); transform: translate(-50%, -50%); z-index: 2;
        width: min(100cqw, calc((100cqh - var(--nav)) * var(--bw) / var(--bh))); aspect-ratio: var(--bw) / var(--bh);
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
    .avimg { width: 100%; height: 100%; display: block; border-radius: 50%; object-fit: cover; }
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
    /* ── Top nav: logo, room info, sound / music / menu ── */
    .topnav { position: absolute; top: 0; left: 0; right: 0; height: var(--nav); z-index: 40; display: flex; align-items: center; justify-content: space-between; gap: .6rem; padding: 0 clamp(8px, 1.6cqw, 18px);
        background: linear-gradient(180deg, rgba(10,8,22,.92), rgba(10,8,22,.62)); border-bottom: 1px solid rgba(255,255,255,.1); backdrop-filter: blur(10px); box-shadow: 0 6px 24px rgba(0,0,0,.35); }
    .topnav::after { content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px; background: linear-gradient(90deg, transparent, #f59e0b, #ec4899, #8b5cf6, transparent); opacity: .8; }
    .brand { display: inline-flex; align-items: center; gap: .5rem; text-decoration: none; color: #fff; min-width: 0; }
    .brand img { height: calc(var(--nav) - 10px); width: auto; flex-shrink: 0; filter: drop-shadow(0 2px 6px rgba(236,72,153,.5)); }
    .brand span { font-weight: 800; font-size: clamp(.78rem, 3.4cqh, 1.05rem); letter-spacing: .02em; white-space: nowrap; }
    .brand b { font-weight: 900; background: linear-gradient(90deg, #fbbf24, #f472b6); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .nav-mid { display: flex; align-items: center; gap: .4rem; min-width: 0; overflow: hidden; }
    .nav-btns { display: inline-flex; gap: clamp(4px, 1cqw, 9px); align-items: center; flex-shrink: 0; }
    .topnav .icon-btn { width: calc(var(--nav) - 12px); height: calc(var(--nav) - 12px); font-size: clamp(.95rem, 4cqh, 1.3rem); }
    .icon-btn.off { opacity: .45; }
    @media (max-width: 640px) { .brand span { display: none; } }
    .hud { position: absolute; z-index: 30; pointer-events: none; }
    .hud > * { pointer-events: auto; }
    .hud-tl { top: calc(var(--nav) + 6px); left: 6px; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; max-width: min(34cqw, 230px); }
    .hud-tr { top: 6px; right: 6px; display: flex; gap: 5px; }
    .hud-left { left: 6px; top: calc(50% + var(--nav) / 2); transform: translateY(-50%); display: flex; flex-direction: column; gap: clamp(3px, 1.3cqh, 7px); max-height: 66cqh; }
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

    /* Turn splash — slow and readable: the avatar pops in, then bobs while the banner holds */
    .splash { position: absolute; left: 0; right: 0; top: 36%; z-index: 48; text-align: center; pointer-events: none; animation: splashIn 3.2s cubic-bezier(.22,1,.36,1) forwards; }
    .splash b { display: inline-flex; align-items: center; gap: .7em; padding: .4em 1.6em .4em .5em; font-size: clamp(1rem, 5.2cqh, 1.6rem); font-weight: 900; color: #fff; background: linear-gradient(90deg, transparent, rgba(8,7,16,.92) 14%, rgba(8,7,16,.92) 86%, transparent); border-top: 2px solid var(--c); border-bottom: 2px solid var(--c); text-shadow: 0 2px 8px rgba(0,0,0,.6); }
    .splash .spl-av { width: 2.3em; height: 2.3em; flex-shrink: 0; border-radius: 50%; border: 3px solid var(--c); overflow: hidden; display: flex; align-items: center; justify-content: center; background: #14121f; font-size: 1em; box-shadow: 0 0 22px color-mix(in srgb, var(--c) 70%, transparent); animation: splAvIn 1.1s cubic-bezier(.34,1.56,.64,1) .15s both, avBob 1.5s ease-in-out 1.3s infinite; }
    .splash .spl-av .avimg { width: 100%; height: 100%; }
    /* flavour banner: same spot and shape as the turn banner, but stays longer and reveals word by word */
    .splash.flavor { animation-duration: 4.4s; top: 34%; }
    .splash.flavor b { position: relative; overflow: hidden; padding-right: 2.2em; max-width: 92cqw; text-align: left; font-size: clamp(.9rem, 4.6cqh, 1.45rem); }
    .splash.flavor b::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.22) 50%, transparent 60%); background-size: 260% 100%; animation: bannerSheen 2.4s .5s ease-in-out infinite; pointer-events: none; }
    @keyframes bannerSheen { from { background-position: 140% 0; } to { background-position: -60% 0; } }
    .splash.flavor .spl-tx { display: flex; flex-direction: column; gap: .1em; align-items: flex-start; }
    .splash.flavor .spl-tx small { font-size: .5em; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; color: var(--c); display: inline-flex; align-items: center; gap: .4em; }
    .splash.flavor .spl-tx small .ic { animation: flavorSpin 2.4s linear infinite; }
    @keyframes flavorSpin { 0% { transform: rotate(0); } 40% { transform: rotate(180deg); } 100% { transform: rotate(180deg); } }
    .splash.flavor .fl-t { line-height: 1.2; }
    .splash.flavor .fl-t .w { display: inline-block; opacity: 0; animation: wordIn .6s calc(.7s + var(--i) * .1s) cubic-bezier(.34,1.56,.64,1) both; }
    .splash .spl-tx { animation: splTxIn .9s cubic-bezier(.22,1,.36,1) .35s both; }
    @keyframes splashIn { 0% { opacity: 0; transform: translateX(-16%) scale(.97); } 12% { opacity: 1; transform: none; } 88% { opacity: 1; transform: none; } 100% { opacity: 0; transform: translateX(16%); } }
    @keyframes splAvIn { 0% { transform: scale(0) rotate(-200deg); } 100% { transform: scale(1) rotate(0); } }
    @keyframes splTxIn { from { opacity: 0; transform: translateX(-14px); } to { opacity: 1; transform: none; } }
    @keyframes avBob { 0%, 100% { transform: translateY(0) rotate(-3deg); } 50% { transform: translateY(-5px) rotate(3deg); } }

    /* Whoever is playing keeps a little life: ringed avatar breathes, the token bobs on the board */
    .pl.cur .ring { animation: ringPulse 1.6s ease-in-out infinite; }
    .pl.cur .ring .av { animation: avBob 1.6s ease-in-out infinite; }
    @keyframes ringPulse { 0%, 100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--c) 60%, transparent); } 50% { box-shadow: 0 0 0 7px transparent; } }
    .token.active { animation: tokIdle 1.2s ease-in-out infinite; }
    @keyframes tokIdle {
        0%, 100% { transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px))) scale(1); }
        50% { transform: translate(calc(-50% + var(--ox, 0px)), calc(-50% + var(--oy, 0px) - 7px)) scale(1.08); }
    }

    /* ── Dialogs: standard, compact sizes ── */
    .ov { position: absolute; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; padding: 8px; box-sizing: border-box; background: rgba(5,4,12,.30); backdrop-filter: blur(1.5px); }
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
    /* designed-card extras: foil border, corner indices, watermark, pips, footer, shine sweep */
    .pcard { animation: cardDeal .7s cubic-bezier(.2,.9,.3,1.1) both, cardFloat 4.2s 1.2s ease-in-out infinite; transform-style: preserve-3d; }
    @keyframes cardFloat { 0%, 100% { transform: translateY(0) rotate(-.6deg); } 50% { transform: translateY(-5px) rotate(.6deg); } }
    @keyframes cardDeal { 0% { opacity: 0; transform: perspective(900px) translateY(40%) rotateX(35deg) rotateY(-120deg) scale(.6); } 60% { opacity: 1; transform: perspective(900px) rotateY(8deg) scale(1.04); } 100% { opacity: 1; transform: none; } }
    .pcard::after { content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; z-index: 3;
        background: linear-gradient(115deg, transparent 38%, rgba(255,255,255,.22) 50%, transparent 62%); background-size: 250% 100%; background-position: 130% 0; animation: foilSweep 1.1s .55s ease-out 1 both, foilIdle 5s 2s linear infinite; }
    @keyframes foilSweep { to { background-position: -60% 0; } }
    @keyframes foilIdle { 0%, 70% { background-position: 130% 0; } 100% { background-position: -60% 0; } }
    .pcard { background: linear-gradient(165deg, rgba(38,32,64,.66), rgba(16,14,28,.56) 62%); border: 2.5px solid color-mix(in srgb, var(--k) 80%, #fff); -webkit-backdrop-filter: blur(7px) saturate(1.2); backdrop-filter: blur(7px) saturate(1.2); box-shadow: inset 0 0 0 1px rgba(255,255,255,.12), 0 0 30px color-mix(in srgb, var(--k) 38%, transparent), 0 16px 40px rgba(0,0,0,.45); }
    .pcard .pc-mark { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: min(46cqw, 130px); color: var(--k); opacity: .08; pointer-events: none; transform: rotate(-12deg); }
    .pcard .pc-corner { position: absolute; z-index: 2; font-size: clamp(.7rem, 3.2cqh, 1rem); color: #fff; opacity: .9; line-height: 1; }
    .pcard .pc-corner.tl { top: .42rem; left: .45rem; }
    .pcard .pc-corner.br { bottom: .42rem; right: .45rem; transform: rotate(180deg); }
    .pcard .pc-head { text-align: center; padding-inline: 1.5rem; }
    .pcard .pc-foot { display: flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; font-size: clamp(.4rem, 1.5cqh, .52rem); font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.35); }
    .pcard .pc-pips { display: inline-flex; gap: 3px; }
    .pcard .pc-pips i { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.18); }
    .pcard .pc-pips i.on { background: var(--k); box-shadow: 0 0 6px var(--k); }
    /* motion layer: spinning rays, rising embers, word-by-word text, popping avatar chips, ringing icon */
    .pcard .pc-rays { position: absolute; left: 50%; top: 34%; width: 190%; aspect-ratio: 1; margin: -95% 0 0 -95%; pointer-events: none; opacity: .2; background: repeating-conic-gradient(from 0deg, var(--k) 0 7deg, transparent 7deg 22deg); -webkit-mask-image: radial-gradient(circle, #000 0, transparent 52%); mask-image: radial-gradient(circle, #000 0, transparent 52%); animation: raySpin 22s linear infinite; }
    @keyframes raySpin { to { transform: rotate(360deg); } }
    .pcard .ember { position: absolute; bottom: -6px; width: 5px; height: 5px; border-radius: 50%; background: var(--k); box-shadow: 0 0 8px var(--k); opacity: 0; pointer-events: none; animation: emberRise calc(2.6s / var(--s)) var(--d) ease-out infinite; }
    @keyframes emberRise { 0% { opacity: 0; transform: translateY(0) scale(.5); } 15% { opacity: .9; } 100% { opacity: 0; transform: translateY(-120cqh) translateX(14px) scale(1.2); } }
    .pcard .pc-head { animation: headDrop .6s .15s cubic-bezier(.22,1,.36,1) both; background-size: 220% 100%; }
    @keyframes headDrop { from { opacity: 0; transform: translateY(-100%); } to { opacity: 1; transform: none; } }
    .pcard .pc-icon { animation: iconPop .9s .25s cubic-bezier(.34,1.56,.64,1) both, iconPulse 1.8s 1.2s ease-in-out infinite; }
    @keyframes iconPop { 0% { transform: scale(0) rotate(-220deg); } 100% { transform: scale(1) rotate(0); } }
    .pcard .pc-icon::before, .pcard .pc-icon::after { content: ''; position: absolute; inset: -3px; border-radius: 50%; border: 2px solid var(--k); opacity: 0; animation: ringOut 2.2s 1s ease-out infinite; }
    .pcard .pc-icon::after { animation-delay: 1.8s; }
    .pcard .pc-icon { position: relative; }
    @keyframes ringOut { 0% { transform: scale(1); opacity: .7; } 100% { transform: scale(1.9); opacity: 0; } }
    .pcard .pc-text .w { display: inline-block; opacity: 0; animation: wordIn .55s calc(.55s + var(--i) * .07s) cubic-bezier(.34,1.56,.64,1) both; }
    @keyframes wordIn { from { opacity: 0; transform: translateY(.7em) scale(.6) rotate(-4deg); filter: blur(4px); } to { opacity: 1; transform: none; filter: none; } }
    .pcard .tgt-row .tgt { animation: chipPop .7s .35s cubic-bezier(.34,1.56,.64,1) both; }
    .pcard .tgt-row .tgt:nth-child(2) { animation-delay: .5s; } .pcard .tgt-row .tgt:nth-child(3) { animation-delay: .65s; }
    @keyframes chipPop { from { opacity: 0; transform: translateY(14px) scale(.4); } to { opacity: 1; transform: none; } }
    .pcard .tgt-row .tgt .av { animation: avBob 1.6s 1.2s ease-in-out infinite; }
    .pcard .pc-pips i { animation: pipPop .4s calc(.9s + var(--p, 0) * .12s) cubic-bezier(.34,1.56,.64,1) both; }
    .pcard .pc-pips i:nth-child(2) { --p: 1; } .pcard .pc-pips i:nth-child(3) { --p: 2; }
    @keyframes pipPop { from { transform: scale(0); } to { transform: scale(1); } }
    .pcard .ov-btns { animation: chipPop .5s 1.1s ease-out both; }
    .pcard .ov-sub { animation: chipPop .6s 1s ease-out both; }
    @media (prefers-reduced-motion: reduce) { .pcard, .pcard * { animation-duration: .01s !important; animation-delay: 0s !important; animation-iteration-count: 1 !important; } }
    .rw { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: center; }
    .rw .tgt { opacity: .45; transform: scale(.92); transition: all .12s; }
    .rw .tgt.hot { opacity: 1; transform: scale(1.12); border-color: var(--c); box-shadow: 0 0 16px var(--c); }
    .rw .tgt.won { animation: none; transform: scale(1.22); }
    .netbar { position: absolute; left: 50%; top: calc(var(--nav) + 8px); transform: translateX(-50%); z-index: 50; display: flex; align-items: center; gap: .7rem; padding: .45rem .5rem .45rem .9rem; border-radius: 999px; background: rgba(120,20,20,.92); border: 1px solid rgba(248,113,113,.7); font-weight: 800; font-size: clamp(.64rem, 2.4cqh, .82rem); box-shadow: 0 10px 28px rgba(0,0,0,.5); animation: chipPop .4s ease both; }
    .netbar button { border: 0; border-radius: 999px; padding: .38rem .9rem; font: inherit; font-weight: 900; color: #1b1830; background: linear-gradient(135deg, #fbbf24, #f59e0b); cursor: pointer; white-space: nowrap; }
    /* ═════ v3 polish: every pop-up shares the card look (frosted glass, foil frame, rays, staggered entrance) ═════ */
    .pcard { border-radius: 1.25rem; }
    .pcard::before { inset: 6px; border-radius: .95rem; border: 1px solid color-mix(in srgb, var(--k) 42%, rgba(255,255,255,.22)); z-index: 2;
        background: radial-gradient(circle at 50% -8%, color-mix(in srgb, var(--k) 30%, transparent), transparent 55%), repeating-linear-gradient(135deg, transparent 0 13px, color-mix(in srgb, var(--k) 8%, transparent) 13px 15px); }
    .pcard .pc-head { margin: 8px 8px 0; border-radius: .7rem .7rem .3rem .3rem; padding: .38rem .5rem; box-shadow: 0 4px 12px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.35); text-shadow: 0 1px 3px rgba(0,0,0,.55); }
    .pcard.wide { width: min(90cqw, 440px); aspect-ratio: auto; max-height: 94cqh; }
    .pcard.wide .pc-body { justify-content: flex-start; }
    .pcard .obtn { box-shadow: 0 6px 14px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.35); }

    /* buttons: glossy, lift on hover, big and clear for the answer buttons */
    .obtn { position: relative; overflow: hidden; border-radius: .9rem; text-shadow: 0 1px 2px rgba(0,0,0,.35); box-shadow: 0 5px 12px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.35), inset 0 -3px 0 rgba(0,0,0,.18); transition: transform .12s, filter .15s; }
    .obtn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(105deg, transparent 38%, rgba(255,255,255,.35) 50%, transparent 62%); transform: translateX(-130%); transition: transform .5s; pointer-events: none; }
    .obtn:hover { transform: translateY(-2px); filter: brightness(1.1); } .obtn:hover::after { transform: translateX(130%); }
    .obtn.ok { background: linear-gradient(135deg, #34d399, #059669); } .obtn.no { background: linear-gradient(135deg, #fb7185, #dc2626); }
    .pcard .ov-btns .obtn.ok, .pcard .ov-btns .obtn.no { min-width: 36%; font-size: clamp(.78rem, 3cqh, 1rem); padding-block: clamp(.45rem, 2cqh, .7rem); }

    /* player tiles for "choose a friend" */
    .pgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(84px, 1fr)); gap: .5rem; width: 100%; margin-top: .4rem; }
    .ptile { position: relative; display: flex; flex-direction: column; align-items: center; gap: .15rem; padding: .5rem .3rem .4rem; border-radius: .95rem; border: 1.5px solid color-mix(in srgb, var(--c) 55%, rgba(255,255,255,.2)); background: linear-gradient(165deg, color-mix(in srgb, var(--c) 22%, #1a1730), #12101e); color: #fff; cursor: pointer; font: inherit;
        box-shadow: 0 6px 14px rgba(0,0,0,.4); animation: tilePopIn .5s calc(.5s + var(--i, 0) * .06s) cubic-bezier(.34,1.56,.64,1) both; transition: transform .14s, box-shadow .14s; }
    .ptile:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 0 0 2px var(--c), 0 10px 22px color-mix(in srgb, var(--c) 45%, transparent); }
    .ptile:active { transform: scale(.95); }
    .ptile .pav { width: clamp(40px, 13cqh, 58px); height: clamp(40px, 13cqh, 58px); border-radius: 50%; overflow: hidden; border: 3px solid var(--c); background: #14121f; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 14px color-mix(in srgb, var(--c) 55%, transparent); }
    .ptile .pav .avimg { width: 100%; height: 100%; }
    .ptile b { font-size: clamp(.64rem, 2.5cqh, .82rem); max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ptile small { font-size: clamp(.5rem, 1.9cqh, .62rem); color: #9ca3af; font-weight: 700; }
    @keyframes tilePopIn { from { opacity: 0; transform: translateY(18px) scale(.6); } to { opacity: 1; transform: none; } }

    /* add-a-player form */
    .addp { display: flex; align-items: center; gap: .7rem; width: 100%; justify-content: center; }
    .apname { flex: 1; min-width: 0; max-width: 12rem; padding: .55rem .8rem; border-radius: .8rem; border: 1.5px solid rgba(255,255,255,.25); background: rgba(8,7,16,.55); color: #fff; font: 800 1rem Figtree, system-ui, sans-serif; outline: none; }
    .apname:focus { border-color: #34d399; box-shadow: 0 0 0 3px rgba(52,211,153,.3); }

    /* the older .ov-card pop-ups (menu, win, lobby, confirm) get the same glass + foil + entrance */
    .ov-card { --k: #f59e0b; position: relative; border: 2.5px solid color-mix(in srgb, var(--k) 70%, #fff); background: linear-gradient(165deg, rgba(38,32,64,.82), rgba(16,14,28,.74) 62%);
        -webkit-backdrop-filter: blur(8px) saturate(1.2); backdrop-filter: blur(8px) saturate(1.2); border-radius: 1.2rem;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.12), 0 0 30px color-mix(in srgb, var(--k) 38%, transparent), 0 18px 44px rgba(0,0,0,.5); animation: cardDeal .6s cubic-bezier(.2,.9,.3,1.1) both; }
    .ov-card::before { content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; background: radial-gradient(circle at 50% -10%, color-mix(in srgb, var(--k) 26%, transparent), transparent 60%), repeating-linear-gradient(135deg, transparent 0 13px, color-mix(in srgb, var(--k) 7%, transparent) 13px 15px); }
    .ov-card::after { content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; background: linear-gradient(115deg, transparent 38%, rgba(255,255,255,.18) 50%, transparent 62%); background-size: 250% 100%; background-position: 130% 0; animation: foilSweep 1.1s .45s ease-out 1 both; }
    .ov-card > * { position: relative; animation: chipPop .5s calc(.2s + var(--n, 0) * .06s) cubic-bezier(.34,1.56,.64,1) both; }
    .ov-card > *:nth-child(2) { --n: 1; } .ov-card > *:nth-child(3) { --n: 2; } .ov-card > *:nth-child(4) { --n: 3; } .ov-card > *:nth-child(5) { --n: 4; } .ov-card > *:nth-child(6) { --n: 5; }
    .ov-title { color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,.5); background: linear-gradient(135deg, color-mix(in srgb, var(--k) 85%, #000), color-mix(in srgb, var(--k) 45%, #000)); padding: .28rem .7rem; border-radius: .6rem; display: inline-block; letter-spacing: .12em; }
    .ov-icon { animation: iconPop .8s .2s cubic-bezier(.34,1.56,.64,1) both, iconPulse 1.8s 1.1s ease-in-out infinite !important; background: radial-gradient(circle at 30% 25%, rgba(255,255,255,.22), transparent 55%), color-mix(in srgb, var(--k) 22%, #14121f); box-shadow: 0 0 20px color-mix(in srgb, var(--k) 55%, transparent); }
    @media (prefers-reduced-motion: reduce) { .ov-card, .ov-card > *, .ptile { animation-duration: .01s !important; animation-delay: 0s !important; } }
    .board-dim { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(ellipse at center, rgba(0,0,0,.10) 40%, rgba(0,0,0,.30) 100%), rgba(4,6,18,.14); }
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

    /* ── Answer buttons, the "watching" card and the Spotlight clock ── */
    .obtn.ok { background: linear-gradient(135deg, #10b981, #059669); }
    .obtn.no { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .watch {
        --k: #06b6d4; position: absolute; top: calc(var(--nav) + 6px); left: 50%; transform: translateX(-50%) translateY(-8px); z-index: 36; display: none; align-items: center; gap: .6rem;
        width: min(52cqw, 360px); padding: .45rem .7rem .45rem .5rem; border-radius: .9rem; text-align: left; pointer-events: none;
        background: linear-gradient(135deg, color-mix(in srgb, var(--k) 22%, #14121f), rgba(8,7,16,.92)); border: 1.5px solid var(--k);
        box-shadow: 0 8px 24px rgba(0,0,0,.5), 0 0 22px color-mix(in srgb, var(--k) 35%, transparent);
    }
    .watch.show { display: flex; animation: watchIn .3s ease both; }
    @keyframes watchIn { to { transform: translateX(-50%) translateY(0); } }
    .watch .w-ic { width: clamp(26px, 8cqh, 38px); height: clamp(26px, 8cqh, 38px); border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: clamp(.95rem, 4cqh, 1.35rem); color: var(--k); border: 2px solid var(--k); background: rgba(255,255,255,.05); }
    .watch .w-tx { min-width: 0; font-size: clamp(.58rem, 2.3cqh, .76rem); line-height: 1.3; }
    .watch .w-tx b { display: block; font-size: .88em; letter-spacing: .08em; text-transform: uppercase; color: var(--k); }
    .watch .w-tx div { font-weight: 800; }
    .watch .w-tx span { color: #9ca3af; font-weight: 600; }
    .spot-clock { margin: .25rem 0; }
    .spot-clock b { display: inline-flex; align-items: center; justify-content: center; min-width: 2.4em; padding: .1em .5em; font-size: clamp(1.3rem, 8cqh, 2.4rem); font-weight: 900; font-variant-numeric: tabular-nums; border-radius: .6em; background: rgba(251,146,60,.18); border: 2px solid #fb923c; color: #fdba74; }
    .spot-clock.low b { background: rgba(239,68,68,.22); border-color: #ef4444; color: #fca5a5; animation: clockPulse .6s ease-in-out infinite; }
    @keyframes clockPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
    .pcard .tgt-row .tgt { cursor: pointer; }
    .pcard .watch-secs { color: #9ca3af; }

    /* ═════ Wide screens: two matching sidebars ═════
       The .side wrappers are invisible on phones. Once the stage is big enough they become real columns of
       equal width, and the board shrinks to fit between them — so both edges carry the same visual weight. */
    .pl .pos { display: none; }
    .side { display: contents; }
    .feed { display: none; }
    .feed-h, .feed-list { display: none; }

    @container (min-width: 1000px) and (min-height: 600px) {
        .board-wrap { --sw: clamp(200px, 15.5cqw, 290px); width: min(calc(100cqw - 2 * var(--sw) - 40px), calc((100cqh - var(--nav) - 16px) * var(--bw) / var(--bh))); }

        .side {
            --sw: clamp(200px, 15.5cqw, 290px);
            display: flex; flex-direction: column; gap: 12px; position: absolute; top: calc(var(--nav) + 10px); bottom: 14px; width: var(--sw); z-index: 30; pointer-events: none;
        }
        .side-l { left: 14px; }
        .side-r { right: 14px; }
        .side .hud { position: static; transform: none; inset: auto; max-width: none; width: 100%; pointer-events: auto; }
        .side-l .hud-tl, .side-l .hud-left, .side-r .feed, .side-r .hud-br, .side-r .hud-bl {
            box-sizing: border-box; border-radius: 18px; background: linear-gradient(160deg, rgba(24,21,42,.78), rgba(8,7,16,.7));
            border: 1px solid rgba(255,255,255,.11); backdrop-filter: blur(12px); box-shadow: 0 10px 30px rgba(0,0,0,.35);
        }

        /* ── Left: the game card, then every player ── */
        .side-l .hud-tl { display: flex; flex-direction: column; align-items: stretch; gap: 7px; padding: 12px; flex-shrink: 0; }
        .side-l .hud-tl .chip { width: 100%; box-sizing: border-box; justify-content: flex-start; padding: .45rem .75rem; font-size: .82rem; border-radius: .8rem; background: rgba(255,255,255,.05); }
        .side-l .hud-tl .chip .ic { font-size: 1.15em; }
        .side-l .hud-tl .chip.rule { padding: .55rem .75rem; line-height: 1.35; }
        .side-l .hud-left { flex: 1; min-height: 0; max-height: none; overflow-y: auto; display: flex; flex-direction: column; justify-content: flex-start; gap: 8px; padding: 10px; transform: none; scrollbar-width: thin; }
        .side-l .pl { width: 100%; box-sizing: border-box; gap: 10px; padding: 6px 12px 6px 6px; border-radius: 14px; }
        .side-l .pl.cur { transform: none; }
        .side-l .pl .ring { --d: 46px; }
        .side-l .pl .info { display: flex; flex: 1; min-width: 0; gap: 4px; }
        .side-l .pl .nm { max-width: none; font-size: .9rem; }
        .side-l .pl .pos { display: block; font-size: .68rem; font-weight: 700; color: #9ca3af; }
        .side-l .pl .bar { display: block; width: 100%; max-width: none; height: 6px; }
        .side-l .pl .pw { display: flex; font-size: .78rem; gap: 6px; }

        /* ── Right: header buttons, the recent-moves feed, reactions, and the roll dock ── */
        .side-r .hud-tr { justify-content: flex-end; gap: 9px; flex-shrink: 0; }
        .side-r .icon-btn { width: 44px; height: 44px; font-size: 1.3rem; }
        .side-r .feed { flex: 1; min-height: 0; display: flex; flex-direction: column; padding: 12px; }
        .side-r .feed-h { display: block; flex-shrink: 0; font-size: .68rem; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; color: #9ca3af; margin-bottom: 8px; }
        .side-r .feed-list { display: flex; flex: 1; min-height: 0; flex-direction: column; justify-content: flex-end; gap: 6px; overflow: hidden; }
        .fi { font-size: .8rem; font-weight: 700; line-height: 1.3; padding: .45rem .65rem; border-radius: .7rem; background: rgba(255,255,255,.05); border-left: 3px solid var(--c, #6b7280); animation: feedIn .35s ease both; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }
        .fi .ic { color: var(--c, #fff); margin-right: .2em; }
        @keyframes feedIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .side-r .hud-bl { display: flex; flex-direction: row; align-items: center; padding: 8px; flex-shrink: 0; }
        .side-r .reactbar { display: flex; flex: 1; max-width: none; justify-content: space-between; }
        .side-r #reactToggle { display: none; }
        .side-r .reactbar button { font-size: 1.15rem; padding: .3rem .4rem; }
        .side-r .hud-br { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 14px; flex-shrink: 0; }
        .side-r .turn-banner { font-size: .92rem; padding: .6rem .8rem; }
        .side-r .die-badge { width: 136px; height: 136px; }
        .side-r .die-scene, .side-r .die-cube { --s: 58px; }
        .side-r .die-label { font-size: .72rem; }
        .side-r .die-val { width: 30px; height: 30px; font-size: .95rem; }
        .side-r .powers { width: 100%; justify-content: center; gap: 8px; }
        .side-r .pwr { font-size: .95rem; padding: .35rem .9rem; }
        .toasts { bottom: 4cqh; }
    }
</style>
