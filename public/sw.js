/* Snakes & Liquor — service worker.
   Keeps the whole party game on the device so it opens and plays with no connection:
     • install   → downloads the file list from /offline/assets.json into a versioned cache (pages, scripts, fonts, avatars, audio, board art, game pack)
     • pages     → network first (fresh when online), cached copy when the network is slow or gone
     • files     → cache first (they're versioned), refreshed in the background
     • game pack → cache first + background refresh, so new cards arrive by themselves when online
   Online rooms, the admin area and every POST always go to the network. */
const PREFIX = 'snl-';
const NET_TIMEOUT = 3500; // a "connected but useless" network falls back to the cached page after this long

let cacheName = null;
const meta = () => caches.open(PREFIX + 'meta');

async function currentCache() {
  if (cacheName) return cacheName;
  const m = await meta();
  const hit = await m.match('current');
  cacheName = hit ? await hit.text() : PREFIX + 'boot';
  return cacheName;
}

async function say(msg) {
  const all = await self.clients.matchAll({ includeUncontrolled: true });
  all.forEach((c) => c.postMessage(msg));
}

const HEAVY = /^\/(avatars|audio|img)\//; // downloaded in the background after the app is already usable

async function fetchManifest() {
  const res = await fetch('/offline/assets.json', { cache: 'no-store' });
  return res.json();
}

/** Step 1 (blocks install, small): pages, the game pack, scripts, fonts, icons — enough to start a party game. */
async function installCore(manifest) {
  const name = PREFIX + manifest.version;
  const cache = await caches.open(name);
  await Promise.all([...(manifest.pages || []), manifest.pack].map(async (u) => {
    const r = await fetch(u, { cache: 'reload', credentials: 'same-origin' });
    if (!r.ok) throw new Error('core file failed: ' + u);
    await cache.put(u, r);
  }));
  await fillFiles(cache, (manifest.urls || []).filter((u) => !HEAVY.test(u)));
  const m = await meta();
  await m.put('current', new Response(name));
  cacheName = name;
  for (const k of await caches.keys()) if (k.startsWith(PREFIX) && k !== name && k !== PREFIX + 'meta') await caches.delete(k);
  return cache;
}

/** Step 2 (background): avatars, board art, sounds. Safe to run again — it only fetches what is missing. */
async function installRest(manifest, cache) {
  const files = (manifest.urls || []).filter((u) => HEAVY.test(u));
  await fillFiles(cache, files, (done) => say({ type: 'snl-progress', done, total: files.length }));
  say({ type: 'snl-ready', version: manifest.version });
}

async function fillFiles(cache, files, onProgress) {
  const queue = files.slice();
  let done = 0;
  const work = async () => {
    while (queue.length) {
      const u = queue.shift();
      try {
        if (!(await cache.match(u))) {
          const r = await fetch(u, { credentials: 'same-origin' });
          if (r.ok) await cache.put(u, r);
        }
      } catch (_) { /* best effort — retried on the next check */ }
      done++;
      if (onProgress && done % 40 === 0) onProgress(done);
    }
  };
  await Promise.all([work(), work(), work(), work()]);
}

let pendingRest = null;
async function install() {
  const manifest = await fetchManifest();
  const cache = await installCore(manifest);
  pendingRest = installRest(manifest, cache);
  return pendingRest;
}

self.addEventListener('install', (e) => {
  // become active as soon as the small core set is stored; the heavy files keep downloading in the background
  e.waitUntil((async () => {
    try {
      const manifest = await fetchManifest();
      const cache = await installCore(manifest);
      pendingRest = installRest(manifest, cache);
    } catch (err) { try { await (await meta()).put('install-error', new Response(String((err && err.message) || err))); } catch (_) {} /* offline install attempt — the page will ask again */ }
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', (e) => {
  e.waitUntil(self.clients.claim().then(() => pendingRest));
});

self.addEventListener('message', (e) => {
  if (e.data === 'snl-check') {
    // The page asks "is there a newer file set?" — compare versions and re-install quietly if so.
    e.waitUntil((async () => {
      try {
        const r = await fetch('/offline/assets.json', { cache: 'no-store' });
        const m = await r.json();
        if (PREFIX + m.version !== (await currentCache())) await install();
        else await installRest(m, await caches.open(await currentCache())); // same version: just top up anything that never finished downloading
      } catch (_) { /* offline — nothing to do */ }
    })());
  }
});

const isStatic = (url) => /^\/(build|js|css|fonts|avatars|audio|img|icons|uploads)\//.test(url.pathname) || /\.(svg|ico|webmanifest|png|jpg|jpeg|webp|woff2)$/.test(url.pathname);
const neverCache = (url) => /^\/(gameset|login|logout|admin)/.test(url.pathname) || /^\/play\/[^/]+\/(state|roll|decide|start|leave|react|rematch)$/.test(url.pathname);

async function fromCache(req, opts) {
  const c = await caches.open(await currentCache());
  return (await c.match(req, opts)) || null;
}

function timeout(ms) { return new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), ms)); }

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin || neverCache(url)) return;

  // Pages: fresh when possible, cached when not.
  if (req.mode === 'navigate') {
    e.respondWith((async () => {
      try {
        const res = await Promise.race([fetch(req), timeout(NET_TIMEOUT)]);
        if (res && res.ok && !/^\/play\/(?!local)[^/]+$/.test(url.pathname)) {
          const c = await caches.open(await currentCache());
          c.put(req, res.clone());
        }
        return res;
      } catch (_) {
        const hit = (await fromCache(req)) || (await fromCache(new Request(url.pathname + url.search)))
          || (await fromCache(url.pathname === '/new' ? '/new?type=party' : '/'));
        // An online-room link with no network → the friendly offline page (it offers party mode + "continue my game").
        if (/^\/play\/(?!local)[^/]+$/.test(url.pathname) || /^\/join/.test(url.pathname)) return (await fromCache('/offline.html')) || hit || Response.error();
        return hit || (await fromCache('/offline.html')) || Response.error();
      }
    })());
    return;
  }

  // The game pack: freshest wins — the network (so new cards show up at once), the stored copy when offline or slow.
  if (url.pathname === '/offline/pack.json') {
    e.respondWith((async () => {
      const hit = await fromCache(req);
      try {
        const res = await Promise.race([fetch(req), timeout(2500)]);
        if (res && res.ok) {
          const c = await caches.open(await currentCache());
          const old = hit ? await hit.clone().json().catch(() => null) : null;
          const fresh = await res.clone().json().catch(() => null);
          await c.put(req, res.clone());
          if (old && fresh && old.version !== fresh.version) say({ type: 'snl-pack-updated', version: fresh.version });
          return res;
        }
      } catch (_) { /* offline or slow — use what is stored */ }
      return hit || Response.error();
    })());
    return;
  }

  // Versioned static files: cache first.
  if (isStatic(url)) {
    e.respondWith((async () => {
      const hit = await fromCache(req);
      if (hit) return hit;
      try {
        const res = await fetch(req);
        if (res.ok) (await caches.open(await currentCache())).put(req, res.clone());
        return res;
      } catch (_) {
        // offline and this exact version isn't stored: an older copy of the same file is better than nothing
        return (await fromCache(req, { ignoreSearch: true })) || Response.error();
      }
    })());
  }
});
