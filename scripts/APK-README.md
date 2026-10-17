# Android app (signed APK)

Capacitor 6 app (`mobile/`) that bundles the game's web files (`mobile/www`) inside the APK, so it opens and plays with no network — even on first launch.

## Rebuild
```
npm run apk          # = node scripts/build-apk.mjs
```
It runs `cap sync`, `gradlew assembleRelease`, verifies the signature (apksigner v2/v3) and zip alignment, then writes:
`public/downloads/snakes-and-liquor.apk`, `.apk.sha256`, `version.json`.

Needs the toolchain in `C:\snl-tools` (JDK 17, Android SDK 34 — installed by `C:\snl-tools\setup.sh`). Icons/splash: `node scripts/gen-android-icons.mjs` (from `public/snakes-and-liquor-neon-serpent-512.png`).

## Web files
Put the built web app in `mobile/www/` (it needs an `index.html`), then `npm run apk`.

## Version
Edit `mobile/app-version.json` (`versionName`, and **increase `versionCode` every release** — Android refuses updates with a lower/equal code).

## Keystore — BACK IT UP
`C:\snl-tools\keys\snakes-and-liquor.jks` + `keystore.properties` (passwords). They are NOT in git.
**If you lose this keystore, existing users can never update the app** (a new key = a different app). Copy both files somewhere safe (password manager / encrypted drive). Override the location with env `SNL_KEYSTORE_PROPS`.
