# Offline play & the installable apps

Snakes & Liquor party mode (one phone, pass it around) runs **entirely on the device**. Online rooms still need a connection.

## How it works

| Piece | File | Job |
|---|---|---|
| Rules engine | `public/js/snl-engine.js` | A JavaScript port of `app/Services/GameEngine.php`. Same rules, same log format, so the table screen is unchanged. |
| Local game store | `public/js/snl-local.js` | Creates / saves / resumes a game in `localStorage` after **every move**; "Continue your game" reads it. |
| Game pack | `GET /offline/pack.json` (`app/Support/OfflinePack.php`) | Rules config, boards + squares, every card, word lists, avatar roster. Refreshes itself when online, so new cards just arrive. |
| Service worker | `public/sw.js` + `GET /offline/assets.json` | Keeps the pages, scripts, fonts, avatars, audio, board art and the pack on the phone. Versioned; updates quietly. |
| Table page | `GET /play/local` | The same `play.blade.php`, booted from the saved game instead of a server room. |
| Install glue | `public/js/pwa.js`, `public/manifest.webmanifest` | Install prompt, persistent storage, icons. |
| Android app | `mobile/` (Capacitor) + `php artisan snl:export-app` + `npm run apk` | Same pages baked in as static files — works from the very first launch with no data. |

## Scenarios

* **New game with no data (installed app / installed PWA):** works — everything is on the device. A PWA opened *for the first time* with no data can't work (nothing downloaded yet); the **Android app** can, because the files are inside the .apk.
* **Phone call / switching apps / OS kills the app:** the game is saved after every move. Reopen → home screen shows **Continue your game**. Turn timers don't exist in party mode, so nobody loses a turn.
* **Online room loses signal:** a banner appears ("Connection lost — reconnecting…"). If it doesn't come back, **Carry on on this phone** converts the last known table (players, positions, powers, round, rule) into a local pass-the-phone game. The room on the other phones stops.
* **Updates:** open the app online and it fetches the new file set / cards by itself. Old games keep working with the new content.

## Deploying the website side

1. `git pull`, `composer install --no-dev`, `npm ci && npm run build` (the Vite build is not in git), `php artisan migrate`, `php artisan optimize`.
2. Serve over **HTTPS** (service workers + install prompts need it).
3. Make sure `public/sw.js` is not cached by the host (the shipped `.htaccess` sets `Cache-Control: no-cache` for it).
4. The Android download lives in `public/downloads/` (`snakes-and-liquor.apk`, `version.json`). It is committed, so a `git pull` ships it. The landing page shows the download card automatically when the file exists.

## Rebuilding the Android app

```
php artisan snl:export-app     # bake the current game + content into mobile/www
npm run apk                    # sign + build → public/downloads/snakes-and-liquor.apk
```
See `scripts/APK-README.md`. The signing keystore lives **outside the repo** (`C:\snl-tools\keys`). **Back it up** — without it you can't ship updates to people who installed the app.
Bump the version in `mobile/app-version.json` before every release.

## Windows / PC

* Chrome and Edge show an **Install** icon in the address bar and the landing page has an **Install app** button — the PWA opens in its own window and works offline.
* A packaged `.msix` (Microsoft Store / sideload) can be made with PWABuilder **once the site is live on its real domain**: open pwabuilder.com, enter the URL, choose *Windows*. It uses `manifest.webmanifest` and the service worker as they are. Sideloading needs a code-signing certificate; the Store signs for you.

## iPhone / iPad

No downloads outside the App Store. The landing page tells people to use **Share → Add to Home Screen**; that PWA works offline too. Safari can clear a site's storage after ~7 days of non-use unless it is added to the Home Screen.

## Store policy note

Google Play and the Microsoft Store treat alcohol-related apps strictly. Keep the 18+ confirmation for Drinks mode, the responsible-drinking notice, and an honest content rating before submitting.

## Updating the installed Android app

The app's files live **inside the .apk**, so `git pull` on the website does not change an installed app (reloading does nothing either). To ship an update:

1. Bump `mobile/app-version.json` (`versionCode` must go up), then `php artisan snl:export-app` and `npm run apk`.
2. Commit + push (the new `public/downloads/snakes-and-liquor.apk` and `version.json` go with it) and `git pull` on the server.
3. People open the app online → a green **"New version … is ready — Update"** bar appears (it reads `/downloads/version.json` from the site set in `SNL_PUBLIC_URL`, default `https://snl.moski.org`). Tapping it downloads the new APK; installing it **over the old one** (no uninstall) updates the app and keeps their saved game. Always sign with the same keystore.

People using the website or the installed web app (PWA) get updates automatically — the service worker refreshes the files the next time they're online.
