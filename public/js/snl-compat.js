/* Old TV / set-top-box browsers.
   1) The game screen is laid out with container units (cqw / cqh) and aspect-ratio. Browsers older than Chrome ~105 ignore them,
      which collapses the board into a thin strip. Where they are missing, rewrite those units to CSS variables that we set from the
      screen size, and size the board in pixels.
   2) Low-power screens (TVs, few CPU cores, little memory) get html.lite: no animated blur, no backdrop blur, fewer floating effects.
   Loaded in <head> after the page's <style> blocks, so it runs before anything is painted. */
(function () {
  var root = document.documentElement;
  var sup = function (p, v) { try { return !!(window.CSS && CSS.supports && CSS.supports(p, v)); } catch (e) { return false; } };
  var ua = navigator.userAgent || '';
  var tvish = /SMART-TV|SmartTV|Android TV|AFT[A-Z]|BRAVIA|Tizen|Web0S|WebOS|HbbTV|VIDAA|Vitron|CrKey|GoogleTV|NetCast|Roku/i.test(ua);
  var weak = (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) || (navigator.deviceMemory && navigator.deviceMemory <= 2);
  if (tvish || weak || /[?&]lite=1/.test(location.search)) root.classList.add('lite');

  var cq = sup('width', '1cqw'), ar = sup('aspect-ratio', '1 / 1');
  if (cq && ar) return;

  if (!cq) {
    var styles = document.getElementsByTagName('style');
    for (var i = 0; i < styles.length; i++) {
      styles[i].textContent = styles[i].textContent.replace(/(-?\d*\.?\d+)cq([wh])\b/g, 'calc($1 * var(--cq$2))');
    }
  }

  function fit() {
    var w = window.innerWidth, h = window.innerHeight;
    if (w < 1024 && h > w) { var t = w; w = h; h = t; } // portrait phones show the stage rotated
    root.style.setProperty('--cqw', (w / 100) + 'px');
    root.style.setProperty('--cqh', (h / 100) + 'px');
    var b = document.getElementById('boardWrap');
    if (!b) return;
    var cs = getComputedStyle(root);
    var bw = parseFloat(cs.getPropertyValue('--bw')) || 1264, bh = parseFloat(cs.getPropertyValue('--bh')) || 848;
    var nav = Math.min(52, Math.max(30, h * 0.086));
    var width = Math.min(w, (h - nav) * bw / bh);
    b.style.width = width + 'px';
    b.style.height = (width * bh / bw) + 'px';
  }
  fit();
  document.addEventListener('DOMContentLoaded', fit);
  window.addEventListener('resize', fit);
  window.addEventListener('orientationchange', function () { setTimeout(fit, 200); });
})();
