<style>
    /* Brand lockup: logo + "Snakes & Liquor" in Bungee, flanked by the snake and the cheers glass */
    .brand-lockup { display: inline-flex; align-items: center; gap: .6rem; text-decoration: none; color: #fff; line-height: 1; min-width: 0; }
    .brand-lockup .bl-logo { height: 2.3rem; width: auto; flex-shrink: 0; border-radius: .65rem; filter: drop-shadow(0 2px 9px rgba(236,72,153,.55)); }
    .brand-lockup .bl-word { display: inline-flex; align-items: center; gap: .3em; font-family: 'Bungee', 'Figtree', system-ui, sans-serif; font-weight: 400; font-size: 1.1rem; letter-spacing: .02em; white-space: nowrap; filter: drop-shadow(0 0 12px rgba(236,72,153,.35)); }
    .brand-lockup .bl-word b { font-weight: 400; background: linear-gradient(180deg, #fff 20%, #cbd5e1); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: none; }
    .brand-lockup .bl-word i { font-style: normal; background: linear-gradient(135deg, #f59e0b, #ec4899); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .brand-lockup .bl-ic { font-size: 1.2em; }
    .brand-lockup .bl-ic.a { color: #34d399; } .brand-lockup .bl-ic.b { color: #f59e0b; }
    .brand-lockup:hover .bl-logo { transform: rotate(-4deg) scale(1.05); } .brand-lockup .bl-logo { transition: transform .2s; }
    .brand-lockup.lg .bl-logo { height: 3.2rem; } .brand-lockup.lg .bl-word { font-size: 1.5rem; }
    .brand-lockup.sm .bl-logo { height: 1.9rem; } .brand-lockup.sm .bl-word { font-size: .95rem; }
    @media (max-width: 480px) { .brand-lockup .bl-ic { display: none; } .brand-lockup .bl-word { font-size: .95rem; } }
</style>
