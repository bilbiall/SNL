/* Android app only: tells you when a newer version of the app is available.
   The app files live inside the APK, so a website update can't reach it — a new APK has to be installed over the old one
   (no uninstall needed; your saved game stays). This checks the site's /downloads/version.json when you open the app online. */
(function () {
  'use strict';
  var isApp = !!window.Capacitor || (location.hostname === 'localhost' && !location.port && location.protocol === 'https:');
  if (!isApp) return;

  fetch('/app-info.json', { cache: 'no-store' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (me) {
    if (!me || !me.site) return;
    var ctl = new AbortController(), timer = setTimeout(function () { ctl.abort(); }, 5000);
    return fetch(me.site + '/downloads/version.json?t=' + Date.now(), { cache: 'no-store', signal: ctl.signal })
      .then(function (r) { clearTimeout(timer); return r.ok ? r.json() : null; })
      .then(function (remote) {
        if (!remote || !(remote.versionCode > me.versionCode)) return;
        var seen = null; try { seen = localStorage.getItem('snl_update_dismissed'); } catch (e) {}
        if (seen === String(remote.versionCode)) return;
        var bar = document.createElement('div');
        bar.style.cssText = 'position:fixed;left:50%;bottom:calc(16px + env(safe-area-inset-bottom));transform:translateX(-50%);z-index:2500;display:flex;align-items:center;gap:.7rem;max-width:92vw;padding:.55rem .6rem .55rem 1rem;border-radius:999px;background:rgba(16,185,129,.95);color:#fff;font:800 .85rem Figtree,system-ui,sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.5);';
        bar.innerHTML = '<span>New version ' + (remote.versionName || '') + ' is ready</span><a style="background:#fff;color:#065f46;border-radius:999px;padding:.4rem .9rem;text-decoration:none;font-weight:900;" href="' + me.site + '/download/android">Update</a><button type="button" aria-label="Dismiss" style="background:none;border:0;color:#fff;font-size:1.1rem;cursor:pointer;padding:0 .4rem;">×</button>';
        bar.querySelector('button').onclick = function () { try { localStorage.setItem('snl_update_dismissed', String(remote.versionCode)); } catch (e) {} bar.remove(); };
        document.body.appendChild(bar);
      });
  }).catch(function () { /* offline — nothing to check */ });
})();
