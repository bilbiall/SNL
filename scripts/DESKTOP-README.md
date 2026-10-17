# Windows desktop app

Electron wrapper around the same offline game as the APK (`php artisan snl:export-app`). No network needed from first launch.

## Build
```
npm run desktop
```
Exports the game into `desktop/www`, runs electron-builder (unsigned, x64), and copies to `public/downloads/`:
- `Snakes-and-Liquor-Setup-<ver>.exe` (installer)
- `Snakes-and-Liquor-Portable-<ver>.exe`
- `desktop-version.json` (read by the in-app update bar)

Try it without packaging: `npm run desktop:dev`.

## Release a new version
1. Bump `desktop/app-version.json` (raise `versionCode`).
2. `npm run desktop`
3. Upload the two `.exe` files and `desktop-version.json` to `public/downloads/` on the server (cPanel File Manager). The `.exe` files are git-ignored.
4. `/download/windows` serves the installer; `/app` and the home "Get the app" card show the Windows link once `desktop-version.json` exists.

## Notes
- The installer is unsigned: Windows SmartScreen shows "More info → Run anyway".
- Saves live in the app's localStorage (`%APPDATA%\Snakes & Liquor`); updating by installing over the old version keeps them.
- F11 toggles fullscreen.
