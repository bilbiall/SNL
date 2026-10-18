{{-- Google AdSense game ads. Included ONLY on the new-game page and the play page (never the landing page, TV screen or apps).
     Loading this script shows nothing by itself: an ad appears only when snl-ads.js calls adBreak() at a pause. --}}
@if(preg_match('/^ca-pub-\d+$/', (string) config('snl.ads.client')))
<script>window.SNL_ADS = { minGap: {{ (int) config('snl.ads.min_gap') }}, freeGames: {{ (int) config('snl.ads.free_games') }} };</script>
<script>
(function () {
    if (window.Capacitor || window.SNLDesktop || location.protocol === 'app:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1') return;
    window.adsbygoogle = window.adsbygoogle || [];
    window.adBreak = window.adConfig = function (o) { window.adsbygoogle.push(o); };
    window.adConfig({ preloadAdBreaks: 'on', sound: 'on' });
    var s = document.createElement('script');
    s.async = true; s.crossOrigin = 'anonymous';
    s.setAttribute('data-ad-client', @json(config('snl.ads.client')));
    s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js';
    document.head.appendChild(s);
})();
</script>
@endif
