/* iPhone / iPad install prompt. iOS has no install API, so this shows a small bottom sheet that walks people through
   Share → Add to Home Screen (or "open in Safari" when they are inside Instagram, WhatsApp etc.).
   Shows on the landing / new-game / app pages only, never mid-game, never inside the apps or once installed.
   window.SNLIosInstall = { supported(), show() }  — home.blade.php uses it for its "Install on iPhone" button. */
(function () {
  'use strict';
  var ua = navigator.userAgent || '';
  var ios = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  var ipad = /iPad/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  var standalone = window.navigator.standalone === true || (window.matchMedia && matchMedia('(display-mode: standalone)').matches);
  var inApp = /FBAN|FBAV|FB_IAB|Instagram|Line\/|MicroMessenger|Snapchat|Twitter|musical_ly|Bytedance|TikTok|WhatsApp|GSA\/|LinkedInApp/i.test(ua); // in-app browsers can't add to the home screen
  var otherBrowser = /CriOS|FxiOS|EdgiOS|OPiOS/.test(ua);                                                                                    // Chrome / Firefox / Edge on iOS (iOS 16.4+ can add too)
  var KEY = 'snl_ios_install', DAYS = 14;

  var get = function () { try { return JSON.parse(localStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
  var set = function (o) { try { localStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {} };

  var supported = function () { return ios && !standalone && !window.Capacitor && !window.SNLDesktop && location.protocol !== 'app:'; };
  window.SNLIosInstall = { supported: supported, show: function () { return open(true); } };
  if (!supported()) return;

  var shareIcon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-5px"><path d="M12 15V3M8 7l4-4 4 4"/><path d="M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>';
  var plusIcon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#e5e7eb" stroke-width="2" stroke-linecap="round" style="vertical-align:-5px"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M12 8v8M8 12h8"/></svg>';

  function steps() {
    if (inApp) {
      return '<li>Tap the <b>⋯</b> or <b>Share</b> button of this app.</li><li>Choose <b>Open in Safari</b>.</li><li>Then follow the install steps there.</li>';
    }
    var where = ipad ? 'at the top of the screen' : (otherBrowser ? 'in the address bar or menu' : 'at the bottom of the screen');
    return '<li>Tap the <b>Share</b> button ' + shareIcon + ' ' + where + '.</li>' +
           '<li>Scroll down and tap <b>Add to Home Screen</b> ' + plusIcon + '.</li>' +
           '<li>Tap <b>Add</b>. Open <b>Snakes &amp; Liquor</b> from your home screen. It plays offline.</li>';
  }

  var el = null;
  function close(permanent) {
    var s = get(); s.at = Date.now(); if (permanent) s.never = true; set(s);
    if (el) { el.style.transform = 'translateY(110%)'; var e = el; el = null; setTimeout(function () { e.remove(); }, 350); }
  }

  function open(manual) {
    if (el || !supported()) return false;
    var arrow = (!ipad && !inApp && !otherBrowser) ? '<div id="snlIosArrow" style="text-align:center;font-size:1.7rem;line-height:1;margin-top:.35rem;animation:snlIosBob 1.1s ease-in-out infinite;color:#38bdf8">⬇</div>' : '';
    el = document.createElement('div');
    el.setAttribute('role', 'dialog'); el.setAttribute('aria-label', 'Install Snakes and Liquor');
    el.style.cssText = 'position:fixed;left:0;right:0;bottom:0;z-index:9990;padding:.9rem 1rem calc(.9rem + env(safe-area-inset-bottom));' +
      'background:linear-gradient(180deg,#1b1233,#0b0a16);border-top:1.5px solid rgba(255,255,255,.18);border-radius:1.2rem 1.2rem 0 0;' +
      'box-shadow:0 -12px 40px rgba(0,0,0,.6);color:#fff;font:600 .92rem/1.45 Figtree,system-ui,-apple-system,sans-serif;' +
      'transform:translateY(110%);transition:transform .35s ease;max-width:34rem;margin:0 auto;';
    el.innerHTML =
      '<style>@keyframes snlIosBob{0%,100%{transform:translateY(0)}50%{transform:translateY(7px)}}#snlIos li{margin:.35rem 0}#snlIos b{color:#fde68a}</style>' +
      '<div id="snlIos">' +
      '<div style="display:flex;align-items:center;gap:.7rem">' +
        '<img src="/icons/apple-touch-icon.png" alt="" width="46" height="46" style="border-radius:.8rem;box-shadow:0 4px 14px rgba(236,72,153,.4)">' +
        '<div style="flex:1"><div style="font-weight:900;font-size:1.02rem">Get Snakes &amp; Liquor on your iPhone</div>' +
        '<div style="color:#9ca3af;font-size:.8rem">Free · full screen · plays offline · no App Store needed</div></div>' +
        '<button id="snlIosX" aria-label="Close" style="border:0;background:rgba(255,255,255,.1);color:#fff;border-radius:50%;width:2rem;height:2rem;font-size:1.1rem;cursor:pointer">✕</button>' +
      '</div>' +
      '<ol style="margin:.6rem 0 .2rem 1.2rem;padding:0">' + steps() + '</ol>' + arrow +
      '<div style="display:flex;gap:.5rem;margin-top:.6rem">' +
        '<button id="snlIosOk" style="flex:1;border:0;border-radius:.8rem;padding:.6rem;font:900 .9rem inherit;color:#fff;background:linear-gradient(135deg,#f59e0b,#ec4899);cursor:pointer">Got it</button>' +
        (manual ? '' : '<button id="snlIosNo" style="border:1px solid rgba(255,255,255,.2);border-radius:.8rem;padding:.6rem .9rem;font:800 .8rem inherit;color:#cbd5e1;background:transparent;cursor:pointer">Don\'t show again</button>') +
      '</div></div>';
    document.body.appendChild(el);
    requestAnimationFrame(function () { requestAnimationFrame(function () { if (el) el.style.transform = 'translateY(0)'; }); });
    document.getElementById('snlIosX').onclick = function () { close(false); };
    document.getElementById('snlIosOk').onclick = function () { close(false); };
    var no = document.getElementById('snlIosNo'); if (no) no.onclick = function () { close(true); };
    return true;
  }

  // the automatic prompt: only on the pages people land on, a few seconds in, and not again for two weeks
  var page = location.pathname.replace(/\/+$/, '') || '/';
  if (['/', '/new', '/app', '/how-to-play', '/fun-facts'].indexOf(page) === -1) return;
  var st = get();
  if (st.never || (st.at && Date.now() - st.at < DAYS * 86400000)) return;
  window.addEventListener('load', function () { setTimeout(function () { if (!document.hidden) open(false); }, 5000); });
})();
