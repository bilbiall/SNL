/* Ads at the pauses only. SNLAds.show(name) resolves when it is fine to carry on — straight away if no ad is wanted or available.
   Called by the host starting a new game and by "Play again"; there is no call anywhere during play, so nothing can interrupt a turn. */
(function () {
  var KEY = 'snl-ads';
  var read = function () { try { return JSON.parse(sessionStorage.getItem(KEY)) || {}; } catch (e) { return {}; } };
  var write = function (o) { try { sessionStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {} };

  function wanted(st) {
    var c = window.SNL_ADS;
    if (!c || !window.adBreak || !window.adsbygoogle || window.adsbygoogle.loaded !== true) return false; // no id, blocked, offline, or still loading
    if ((st.games || 0) < c.freeGames) return false;                      // the first game(s) of a visit are free
    if (st.last && Date.now() - st.last < c.minGap * 1000) return false;  // never stack ads
    return true;
  }

  window.SNLAds = {
    /** @param {string} name e.g. 'new_game', 'play_again'. Always resolves, never throws. */
    show: function (name) {
      return new Promise(function (resolve) {
        var st = read(), go = wanted(st);
        st.games = (st.games || 0) + 1; write(st);
        if (!go) return resolve();
        var started = false, finished = false, t;
        var finish = function () { if (finished) return; finished = true; clearTimeout(t); resolve(); };
        t = setTimeout(function () { if (!started) finish(); }, 4000); // no ad began → never keep the player waiting
        try {
          window.adBreak({
            type: 'next', name: name || 'next',
            beforeAd: function () { started = true; },
            afterAd: function () { var s = read(); s.last = Date.now(); write(s); },
            adBreakDone: finish,
          });
        } catch (e) { finish(); }
      });
    },
  };
})();
