{{-- Google Analytics 4. Turned on by SNL_GA4_ID in .env (e.g. G-XXXXXXXXXX); nothing loads without it, and never inside the Android / Windows apps. --}}
@if(config('snl.analytics.ga4_id'))
<script>
(function () {
    var id = @json(config('snl.analytics.ga4_id'));
    if (window.Capacitor || window.SNLDesktop || location.protocol === 'app:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1') return;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { dataLayer.push(arguments); };
    gtag('js', new Date());
    gtag('config', id, { anonymize_ip: true });
    var s = document.createElement('script');
    s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(s);
})();
</script>
@endif
<script>window.snlTrack = function (name, params) { try { if (window.gtag) gtag('event', name, params || {}); } catch (e) {} };</script>
