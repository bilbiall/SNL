/* Installable-app glue: registers the service worker, keeps storage persistent, and exposes the install prompt.
   window.SNLPwa = { canInstall(), install(), isStandalone(), onChange(fn) } */
(function () {
  'use strict';
  var deferred = null, listeners = [];
  var notify = function () { listeners.forEach(function (f) { try { f(); } catch (e) {} }); };

  var standalone = function () {
    return (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true || document.referrer.indexOf('android-app://') === 0;
  };

  window.SNLPwa = {
    canInstall: function () { return !!deferred; },
    isStandalone: standalone,
    onChange: function (f) { listeners.push(f); },
    install: function () {
      if (!deferred) return Promise.resolve(false);
      deferred.prompt();
      return deferred.userChoice.then(function (c) { deferred = null; notify(); return c && c.outcome === 'accepted'; });
    },
  };

  window.addEventListener('beforeinstallprompt', function (e) { e.preventDefault(); deferred = e; notify(); });
  window.addEventListener('appinstalled', function () { deferred = null; notify(); });

  // inside the Android / Windows app the live site (online play) opens in the same window — give a way back to the app's own home
  (function () {
    var app = window.Capacitor || window.SNLDesktop, local = location.hostname === 'localhost' || location.protocol === 'app:';
    if (!app || local) return;
    document.addEventListener('DOMContentLoaded', function () {
      var a = document.createElement('a');
      a.href = window.SNLDesktop ? 'app://snl/index.html' : 'https://localhost/index.html';
      a.textContent = '‹ App home';
      a.style.cssText = 'position:fixed;left:8px;bottom:calc(8px + env(safe-area-inset-bottom));z-index:2400;padding:.3rem .7rem;border-radius:.6rem;background:rgba(8,7,16,.8);border:1px solid rgba(255,255,255,.2);color:#e5e7eb;font:800 .7rem Figtree,system-ui,sans-serif;text-decoration:none;backdrop-filter:blur(6px);';
      document.body.appendChild(a);
    });
  })();

  function note(text) {
    try {
      var el = document.createElement('div');
      el.textContent = text;
      el.style.cssText = 'position:fixed;left:50%;bottom:calc(14px + env(safe-area-inset-bottom));transform:translateX(-50%);z-index:9999;max-width:90vw;padding:.55rem 1rem;border-radius:999px;background:rgba(10,8,22,.92);border:1px solid rgba(255,255,255,.2);color:#fff;font:800 .8rem Figtree,system-ui,sans-serif;box-shadow:0 8px 24px rgba(0,0,0,.5);transition:opacity .4s;';
      document.body.appendChild(el);
      setTimeout(function () { el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 500); }, 3200);
    } catch (e) {}
  }

  if (window.Capacitor || window.SNLDesktop || (location.hostname === 'localhost' && !location.port && location.protocol === 'https:')) return; // the Android app already carries every file inside it
  if (!('serviceWorker' in navigator)) return;
  var secure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
  if (!secure) return;

  navigator.serviceWorker.addEventListener('message', function (e) {
    var d = e.data || {};
    if (d.type === 'snl-ready') {
      var first = !localStorage.getItem('snl_offline_ready');
      try { localStorage.setItem('snl_offline_ready', d.version); } catch (x) {}
      if (first) note('Ready to play offline');
      window.dispatchEvent(new CustomEvent('snl-offline-ready', { detail: d }));
    } else if (d.type === 'snl-pack-updated') {
      window.dispatchEvent(new CustomEvent('snl-pack-updated', { detail: d }));
    }
  });

  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (reg) {
      // look for a newer file set whenever the app comes back to the front
      var check = function () { if (navigator.onLine && reg.active) reg.active.postMessage('snl-check'); };
      document.addEventListener('visibilitychange', function () { if (!document.hidden) check(); });
      setTimeout(check, 4000);
    }).catch(function () {});
    // keep the downloaded game files from being evicted when the phone is short on space
    if (navigator.storage && navigator.storage.persist) navigator.storage.persist().catch(function () {});
  });
})();
