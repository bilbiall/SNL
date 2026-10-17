@props(['title' => 'Snakes & Liquor', 'text' => 'Come play Snakes & Liquor — the free party board game where every square has a story.', 'url' => null, 'label' => 'Share'])
@php
    $u = $url ?: url('/');
    $t = $text;
    $enc = fn ($s) => rawurlencode($s);
@endphp
{{-- Share buttons: native share sheet where the device has one, plus plain links (no third-party scripts, no tracking). --}}
<div class="share-row" data-share data-title="{{ $title }}" data-text="{{ $t }}" data-url="{{ $u }}">
    <span class="share-l"><x-icon name="share" /> {{ $label }}</span>
    <button type="button" class="sb nat" data-native style="display:none;">Share…</button>
    <a class="sb wa" target="_blank" rel="noopener" href="https://wa.me/?text={{ $enc($t . ' ' . $u) }}">WhatsApp</a>
    <a class="sb tg" target="_blank" rel="noopener" href="https://t.me/share/url?url={{ $enc($u) }}&text={{ $enc($t) }}">Telegram</a>
    <a class="sb x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text={{ $enc($t) }}&url={{ $enc($u) }}">X</a>
    <a class="sb fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $enc($u) }}">Facebook</a>
    <a class="sb em" href="mailto:?subject={{ $enc($title) }}&body={{ $enc($t . "\n\n" . $u) }}">Email</a>
    <button type="button" class="sb cp" data-copy>Copy link</button>
</div>
@once
<style>
    .share-row { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; justify-content: center; margin: 1.1rem 0 0; }
    .share-l { display: inline-flex; align-items: center; gap: .3rem; font-size: .72rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: #94a3b8; margin-right: .2rem; }
    .sb { padding: .42rem .8rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.18); background: rgba(255,255,255,.08); color: #fff; font: 800 .74rem Figtree, system-ui, sans-serif; text-decoration: none; cursor: pointer; transition: transform .12s, background .15s; }
    .sb:hover { transform: translateY(-2px); background: rgba(255,255,255,.16); }
    .sb.wa { border-color: rgba(37,211,102,.55); } .sb.tg { border-color: rgba(42,171,238,.55); } .sb.x { border-color: rgba(255,255,255,.4); } .sb.fb { border-color: rgba(66,103,178,.7); }
    .sb.nat { background: linear-gradient(135deg, #8b5cf6, #6366f1); border-color: transparent; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-share]').forEach(function (box) {
        var data = { title: box.dataset.title, text: box.dataset.text, url: box.dataset.url };
        var nat = box.querySelector('[data-native]'), copy = box.querySelector('[data-copy]');
        if (navigator.share) { nat.style.display = ''; nat.onclick = function () { navigator.share(data).catch(function () {}); }; }
        copy.onclick = function () {
            (navigator.clipboard ? navigator.clipboard.writeText(data.url) : Promise.reject()).then(function () { copy.textContent = 'Link copied'; setTimeout(function () { copy.textContent = 'Copy link'; }, 2200); }, function () { window.prompt('Copy this link', data.url); });
        };
    });
});
</script>
@endonce
