{{-- Avatar picker. Usage:  SNLAvatars.open(currentKey, key => …)  ·  SNLAvatars.url(key)
     Keys look like "f3-4" or "h7-2" (look, skin shade). Collections (Retro 90s, Heroes) come from resources/avatars.json. --}}
<style>
    .avbtn { position: relative; flex-shrink: 0; width: 3.1rem; height: 3.1rem; padding: 0; border-radius: 50%; border: 2px solid #fff; background: #1b1830; cursor: pointer; overflow: visible; box-shadow: 0 4px 12px rgba(0,0,0,.4); transition: transform .12s; }
    .avbtn:hover { transform: scale(1.06); } .avbtn:active { transform: scale(.95); }
    .avbtn img { width: 100%; height: 100%; border-radius: 50%; display: block; image-rendering: pixelated; object-fit: cover; }
    .avbtn::after { content: 'Edit'; position: absolute; left: 50%; bottom: -.55rem; transform: translateX(-50%); padding: 0 .4rem; border-radius: 999px; background: #f59e0b; color: #1b1830; font-size: .55rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .avbtn.lg { width: 4.4rem; height: 4.4rem; }

    .avp { position: fixed; inset: 0; z-index: 200; display: none; align-items: center; justify-content: center; padding: 1rem; background: rgba(5,4,12,.78); backdrop-filter: blur(4px); }
    .avp.show { display: flex; }
    .avp-card { width: min(94vw, 400px); max-height: 92vh; overflow-y: auto; border-radius: 1.2rem; padding: 1rem; background: linear-gradient(165deg, #211d36, #14121f 60%); border: 1px solid rgba(255,255,255,.18); box-shadow: 0 24px 60px rgba(0,0,0,.65); text-align: center; animation: avpIn .25s cubic-bezier(.34,1.4,.64,1) both; }
    @keyframes avpIn { from { opacity: 0; transform: scale(.92) translateY(10px); } to { opacity: 1; transform: none; } }
    .avp-prev { width: 6rem; height: 6rem; margin: 0 auto .35rem; border-radius: 1rem; border: 3px solid #fff; overflow: hidden; box-shadow: 0 8px 22px rgba(0,0,0,.5); }
    .avp-prev img { width: 100%; height: 100%; display: block; image-rendering: pixelated; }
    .avp-name { font-weight: 900; font-size: .95rem; }
    .avp-tabs { display: inline-flex; gap: .25rem; padding: .2rem; border-radius: 999px; background: rgba(255,255,255,.07); margin: .5rem .15rem; }
    .avp-tabs button { border: none; background: none; color: #9ca3af; font: inherit; font-weight: 800; font-size: .74rem; padding: .3rem .8rem; border-radius: 999px; cursor: pointer; }
    .avp-tabs button.on { background: linear-gradient(135deg, #f59e0b, #ec4899); color: #fff; }
    .avp-cols button.on { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
    .avp-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: .45rem; }
    .avp-look { position: relative; padding: 0; border: 2px solid transparent; border-radius: .8rem; background: none; cursor: pointer; overflow: hidden; aspect-ratio: 1; }
    .avp-look img { width: 100%; height: 100%; display: block; image-rendering: pixelated; border-radius: .6rem; }
    .avp-look:hover { transform: translateY(-2px); }
    .avp-look.on { border-color: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,.8), 0 0 18px rgba(245,158,11,.5); }
    .avp-look span { position: absolute; left: 0; right: 0; bottom: 0; padding: .1rem 0; font-size: .52rem; font-weight: 800; background: rgba(8,7,16,.75); color: #e5e7eb; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .avp-skins { display: flex; justify-content: center; gap: .4rem; margin: .5rem 0 .2rem; flex-wrap: wrap; }
    .avp-skins button { width: 1.9rem; height: 1.9rem; border-radius: 50%; border: 2px solid rgba(255,255,255,.35); cursor: pointer; padding: 0; }
    .avp-skins button.on { border-color: #fff; box-shadow: 0 0 0 3px rgba(245,158,11,.85); transform: scale(1.12); }
    .avp-lbl { font-size: .62rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #9ca3af; margin-top: .6rem; }
</style>

<div class="avp" id="avp" role="dialog" aria-modal="true" aria-label="Choose your avatar">
    <div class="avp-card">
        <div class="avp-prev"><img id="avpPrev" alt=""></div>
        <div class="avp-name" id="avpName"></div>
        <div class="avp-tabs avp-cols" id="avpCols"></div>
        <div class="avp-tabs" id="avpTabs">
            <button type="button" data-f="all" class="on">All</button>
            <button type="button" data-f="f">Women</button>
            <button type="button" data-f="m">Men</button>
        </div>
        <div class="avp-grid" id="avpGrid"></div>
        <div class="avp-lbl">Skin shade</div>
        <div class="avp-skins" id="avpSkins"></div>
        <button type="button" class="btn btn-primary btn-block" id="avpDone" style="margin-top:.8rem;">Done</button>
    </div>
</div>

<script>
window.SNLAvatars = (function () {
    var R = @json(\App\Support\Avatars::roster()), BASE = @json(asset('avatars'));
    var el = function (id) { return document.getElementById(id); };
    var url = function (key) { return BASE + '/' + key + '.svg'; };
    var LOOKS = [].concat.apply([], R.collections.map(function (c) { return c.looks; }));
    var colOf = function (look) {
        var c = R.collections.find(function (c) { return c.looks.some(function (l) { return l.id === look; }); });
        return (c || R.collections[0]).id;
    };
    var first = R.collections[0].looks[0].id;
    var st = { look: first, col: R.collections[0].id, skin: 3, f: 'all', cb: null };
    var KEY = new RegExp('^([a-z][0-9]+)-([0-9]+)$');
    function parse(key) { var m = KEY.exec(key || ''); return m ? { look: m[1], skin: +m[2] } : { look: first, skin: 3 }; }
    function draw() {
        var cur = LOOKS.find(function (l) { return l.id === st.look; }) || LOOKS[0];
        var col = R.collections.find(function (c) { return c.id === st.col; }) || R.collections[0];
        el('avpPrev').src = url(st.look + '-' + st.skin);
        el('avpName').textContent = cur.label + ' · ' + R.skins.find(function (s) { return s.id === st.skin; }).label;
        el('avpCols').innerHTML = R.collections.map(function (c) {
            return '<button type="button" data-col="' + c.id + '" class="' + (c.id === st.col ? 'on' : '') + '">' + c.label + '</button>';
        }).join('');
        el('avpGrid').innerHTML = col.looks.filter(function (l) { return st.f === 'all' || l.gender === st.f; }).map(function (l) {
            return '<button type="button" class="avp-look ' + (l.id === st.look ? 'on' : '') + '" data-look="' + l.id + '" title="' + l.label + '"><img src="' + url(l.id + '-' + st.skin) + '" alt="' + l.label + '"><span>' + l.label + '</span></button>';
        }).join('');
        el('avpSkins').innerHTML = R.skins.map(function (s) {
            return '<button type="button" class="' + (s.id === st.skin ? 'on' : '') + '" data-skin="' + s.id + '" title="' + s.label + '" style="background:' + s.hex + '"></button>';
        }).join('');
        el('avpTabs').querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b.dataset.f === st.f); });
    }
    el('avpCols').addEventListener('click', function (e) {
        var b = e.target.closest('[data-col]'); if (!b) return;
        st.col = b.dataset.col;
        var c = R.collections.find(function (x) { return x.id === st.col; });
        if (!c.looks.some(function (l) { return l.id === st.look; })) st.look = c.looks[0].id; // keep the shade, switch to this collection's first look
        draw();
    });
    el('avpGrid').addEventListener('click', function (e) { var b = e.target.closest('[data-look]'); if (b) { st.look = b.dataset.look; draw(); } });
    el('avpSkins').addEventListener('click', function (e) { var b = e.target.closest('[data-skin]'); if (b) { st.skin = +b.dataset.skin; draw(); } });
    el('avpTabs').addEventListener('click', function (e) { var b = e.target.closest('[data-f]'); if (b) { st.f = b.dataset.f; draw(); } });
    function close(commit) { el('avp').classList.remove('show'); if (commit && st.cb) st.cb(st.look + '-' + st.skin); st.cb = null; }
    el('avpDone').addEventListener('click', function () { close(true); });
    el('avp').addEventListener('click', function (e) { if (e.target === el('avp')) close(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && el('avp').classList.contains('show')) close(false); });
    return {
        url: url,
        open: function (key, cb) {
            var p = parse(key); st.look = p.look; st.col = colOf(p.look); st.skin = p.skin; st.cb = cb; st.f = 'all';
            draw(); el('avp').classList.add('show');
        },
    };
})();
</script>
