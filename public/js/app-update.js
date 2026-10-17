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
  /** Small centred card: version change, what's new, one Update button. In the Android app the download happens inside the app. */
  function bar(res, force) {
    var remote = res.remote, site = res.me.site;
    var seen = null; try { seen = localStorage.getItem('snl_update_dismissed'); } catch (e) {}
    if ((!force && seen === String(remote.versionCode)) || document.getElementById('snlUpdateBar')) return;
    var plug = window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.AppUpdater;
    var notes = (remote.notes || []).slice(0, 4).map(function (n) { return '<li style="margin:.15rem 0;">' + esc(n) + '</li>'; }).join('');
    var size = remote.sizeBytes ? ' · ' + (remote.sizeBytes / 1048576).toFixed(1) + ' MB' : '';
    var el = document.createElement('div');
    el.id = 'snlUpdateBar';
    el.style.cssText = 'position:fixed;inset:0;z-index:2500;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(5,4,12,.55);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);font-family:Figtree,system-ui,sans-serif;';
    var btn = 'flex:1;border:0;border-radius:.7rem;padding:.5rem .6rem;font:900 .78rem Figtree,system-ui,sans-serif;text-align:center;text-decoration:none;cursor:pointer;';
    el.innerHTML = '<div style="width:100%;max-width:17.5rem;border-radius:1rem;padding:.85rem .9rem;background:linear-gradient(160deg,#1f1a3a,#12101f);border:1px solid rgba(255,255,255,.16);box-shadow:0 16px 40px rgba(0,0,0,.6);color:#fff;text-align:center;">'
      + '<div style="font-weight:900;font-size:.9rem;">Update available</div>'
      + '<div style="margin:.15rem 0 .5rem;font-size:.7rem;color:#94a3b8;font-weight:700;">v' + esc(res.me.versionName) + ' &rarr; <span style="color:#6ee7b7;">v' + esc(remote.versionName) + '</span>' + size + '</div>'
      + (notes ? '<ul style="margin:0 0 .6rem;padding-left:1rem;font-size:.72rem;color:#cbd5e1;text-align:left;line-height:1.35;">' + notes + '</ul>' : '')
      + '<div id="updBar" style="display:none;height:.35rem;border-radius:99px;background:rgba(255,255,255,.12);overflow:hidden;margin-bottom:.4rem;"><div id="updFill" style="height:100%;width:0;background:#10b981;transition:width .2s;"></div></div>'
      + '<div id="updMsg" style="min-height:.9rem;margin-bottom:.35rem;font-size:.68rem;color:#94a3b8;"></div>'
      + '<div style="display:flex;gap:.45rem;"><button type="button" id="updGo" style="' + btn + 'background:linear-gradient(135deg,#10b981,#059669);color:#fff;">Update now</button>'
      + '<button type="button" id="updLater" style="' + btn + 'background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);color:#e5e7eb;">Later</button></div></div>';
    document.body.appendChild(el);
    var go = el.querySelector('#updGo'), msg = el.querySelector('#updMsg');
    function close(remember) { if (remember) { try { localStorage.setItem('snl_update_dismissed', String(remote.versionCode)); } catch (e) {} } el.remove(); }
    el.querySelector('#updLater').onclick = function () { close(true); };
    go.onclick = function () {
      if (!plug || desktop) { location.href = site + DOWNLOAD_PATH; return; }          // no native helper (older app / desktop): the browser download
      go.disabled = true; go.style.opacity = '.6'; msg.textContent = 'Downloading…';
      el.querySelector('#updBar').style.display = 'block';
      try { plug.addListener('progress', function (p) { if (p.percent >= 0) { el.querySelector('#updFill').style.width = p.percent + '%'; msg.textContent = 'Downloading… ' + p.percent + '%'; } }); } catch (e) {}
      plug.install({ url: site + '/downloads/snakes-and-liquor.apk', sha256: remote.sha256 || '' }).then(function (r) {
        go.disabled = false; go.style.opacity = '1';
        if (r && r.needsPermission) { msg.textContent = 'Turn on "Allow from this source" for this app, come back and tap Update now again.'; el.querySelector('#updBar').style.display = 'none'; }
        else close(false);                       // Android's installer window takes over; the card has done its job
      }, function (e) {
        go.disabled = false; go.style.opacity = '1'; el.querySelector('#updBar').style.display = 'none';
        msg.textContent = (e && e.message ? e.message : 'Download failed') + ' · or use the browser link';
        go.onclick = function () { location.href = site + DOWNLOAD_PATH; }; go.textContent = 'Open in browser';
      });
    };
  }

  // automatic check on every screen except the table (nothing should pop up mid-game; the menu has a button for that)
  if (!/\/play(\.html)?$/.test(location.pathname)) {
    check().then(function (res) { if (res.newer) bar(res); });
  }
})();
