<style>
    :root {
        --bg: #0b0a16; --ink: #fff; --muted: #9ca3af; --line: rgba(255,255,255,.1);
        --amber: #f59e0b; --pink: #ec4899; --violet: #8b5cf6; --mint: #34d399; --red: #f87171;
    }
    html { -webkit-text-size-adjust: 100%; }
    body {
        margin: 0; min-height: 100vh; color: var(--ink); font-family: 'Figtree', system-ui, sans-serif;
        background:
            radial-gradient(ellipse 900px 520px at 12% -5%, rgba(245,158,11,.16), transparent 60%),
            radial-gradient(ellipse 900px 640px at 92% 105%, rgba(236,72,153,.16), transparent 60%),
            var(--bg);
        background-attachment: fixed;
    }
    .snl-wrap { max-width: 1040px; margin: 0 auto; padding: 1rem 1rem 3rem; }
    .snl-narrow { max-width: 560px; margin: 0 auto; padding: 1rem 1rem 3rem; }
    .snl-card { background: rgba(255,255,255,.04); border: 1px solid var(--line); border-radius: 1.25rem; padding: 1.15rem; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    .snl-h { font-weight: 900; letter-spacing: -.01em; }
    .snl-muted { color: var(--muted); }
    .snl-label { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: .35rem; }
    .snl-input {
        width: 100%; background: rgba(255,255,255,.06); border: 1px solid var(--line); border-radius: .8rem;
        padding: .7rem .85rem; color: #fff; font: inherit; font-size: .95rem; outline: none; box-sizing: border-box;
    }
    .snl-input:focus { border-color: rgba(245,158,11,.7); box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
    select.snl-input option { background: #1a1a2e; }
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .45rem; border: none; cursor: pointer;
        font: inherit; font-weight: 900; font-size: .92rem; padding: .8rem 1.3rem; border-radius: .95rem; color: #fff;
        text-decoration: none; transition: transform .12s, filter .15s, opacity .15s; text-align: center;
    }
    .btn:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); }
    .btn:active:not(:disabled) { transform: scale(.97); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }
    .btn-primary { background: linear-gradient(135deg, #f59e0b, #ec4899); box-shadow: 0 8px 22px rgba(236,72,153,.28); }
    .btn-violet { background: linear-gradient(135deg, #8b5cf6, #6366f1); box-shadow: 0 8px 22px rgba(99,102,241,.28); }
    .btn-ghost { background: rgba(255,255,255,.07); border: 1px solid var(--line); color: #e5e7eb; }
    .btn-sm { padding: .5rem .85rem; font-size: .78rem; border-radius: .7rem; }
    .btn-block { width: 100%; }
    .pill-opt { position: relative; }
    .pill-opt input { position: absolute; opacity: 0; pointer-events: none; }
    .pill-opt > span {
        display: flex; flex-direction: column; align-items: center; gap: .15rem; text-align: center;
        padding: .75rem .6rem; border-radius: 1rem; background: rgba(255,255,255,.05); border: 1.5px solid var(--line);
        cursor: pointer; font-weight: 800; font-size: .85rem; transition: all .15s; height: 100%; box-sizing: border-box;
    }
    .pill-opt > span small { font-weight: 600; font-size: .68rem; color: var(--muted); line-height: 1.3; }
    .pill-opt input:checked + span { background: rgba(245,158,11,.14); border-color: rgba(245,158,11,.75); box-shadow: 0 0 0 3px rgba(245,158,11,.12); }
    .pill-opt input:disabled + span { opacity: .4; cursor: not-allowed; }
    .snl-alert { border-radius: 1rem; padding: .8rem 1rem; font-size: .85rem; font-weight: 700; margin-bottom: 1rem; }
    .snl-alert.err { background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.3); color: #fca5a5; }
    .snl-alert.ok { background: rgba(52,211,153,.1); border: 1px solid rgba(52,211,153,.3); color: #6ee7b7; }
    .snl-foot { text-align: center; font-size: .72rem; color: #6b7280; padding: 1.5rem 1rem 2rem; line-height: 1.7; }
    .snl-foot a { color: #9ca3af; }
    .logo { font-weight: 900; letter-spacing: -.02em; }
    .logo b { background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; }
    /* phones: slimmer buttons and fields everywhere */
    @media (max-width: 640px) {
        .btn { font-size: .8rem; padding: .55rem .95rem; border-radius: .8rem; gap: .35rem; }
        .btn-sm { font-size: .72rem; padding: .38rem .7rem; }
        .snl-input { padding: .55rem .7rem; font-size: .88rem; border-radius: .7rem; }
        .snl-label { font-size: .62rem; margin-bottom: .25rem; }
    }
</style>
