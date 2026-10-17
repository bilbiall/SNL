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
  window.SNLUpdate = { check: check, info: me, show: function (res) { bar(res, true); } };

  function esc(t) { return String(t == null ? '' : t).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  /** Pop-up card: current → new version, what's new, and an Update button (or "Later"). */
  function bar(res, force) {
    var remote = res.remote, site = res.me.site;
    var seen = null; try { seen = localStorage.getItem('snl_update_dismissed'); } catch (e) {}
    if ((!force && seen === String(remote.versionCode)) || document.getElementById('snlUpdateBar')) return;
    var notes = (remote.notes || []).slice(0, 6).map(function (n) { return '<li style="margin:.2rem 0;">' + esc(n) + '</li>'; }).join('');
    var size = remote.sizeBytes ? ' · ' + (remote.sizeBytes / 1048576).toFixed(1) + ' MB' : '';
    var el = document.createElement('div');
    el.id = 'snlUpdateBar';
    el.style.cssText = 'position:fixed;inset:0;z-index:2500;display:flex;align-items:flex-end;justify-content:center;padding:16px 16px calc(16px + env(safe-area-inset-bottom));background:rgba(5,4,12,.5);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);font-family:Figtree,system-ui,sans-serif;';
    el.innerHTML = '<div style="width:100%;max-width:23rem;border-radius:1.2rem;padding:1rem 1.1rem;background:linear-gradient(160deg,#1f1a3a,#12101f);border:1px solid rgba(255,255,255,.16);box-shadow:0 20px 50px rgba(0,0,0,.6);color:#fff;">'
      + '<div style="font-weight:900;font-size:1rem;">New update available</div>'
      + '<div style="margin:.25rem 0 .6rem;font-size:.78rem;color:#94a3b8;font-weight:700;">v' + esc(res.me.versionName) + ' &rarr; <span style="color:#6ee7b7;">v' + esc(remote.versionName) + '</span>' + size + '</div>'
      + (notes ? '<div style="font-size:.7rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:#cbd5e1;">What&#39;s new</div><ul style="margin:.25rem 0 .7rem;padding-left:1.1rem;font-size:.82rem;color:#e2e8f0;">' + notes + '</ul>' : '')
      + '<div style="display:flex;gap:.5rem;"><a href="' + site + DOWNLOAD_PATH + '" style="flex:1;text-align:center;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:.8rem;padding:.6rem;text-decoration:none;font-weight:900;font-size:.86rem;">Get the update</a>'
      + '<button type="button" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);color:#e5e7eb;border-radius:.8rem;padding:.6rem .9rem;font:800 .82rem inherit;cursor:pointer;">Later</button></div>'
      + '<div style="margin-top:.5rem;font-size:.68rem;color:#94a3b8;">Install it over this one — your saved game stays.</div></div>';
    el.querySelector('button').onclick = function () { try { localStorage.setItem('snl_update_dismissed', String(remote.versionCode)); } catch (e) {} el.remove(); };
    document.body.appendChild(el);
  }

  // automatic check on every screen except the table (nothing should pop up mid-game; the menu has a button for that)
  if (!/\/play(\.html)?$/.test(location.pathname)) {
    check().then(function (res) { if (res.newer) bar(res); });
  }
})();
