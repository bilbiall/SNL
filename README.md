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

## How a square plays

| Square | What happens |
|---|---|
| **You do it / Pick a friend / Everyone / Duel / Swap / Rule** | A card for the table (the roller picks the target where it says so). |
| **True or false** | The game states a fact; the roller calls it. Right = pass. Wrong = a penalty (set per card: 1 or 2 units — a sip/shot, or a forfeit in Family mode). The answer never leaves the server until the reveal. |
| **Fact or bluff** | The roller says a fact out loud (true or invented). Anyone can *call bluff*. A neutral judge (or the table, in party mode) rules: proven → the caller pays; caught → the roller pays. Nobody calls → safe. |
| **Spotlight** | A creative prompt on a clock — a movie for the moment, a song, a hot take. Fail it and take the penalty. |
| **Mystery spin** | Spins into any of the above. |
| **Free square** | Calm by default — a breather or a hydration nudge. But about 4 in 10 landings now do something small and fun: a **Quickfire** 10-second challenge, a **Roll again** bonus turn, a free **Shield** gift, or a **Group game** everybody joins. Tune it in `config/snl.php` (`free_event_chance`, `free_events`) and write more lines in GameSet → Cards (kinds *Quickfire*, *Group game*, *Free gift*, *Roll again*). |
| **Love ladder** | A sweet moment — compliments, memories, toasts. No penalty. |

Intensity (**Chill / Normal / Spicy**) decides which cards can appear and how hard penalties bite.

## Keeping it fresh — content that grows

* **Cards** (GameSet → Cards) are tagged by kind, mode (Drinks, Family, or *All modes*) and intensity.
* **Templates**: card text can contain `{you}`, `{other}` (a random player) and any word-list slot such as `{scene}`, `{vibe}`, `{genre}`, `{hot_topic}`. One card — *"Recommend a movie for {scene}"* — reads differently every time.
* **Words** (GameSet → Words) hold the slot lists. Add hundreds; add brand-new slots just by naming them.
* Bulk-add both: one per line. True-or-false lines look like `T | statement | optional explanation`.

## Players, start zone and joining late

* **Start zone** (GameSet → Boards → *Start zone*): drag a green box over the area where players wait before their first roll (the START sign works well). They spread out inside it instead of stacking on square 1, then hop onto the board when they roll. Remove it to go back to square 1.
* **Add a player mid-game** (party mode): hamburger menu → *Add a player*. They take a seat at the start zone and play last in the order (up to 12 players).

## Boards

The game ships with **Neon Night** (80 squares, 5 ladders, 5 snakes) — `database/seeders/data/snl-night.php` holds its artwork path, how the 6 rows of squares sit on the image, every square type and the ladder/snake links. `php artisan db:seed --class=BoardSeeder` creates it (once) and makes it the only active board.

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

## Credits & licences

* **Hero avatars** — 12 original comic-style heroes drawn in code (`scripts/lib/heroes.mjs`). They are not based on any Marvel/DC/other character and use no third-party names or emblems.
* **Retro avatars** — the pixel-art character style from [DiceBear](https://www.dicebear.com/styles/pixel-art/) (CC0 / public domain). 16 looks × 7 skin shades are generated by `npm run avatars` (`scripts/build-avatars.mjs`) into `public/avatars/` and committed, so production needs no extra build.
* **Rookie avatars** — 12 original streetwear characters (headphones, bucket hats, hijab, locs, buns…) drawn in code (`scripts/lib/genz.mjs`).
* **Bar Crew avatars** — 12 original drinking-game characters (bartender, sommelier, brewmaster, DJ, pirate captain…) with props, drawn in code (`scripts/lib/barcrew.mjs`), 7 skin shades each.
* **Party Animals avatars** — 12 original animal mascots holding drinks, drawn in code (`scripts/lib/animals.mjs`), in 7 neon colourways instead of skin shades.
* **Illustrated avatars** — [DiceBear Lorelei](https://www.dicebear.com/styles/lorelei/) by Lisa Wischofsky (CC0 / public domain), 12 looks with recoloured hair.
* **Icons** — [game-icons.net](https://game-icons.net) by Lorc, Delapouite and contributors, licensed [CC BY 3.0](https://creativecommons.org/licenses/by/3.0/). Compiled into `public/icons.svg` from `resources/icons.json` (`npm run icons`); to add or swap an icon, edit that map and rebuild. Please keep this credit.
* **Landing-page photos** (`public/img/landing/`) — free-to-use stock from [Pexels](https://www.pexels.com) under the [Pexels licence](https://www.pexels.com/license/) (no attribution required): the red neon "Cocktails" sign (photo 3566120) and dice and chips on a felt table (photo 4677404).
