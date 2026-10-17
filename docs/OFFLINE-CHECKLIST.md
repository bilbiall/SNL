# Offline + app release checklist

Tick each line only after it has been verified (how is in brackets). [x] = verified · [~] = built and tested as far as possible here, but not exercised on a real device / in the full browser flow.

## Brand
- [x] Favicon + tab icon use `snakes-and-liquor-neon-serpent-512.png` (favicon.ico, 16/32 png) [served 200, correct image]
- [x] App icons: 48…512, maskable 192/512, apple-touch 180 [files exist, manifest lists them]
- [~] Android launcher icons (all densities + adaptive) built from the same art [`aapt dump badging` / unzip] — built from the art; not seen on a device
- [x] Top-nav logo uses the artwork

## Installable web app (PWA)
- [~] `manifest.webmanifest` valid (name, start_url, icons, shortcuts) [Chrome installability] — served and linked; Chrome install prompt not triggered in this test browser
- [x] Service worker registers, installs core set, then downloads avatars/audio/art in the background [cache count]
- [x] Fonts self-hosted (no bunny.net dependency) [no external requests]
- [x] Pages work offline: `/`, `/new?type=party`, `/play/local`, `/offline.html` [server stopped, reload]
- [x] Update flow: bump a file → new version installs quietly, old cache removed
- [x] `sw.js` served no-cache; `.webmanifest` correct MIME (`.htaccess`)

## Rules engine in the browser
- [x] `snl-engine.js` matches `GameEngine.php` (node simulation: all prompt types, wheel pick, spin, shield, bluff, spotlight, recap)
- [x] State shape identical to the server's (key-structure comparison test)
- [x] 400 random games finish with a winner, no answer leaks

## Local-first party mode
- [x] Setup form builds the game on the device (works offline)
- [x] Game saved after every move; **Continue your game** on landing / offline page
- [x] Reload mid-prompt resumes exactly (pending prompt survives)
- [~] Rematch / Play again works locally — engine tested in node; button not clicked in a browser
- [x] Leaving a player works; game ends correctly

## Online fallback
- [~] Connection lost banner after repeated poll failures / `offline` event — not exercised in a browser — logic reviewed, engine conversion tested in node
- [~] **Carry on on this phone** converts the room into a local game (players, positions, powers, round, rule) — engine conversion (fromOnline) tested in node; the button flow itself not exercised in a browser
- [~] Banner clears when the connection returns — not exercised in a browser

## Landing page downloads
- [x] "Install app" button appears when the browser offers it; hidden inside the installed app
- [x] Android `.apk` download card with version + size (only if the file exists)
- [x] iPhone "Add to Home Screen" instructions; PC install hint
- [x] Online/join/admin links removed in the Android build

## Android APK (Capacitor, bundled)
- [x] `php artisan snl:export-app` bakes pages + pack + assets into `mobile/www`
- [~] Signed release APK built (`npm run apk`), `apksigner verify` OK (v2/v3) — apksigner v1+v2 verified; not installed on a device or emulator
- [~] Package id `com.snakesandliquor.app`, app name "Snakes & Liquor", correct icon — verified with aapt; not installed on a device or emulator
- [x] APK copied to `public/downloads/` with sha256 + `version.json`
- [x] Keystore stored outside the repo — **back it up**

## Ship
- [x] Docs: `docs/OFFLINE-APP.md`, `scripts/APK-README.md`
- [x] Regression: simulation + HTTP coverage + render checks still green
- [x] Committed and pushed to `bilbiall/SNL`

## Round 2 (smoother landing, start zone, late joiners, card polish, free-square events)
- [x] Landing token bounce is one smooth requestAnimationFrame animation (arcs, squash, celebrate, avatar swap on each landing)
- [x] Start zone: GameSet drag/resize editor, spread-out tokens, default on the START sign, works in the offline engine path
- [x] Add a player mid-game from the menu (PHP engine + route, JS engine, UI, tests)
- [x] Sounds: card deal whoosh, per-word ticks, button press; existing clips kept
- [x] Prompt cards (pick / shield / true-false / bluff / quickfire) + menu / confirm / win use the animated glass card style; pick uses avatar tiles
- [x] Free squares: Quickfire, Roll again, Gift, Group game (config chance + weights, 4 new card kinds, 100+ cards, both engines, tests)

## Round 3 (public games, sharing, SEO)
- [x] Public rooms: name + public flag, /rooms browser (live list, join, in-progress), rude-name/link filter, idle/finished rooms hidden — tested through the real controllers
- [x] Share component on landing / rooms / how-to-play / fun-facts / app page; share on the win screen and lobby
- [x] SEO: meta + canonical + robots per page, OG/Twitter card image, JSON-LD (SoftwareApplication, FAQ, HowTo), dynamic sitemap + robots.txt, noindex on utility pages
- [x] Content pages: /how-to-play and a family-safe /fun-facts quiz
- [x] The offline app build strips online-only links, the about/FAQ block and the share row

## Known limits (see OFFLINE-APP.md)
- Online rooms need a connection (the fallback converts them to pass-the-phone).
- A browser PWA that has never been opened online has nothing cached; the Android app does not have this problem.
- Windows `.msix` needs the live domain (PWABuilder) — the PWA install works today.
- iOS: Add to Home Screen only.
