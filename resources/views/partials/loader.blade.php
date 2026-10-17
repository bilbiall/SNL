@php
    $__looks = \App\Support\Avatars::looks();
    shuffle($__looks);
    $__pool = array_map(fn ($l) => $l['id'] . '-' . random_int(1, 7), array_slice($__looks, 0, 14));
    $__tiles = [['#ec4899', 'pick'], ['#22d3ee', 'fact'], ['#facc15', 'power'], ['#34d399', 'trophy']];
@endphp
{{-- Loader: the landing-page hopping token, shrunk — it changes avatar on every landing. Shown while a page / the app / the game loads. --}}
<div id="snlLoader" data-wait="{{ $wait ?? 0 }}" role="status" aria-label="Loading">
    <style>
        #snlLoader { position: fixed; inset: 0; z-index: 3000; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.1rem; background: rgba(11,10,22,.55); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); transition: opacity .35s ease; }
        #snlLoader.out { opacity: 0; pointer-events: none; }
        #snlLoader .ld-logo { width: 4.2rem; height: 4.2rem; border-radius: 1.1rem; box-shadow: 0 8px 30px rgba(236,72,153,.5); }
        #snlLoader .ld-track { --t: 30px; position: relative; display: flex; gap: 6px; padding-top: calc(var(--t) * 1.5); }
        #snlLoader .ld-tile { width: var(--t); height: var(--t); border-radius: 24%; display: flex; align-items: center; justify-content: center; font-size: 17px; color: rgba(10,8,20,.85); background: linear-gradient(160deg, color-mix(in srgb, var(--c) 100%, #fff 18%), var(--c)); box-shadow: inset 0 -3px 0 rgba(0,0,0,.25), 0 6px 12px rgba(0,0,0,.5); }
        #snlLoader .ld-tok { position: absolute; left: 0; top: 0; width: var(--t); height: var(--t); will-change: transform; }
        #snlLoader .ld-tok > div { width: 100%; height: 100%; border-radius: 50%; border: 2.5px solid #fff; overflow: hidden; background: #14121f; box-shadow: 0 6px 12px rgba(0,0,0,.55); }
        #snlLoader .ld-tok img { width: 100%; height: 100%; display: block; }
        #snlLoader .ld-txt { font: 900 .78rem Figtree, system-ui, sans-serif; letter-spacing: .22em; text-transform: uppercase; color: #cbd5e1; animation: ldPulse 1.4s ease-in-out infinite; }
        @keyframes ldPulse { 0%, 100% { opacity: .45; } 50% { opacity: 1; } }
        @media (prefers-reduced-motion: reduce) { #snlLoader .ld-txt { animation: none; } }
    </style>
    <img class="ld-logo" src="{{ asset('img/logo-96.png') }}" alt="">
    <div class="ld-track" id="ldTrack">
        @foreach($__tiles as [$c, $icon])<div class="ld-tile" style="--c:{{ $c }}"><x-icon :name="$icon" /></div>@endforeach
        <div class="ld-tok" id="ldTok"><div><img id="ldAv" src="{{ asset('avatars/' . $__pool[0] . '.svg') }}" alt=""></div></div>
    </div>
    <div class="ld-txt">Loading</div>
    <script>
    (function () {
        var el = document.getElementById('snlLoader'), tok = document.getElementById('ldTok'), img = document.getElementById('ldAv');
        var tiles = [].slice.call(el.querySelectorAll('.ld-tile')), base = @json(asset('avatars')), pool = @json($__pool), ai = 0;
        var HOP = 760, GAP = 150, N = tiles.length - 1, CYCLE = N * (HOP + GAP) + 520, t0 = performance.now(), running = true, landed = -1, pitch = 0, size = 0;
        var ease = function (t) { return t < .5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; };
        var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        pool.forEach(function (k) { var p = new Image(); p.src = base + '/' + k + '.svg'; });
        function frame(now) {
            if (!running) return;
            if (!pitch) { pitch = tiles[1].offsetLeft - tiles[0].offsetLeft; size = tiles[0].offsetWidth; }
            var t = (now - t0) % CYCLE, x = 0, y = 0, sx = 1, sy = 1, sc = 1, rot = 0;
            var h = Math.floor(t / (HOP + GAP)), local = t - h * (HOP + GAP);
            if (h < N) {
                if (local < HOP) { var u = local / HOP; x = (h + ease(u)) * pitch; y = -Math.sin(Math.PI * u) * size * 1.05; var st = Math.sin(Math.PI * u) * .12; sy = 1 + st; sx = 1 - st * .6; rot = (u - .5) * 12; }
                else { var s = (local - HOP) / GAP; x = (h + 1) * pitch; var sq = Math.sin((1 - s) * Math.PI / 2) * .2; sy = 1 - sq; sx = 1 + sq * .8; if (landed < h) { landed = h; ai = (ai + 1) % pool.length; img.src = base + '/' + pool[ai] + '.svg'; } }
            } else {                                              // pop back at the start
                x = N * pitch; var f = Math.min(1, (t - N * (HOP + GAP)) / 260); sc = 1 - ease(f); if (t >= CYCLE - 20) landed = -1;
                if (f >= 1) { x = 0; sc = 1; }
            }
            tok.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0) rotate(' + rot.toFixed(1) + 'deg) scale(' + (sx * sc).toFixed(3) + ',' + (sy * sc).toFixed(3) + ')';
            requestAnimationFrame(frame);
        }
        if (!reduce) requestAnimationFrame(frame);

        var needsReady = el.dataset.wait === '1', loaded = false, readyFlag = false, shownAt = performance.now();
        var minShow = (window.Capacitor || window.SNLDesktop || (location.hostname === 'localhost' && !location.port)) ? 900 : 450;
        function hide() {
            var wait = Math.max(0, minShow - (performance.now() - shownAt));
            setTimeout(function () { el.classList.add('out'); setTimeout(function () { el.style.display = 'none'; running = false; }, 380); }, wait);
        }
        window.SNLLoader = {
            show: function () { shownAt = performance.now(); minShow = 350; el.style.display = 'flex'; el.classList.remove('out'); if (!running && !reduce) { running = true; t0 = performance.now(); landed = -1; requestAnimationFrame(frame); } running = true; },
            hide: hide,
            ready: function () { readyFlag = true; if (loaded) hide(); },
        };
        window.addEventListener('load', function () { loaded = true; if (!needsReady || readyFlag) hide(); });
        if (needsReady) setTimeout(hide, 8000);                  // never trap anyone behind it
        // feedback on the way out: moving to another page shows the loader after a beat
        document.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || e.defaultPrevented || a.target || a.hasAttribute('download') || e.metaKey || e.ctrlKey || e.shiftKey || e.button) return;
            var u; try { u = new URL(a.href, location.href); } catch (_) { return; }
            if (u.origin !== location.origin || /^(javascript|mailto|tel):/.test(a.getAttribute('href')) || (u.pathname === location.pathname && u.search === location.search)) return;
            setTimeout(function () { window.SNLLoader.show(); }, 250);
        });
        document.addEventListener('submit', function (e) { if (!e.defaultPrevented) setTimeout(function () { window.SNLLoader.show(); }, 250); });
        window.addEventListener('pageshow', function (e) { if (e.persisted) hide(); });
    })();
    </script>
</div>
