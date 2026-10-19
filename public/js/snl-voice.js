/* Voice chat for online rooms. The mic button joins voice (and unmutes); tapping it again mutes / unmutes.
   Two ways to carry the audio, chosen by an admin in GameSet → Settings and given to the page as SNL.voice.mode:
     'p2p'      free: every phone talks directly to every other (WebRTC). The server only passes offers / answers / ICE messages.
     'livekit'  a hosted LiveKit room: the server only signs a token, LiveKit carries the audio.
   Never loaded on the TV screen or in party mode. Exposes window.SNLVoice = { joined(), muted(), leave(), speaking() }
   and fires a 'snl-speaking' event when who-is-talking changes (snl-play.js draws the glow around their token / card). */
(function () {
  'use strict';
  var C = window.SNL || {}, V = C.voice || {};
  if (!V.mode || V.mode === 'off' || C.tv || C.isParty || !C.meId) return;
  var btn = document.getElementById('micBtn');
  if (!btn || !(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) { if (btn) btn.style.display = 'none'; return; }
  var U = (C.urls || {}).voice || {};
  var speaking = {}, joined = false, muted = true, busy = false, impl = null;

  function toast(msg, ms, color) { try { if (window.snlToast) window.snlToast(msg, ms || 3200, color); } catch (e) {} }
  function emit() { try { window.dispatchEvent(new CustomEvent('snl-speaking')); } catch (e) {} }
  function setSpeaking(id, on) { id = +id; if (!!speaking[id] === !!on) return false; if (on) speaking[id] = 1; else delete speaking[id]; return true; }

  function api(url, method, body) {
    return fetch(url, {
      method: method || 'GET',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': C.csrf },
      body: body ? JSON.stringify(body) : undefined,
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (data) {
        if (!r.ok) { var e = new Error(data.error || 'Voice is not available right now.'); e.status = r.status; throw e; }
        return data;
      });
    });
  }
  function getMic() {
    return navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true }, video: false });
  }

  function ui() {
    btn.classList.toggle('off', !joined);
    btn.classList.toggle('muted', joined && muted);
    btn.classList.toggle('live', joined && !muted);
    btn.setAttribute('aria-pressed', joined && !muted ? 'true' : 'false');
    btn.title = !joined ? 'Join voice chat' : muted ? 'You are muted — tap to talk' : 'You are live — tap to mute';
  }

  // ───────────────────────── free: phone to phone ─────────────────────────
  function p2p() {
    var me = +C.meId, ice = [], pcs = {}, pending = {}, audios = {}, meters = {}, missing = {}, local = null, ac = null, pollT = null, lvlT = null, queue = Promise.resolve(), stopped = false;

    function send(to, kind, data) { api(U.signal, 'POST', { to: to, kind: kind, data: data }).catch(function () {}); }

    function meter(key, stream, pid) {
      if (!ac || meters[key]) return;
      try {
        var src = ac.createMediaStreamSource(stream), an = ac.createAnalyser();
        an.fftSize = 512; src.connect(an);
        meters[key] = { an: an, buf: new Uint8Array(an.fftSize), pid: pid, self: key === 'me' };
      } catch (e) {}
    }
    function levels() {
      var changed = false;
      Object.keys(meters).forEach(function (k) {
        var m = meters[k]; m.an.getByteTimeDomainData(m.buf);
        var sum = 0; for (var i = 0; i < m.buf.length; i++) { var v = (m.buf[i] - 128) / 128; sum += v * v; }
        var on = Math.sqrt(sum / m.buf.length) > 0.035 && (!m.self || !muted);
        if (setSpeaking(m.pid, on)) changed = true;
      });
      if (changed) emit();
    }

    function drop(id) {
      var pc = pcs[id]; delete pcs[id]; delete pending[id]; delete meters[id];
      if (pc) { try { pc.onconnectionstatechange = null; pc.close(); } catch (e) {} }
      if (audios[id]) { try { audios[id].srcObject = null; audios[id].remove(); } catch (e) {} delete audios[id]; }
      if (setSpeaking(id, false)) emit();
    }
    function makePc(id) {
      var pc = new RTCPeerConnection({ iceServers: ice });
      pcs[id] = pc;
      local.getTracks().forEach(function (t) { pc.addTrack(t, local); });
      pc.onicecandidate = function (e) { if (e.candidate) send(id, 'ice', e.candidate.toJSON ? e.candidate.toJSON() : e.candidate); };
      pc.ontrack = function (e) {
        var stream = e.streams && e.streams[0] ? e.streams[0] : new MediaStream([e.track]);
        if (!audios[id]) { var a = document.createElement('audio'); a.autoplay = true; a.setAttribute('playsinline', ''); a.style.display = 'none'; document.body.appendChild(a); audios[id] = a; }
        audios[id].srcObject = stream;
        var pp = audios[id].play(); if (pp && pp.catch) pp.catch(function () {});
        meter(id, stream, id);
      };
      pc.onconnectionstatechange = function () {
        var st = pc.connectionState;
        if (st === 'failed' || st === 'closed') drop(id);
        else if (st === 'disconnected') setTimeout(function () { if (pcs[id] === pc && pc.connectionState === 'disconnected') drop(id); }, 5000);
      };
      return pc;
    }
    function flush(id) {
      var pc = pcs[id]; if (!pc || !pc.remoteDescription) return;
      (pending[id] || []).splice(0).forEach(function (c) { pc.addIceCandidate(c).catch(function () {}); });
    }
    function initiate(id) {
      var pc = makePc(id);
      pc.createOffer().then(function (o) { return pc.setLocalDescription(o).then(function () { send(id, 'offer', { type: o.type, sdp: o.sdp }); }); }).catch(function () { drop(id); });
    }
    function handle(m) {
      var id = +m.from;
      if (m.kind === 'offer') {
        if (pcs[id]) drop(id);
        var pc = makePc(id);
        return pc.setRemoteDescription(m.data).then(function () { flush(id); return pc.createAnswer(); })
          .then(function (a) { return pc.setLocalDescription(a).then(function () { send(id, 'answer', { type: a.type, sdp: a.sdp }); }); })
          .catch(function () { drop(id); });
      }
      var cur = pcs[id];
      if (!cur) return Promise.resolve();
      if (m.kind === 'answer' && cur.signalingState === 'have-local-offer') return cur.setRemoteDescription(m.data).then(function () { flush(id); }).catch(function () { drop(id); });
      if (m.kind === 'ice') {
        if (cur.remoteDescription) cur.addIceCandidate(m.data).catch(function () {});
        else (pending[id] = pending[id] || []).push(m.data);
      }
      return Promise.resolve();
    }
    function peersNow(list) {
      var others = list.filter(function (x) { return +x !== me; }).map(Number);
      others.forEach(function (id) {
        missing[id] = 0;
        if (!pcs[id] && me > id) initiate(id); // the higher id always makes the first call, so two phones never call each other at once
      });
      Object.keys(pcs).forEach(function (k) {
        var id = +k;
        if (others.indexOf(id) === -1 && ++missing[id] >= 2) drop(id);
      });
    }
    function poll() {
      if (stopped) return;
      api(U.poll).then(function (r) {
        peersNow(r.peers || []);
        (r.messages || []).forEach(function (m) { queue = queue.then(function () { return handle(m); }); });
      }).catch(function (e) {
        if (e.status === 410 && !stopped) { Object.keys(pcs).forEach(drop); api(U.join, 'POST', {}).then(function (j) { peersNow(j.peers || []); }).catch(function () {}); }
      });
    }

    return {
      start: function () {
        return getMic().then(function (stream) {
          local = stream;
          return api(U.join, 'POST', {}).catch(function (e) { stream.getTracks().forEach(function (t) { t.stop(); }); throw e; });
        }).then(function (j) {
          ice = j.ice || [];
          ac = new (window.AudioContext || window.webkitAudioContext)();
          if (ac.resume) ac.resume().catch(function () {});
          meter('me', local, me);
          peersNow(j.peers || []);
          pollT = setInterval(poll, 1000);
          lvlT = setInterval(levels, 180);
        });
      },
      mute: function (m) { if (local) local.getAudioTracks().forEach(function (t) { t.enabled = !m; }); },
      leave: function () {
        stopped = true; clearInterval(pollT); clearInterval(lvlT);
        Object.keys(pcs).forEach(drop);
        if (local) local.getTracks().forEach(function (t) { t.stop(); });
        try { ac && ac.close(); } catch (e) {}
        speaking = {}; emit();
        return api(U.leave, 'POST', {}).catch(function () {});
      },
    };
  }

  // ───────────────────────── LiveKit ─────────────────────────
  function loadSdk(url) {
    return new Promise(function (res, rej) {
      if (window.LivekitClient) return res(window.LivekitClient);
      var s = document.createElement('script'); s.src = url; s.async = true;
      s.onload = function () { window.LivekitClient ? res(window.LivekitClient) : rej(new Error('The LiveKit library loaded but is not usable.')); };
      s.onerror = function () { rej(new Error('The LiveKit library could not be loaded (check the library link in GameSet → Settings).')); };
      document.head.appendChild(s);
    });
  }
  function livekit() {
    var room = null;
    return {
      start: function () {
        var tok;
        return api(U.token, 'POST', {}).then(function (t) { tok = t; return loadSdk(t.sdk); }).then(function (L) {
          room = new L.Room({ adaptiveStream: false, dynacast: false, audioCaptureDefaults: { echoCancellation: true, noiseSuppression: true, autoGainControl: true } });
          room.on(L.RoomEvent.TrackSubscribed, function (track) { if (track.kind === 'audio') { var el = track.attach(); el.style.display = 'none'; document.body.appendChild(el); } });
          room.on(L.RoomEvent.TrackUnsubscribed, function (track) { track.detach().forEach(function (e) { e.remove(); }); });
          room.on(L.RoomEvent.ActiveSpeakersChanged, function (list) { speaking = {}; list.forEach(function (p) { if (p.identity) speaking[+p.identity] = 1; }); emit(); });
          room.on(L.RoomEvent.Disconnected, function () { if (joined) { joined = false; muted = true; speaking = {}; emit(); ui(); toast('Voice chat disconnected', 2600, '#f87171'); } });
          return room.connect(tok.url, tok.token);
        }).then(function () {
          if (room.startAudio) room.startAudio().catch(function () {});
          return room.localParticipant.setMicrophoneEnabled(true);
        });
      },
      mute: function (m) { if (room) room.localParticipant.setMicrophoneEnabled(!m).catch(function () {}); },
      leave: function () { speaking = {}; emit(); try { room && room.disconnect(); } catch (e) {} return Promise.resolve(); },
    };
  }

  // ───────────────────────── the button ─────────────────────────
  function fail(e) {
    var denied = e && (e.name === 'NotAllowedError' || e.name === 'SecurityError');
    var none = e && (e.name === 'NotFoundError' || e.name === 'OverconstrainedError');
    toast(denied ? 'Microphone blocked. Allow it for this site in your browser settings, then tap the mic again.'
      : none ? 'No microphone found on this device.'
      : (e && e.message) || 'Voice chat could not start.', 5200, '#f87171');
  }
  btn.addEventListener('click', function () {
    if (busy) return;
    busy = true;
    var done = function () { busy = false; ui(); };
    if (joined) { muted = !muted; impl.mute(muted); toast(muted ? 'Microphone muted' : 'Microphone on', 1400, muted ? '#9ca3af' : '#34d399'); return done(); }
    btn.classList.add('busy');
    impl = V.mode === 'livekit' ? livekit() : p2p();
    impl.start().then(function () { joined = true; muted = false; toast('You are on voice chat', 1800, '#34d399'); })
      .catch(function (e) { var i = impl; impl = null; if (i) { try { i.leave(); } catch (x) {} } fail(e); })
      .then(function () { btn.classList.remove('busy'); done(); });
  });
  window.addEventListener('pagehide', function () { if (impl && joined && navigator.sendBeacon) { /* the server forgets a silent phone after ~15 s */ } });

  window.SNLVoice = {
    joined: function () { return joined; },
    muted: function () { return muted; },
    speaking: function () { return Object.keys(speaking).map(Number); },
    leave: function () { if (!impl) return Promise.resolve(); var i = impl; impl = null; joined = false; muted = true; ui(); return i.leave(); },
  };
  ui();
})();
