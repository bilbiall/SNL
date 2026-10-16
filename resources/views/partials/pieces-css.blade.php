<style>
    /* ── Game icons ── sized by font-size, coloured by text colour */
    .ic { width: 1em; height: 1em; display: inline-block; vertical-align: -.14em; fill: currentColor; flex-shrink: 0; }
    .ic-txt { display: inline-block; line-height: 1; }

    /* ── Floating game pieces: each one pops in (spin + overshoot), then drifts and tilts forever ── */
    .pieces { position: absolute; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
    .pc {
        position: absolute; left: var(--x); top: var(--y); font-size: var(--s, 40px); color: var(--c, #f59e0b); opacity: 0;
        filter: drop-shadow(0 0 10px color-mix(in srgb, var(--c, #f59e0b) 65%, transparent)) drop-shadow(0 5px 6px rgba(0,0,0,.55));
        animation:
            pcIn 1.15s cubic-bezier(.2, 1.45, .35, 1) var(--dl, 0s) both,
            pcBob var(--bd, 8s) ease-in-out calc(var(--dl, 0s) + 1.15s) infinite alternate;
    }
    .pc .ic { display: block; transition: transform .5s ease-out; animation: pcGlow 3.4s ease-in-out calc(var(--dl, 0s) * 1.3) infinite alternate; }
    @keyframes pcIn {
        0%   { opacity: 0; transform: translateY(46px) scale(.05) rotate(-170deg); }
        55%  { opacity: .95; transform: translateY(-8px) scale(1.28) rotate(16deg); }
        80%  { transform: translateY(2px) scale(.94) rotate(calc(var(--r, 0deg) - 4deg)); }
        100% { opacity: var(--o, .7); transform: translateY(0) scale(1) rotate(var(--r, 0deg)); }
    }
    @keyframes pcBob {
        0%   { transform: translate(0, 0) rotate(var(--r, 0deg)); }
        50%  { transform: translate(var(--dx, 10px), calc(var(--dy, 14px) * -1)) rotate(calc(var(--r, 0deg) + 12deg)); }
        100% { transform: translate(calc(var(--dx, 10px) * -1), var(--dy, 14px)) rotate(calc(var(--r, 0deg) - 9deg)); }
    }
    @keyframes pcGlow { from { filter: brightness(.9); } to { filter: brightness(1.35); } }
    @media (prefers-reduced-motion: reduce) { .pc, .pc .ic { animation: none !important; opacity: var(--o, .7) !important; } }
</style>
