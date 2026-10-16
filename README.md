# Snakes & Liquor 🐍🥃

A party board game: roll the dice, dodge the snakes, and find out who takes the shot, picks a friend, states a fact or breaks a rule. Play on **one phone** (party mode) or **online** with a 5-letter room code — no accounts for players.

Built on Laravel 12. Forked from the Pesa Mine arcade engine; money, wagers and XP are gone, replaced by **cards**.

## What's in the box

| Piece | Where |
|---|---|
| Table engine (turns, cards, prompts, win) | `app/Services/GameEngine.php` |
| Game vocabulary (modes, tile kinds, powers, timings) | `config/snl.php` |
| Play screen | `resources/views/play.blade.php` + `public/js/snl-play.js` |
| Admin (**GameSet**) — boards, squares, cards | `/gameset` |
| Starter content | `database/seeders` (board, cards, admin) |
| Plan & decisions | `docs/BUILD-PLAN.md` |

## Modes

* **Drinks** 🥃 — 18+ only, with an age confirmation.
* **Family** 🎈 — zero alcohol.
* **Spicy** 🔥 — reserved (disabled).

Same board, same rules — only the card deck changes. Intensity (Chill / Normal / Wild) filters which cards can appear.

## Local setup

```bash
composer install
cp .env.example .env && php artisan key:generate
# create an empty database named snakesandliquor, then:
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

The seeder creates an admin from `SNL_ADMIN_EMAIL` / `SNL_ADMIN_PASSWORD` in `.env` — **change them before deploying.** Sign in at `/login`.

## Your own board art

1. GameSet → **Boards** → **New board**: upload the art and say how many squares it has.
2. Drag every numbered square onto its spot (desktop layout; the mobile-landscape tab is optional).
3. Set what each square does, then tick **Active**.

Positions are stored as a percentage of the image, so any artwork works.

## Deploying (cPanel)

* Point the domain at `public/`.
* `.env`: production DB, `APP_ENV=production`, `APP_DEBUG=false`, and keep `SESSION_DRIVER=file` / `CACHE_STORE=file` (the file cache provides the per-room action lock).
* `php artisan migrate --force && php artisan db:seed --force` (first time), `npm run build` (locally, then upload `public/build`).
* Make `storage/` and `bootstrap/cache/` writable; `public/uploads/boards` receives board art.
* Cron: `* * * * * php /path/to/artisan schedule:run` (purges idle rooms nightly).

## Responsible play

Drinks mode is for adults. Every screen reminds players that any drink can be swapped for water and that they should never drive after drinking.
