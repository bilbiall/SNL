/* Android app only: tells you when a newer version of the app is available.
   The app files live inside the APK, so a website update can't reach it — a new APK has to be installed over the old one
   (no uninstall needed; your saved game stays). This reads the site's /downloads/version.json whenever the app is open online.
     • an "Update" bar appears on the home / setup screens
     • the game menu has "Check for updates" (window.SNLUpdate.check) */
(function () {
  'use strict';
  var desktop = !!window.SNLDesktop;
  var isApp = !!window.Capacitor || desktop || (location.hostname === 'localhost' && !location.port && location.protocol === 'https:');
  if (!isApp) return;
  var VERSION_FILE = desktop ? 'desktop-version.json' : 'version.json';
  var DOWNLOAD_PATH = desktop ? '/download/windows' : '/download/android';

  var info = null;
  function me() {
    if (info) return Promise.resolve(info);
    if (desktop) { info = { versionName: window.SNLDesktop.versionName, versionCode: window.SNLDesktop.versionCode, site: window.SNLDesktop.site }; return Promise.resolve(info); }
    return fetch('/app-info.json', { cache: 'no-store' }).then(function (r) { return r.ok ? r.json() : null; }).then(function (j) { info = j; return j; }).catch(function () { return null; });
  }

  /** Resolves { me, remote, newer } — remote is null when offline / the site can't be reached. */
  function check() {
    return me().then(function (mine) {
      if (!mine || !mine.site) return { me: mine, remote: null, newer: false };
      var ctl = new AbortController(), timer = setTimeout(function () { ctl.abort(); }, 6000);
      return fetch(mine.site + '/downloads/' + VERSION_FILE + '?t=' + Date.now(), { cache: 'no-store', signal: ctl.signal })
        .then(function (r) { clearTimeout(timer); return r.ok ? r.json() : null; })
        .then(function (remote) { return { me: mine, remote: remote, newer: !!(remote && remote.versionCode > mine.versionCode) }; })
        .catch(function () { clearTimeout(timer); return { me: mine, remote: null, newer: false }; });
    });
  }
  window.SNLUpdate = { check: check, info: me };

  function bar(res) {
    var remote = res.remote, site = res.me.site;
    var seen = null; try { seen = localStorage.getItem('snl_update_dismissed'); } catch (e) {}
    if (seen === String(remote.versionCode) || document.getElementById('snlUpdateBar')) return;
    var el = document.createElement('div');
    el.id = 'snlUpdateBar';
    el.style.cssText = 'position:fixed;left:50%;bottom:calc(16px + env(safe-area-inset-bottom));transform:translateX(-50%);z-index:2500;display:flex;align-items:center;gap:.7rem;max-width:92vw;padding:.55rem .6rem .55rem 1rem;border-radius:999px;background:rgba(16,185,129,.95);color:#fff;font:800 .85rem Figtree,system-ui,sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.5);';
    el.innerHTML = '<span>New version ' + (remote.versionName || '') + ' is ready</span><a style="background:#fff;color:#065f46;border-radius:999px;padding:.4rem .9rem;text-decoration:none;font-weight:900;" href="' + site + DOWNLOAD_PATH + '">Update</a><button type="button" aria-label="Dismiss" style="background:none;border:0;color:#fff;font-size:1.1rem;cursor:pointer;padding:0 .4rem;">×</button>';
    el.querySelector('button').onclick = function () { try { localStorage.setItem('snl_update_dismissed', String(remote.versionCode)); } catch (e) {} el.remove(); };
    document.body.appendChild(el);
  }

  // automatic check on every screen except the table (nothing should pop up mid-game; the menu has a button for that)
  if (!/\/play(\.html)?$/.test(location.pathname)) {
    check().then(function (res) { if (res.newer) bar(res); });
  }
})();
