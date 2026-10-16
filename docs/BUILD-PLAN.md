# Snakes & Liquor — Build Plan

A party board game (snakes & ladders with dares) forked from the Pesa Mine arcade engine.
Same bones: board image → numbered tiles mapped by % position → dice → hop animation → turn-based rooms.
Different soul: **no money, no wallet, no XP**. Landing on a tile draws a **card** that everyone sees.

## Decisions (locked)

| Topic | Decision |
|---|---|
| Money | None. The pot / stake / wager / cash-out / bank / XP / plan-gates are gone. |
| Winning | First player to reach the finish tile. A recap screen gives everyone awards. |
| Content modes | `drinks` (18+), `family`, and `spicy` (reserved, disabled until built). Same board + logic; only the **card deck** and wording change. |
| Intensity | Chill / Normal / Wild per game; cards are tagged and filtered. |
| Play modes | **Party** (one device, 2–12 named players, no accounts) and **Online** (room code, each on their own phone, name only, no account). |
| Picking a target | The roller chooses ("choose a friend to take a shot"). |
| No verification | No dare voting, no truths. Cards are actions, facts, rules and pick-a-friend effects. |
| Board mapping | **Unchanged.** Admin uploads a board image, drags every numbered square onto it (desktop + mobile-landscape layouts). Positions are stored as % of the image. |
| Hosting | Own repo (`bilbiall/SNL`), own DB, own domain on cPanel. |

## Step by step

### Phase 0 — Fork (done)
1. Copy the Pesa Mine working tree into `C:\xampp\htdocs\snakesandliquor` (no `.git`, `vendor`, `node_modules`, `.env`).
2. `git init`, first commit (README only), push to `origin main`.
3. Delete dead weight (zips, old versions, planning docs, uploads).

### Phase 1 — Prune
1. Delete every controller, model, service, command, mail, seeder, migration, view, config and public asset that is not part of the party game.
2. Keep: framework skeleton, board-mapping tool, die/board/token CSS, the sound engine, help-tip component.
3. `composer install` + `npm install`; remove unused packages (socialite, web-push).

### Phase 2 — New data model (no money)
`users` (admin only) · `boards` · `tiles` · `cards` · `rooms` · `players`
(plus Laravel's `sessions`, `cache`, `jobs`).

### Phase 3 — Engine (`App\Services\GameEngine`)
Roll → optional Reroll prompt → move (overshoot bounces back, snake/ladder one hop) → resolve tile kind → optional pick-a-friend / Shield prompt → advance turn → win check.
Tile kinds: `blank`, `self`, `pick`, `everyone`, `duel`, `swap`, `fact`, `rule`, `spin`, `power`, `snake_head`, `ladder_bottom`.
Powers: **Shield** (block a hit aimed at you) and **Reroll** (re-throw your die). Cap 3 each; `power` tiles hand them out.
All table events are written to an action log (`rooms.actions`) so every device replays the same animation in order.

### Phase 4 — Party mode + online rooms
Setup → create room → play page (`/play/{token}`) → state polling → recap → rematch.
Online join by 6-letter code + name (+ 18+ tick for drinks rooms). "I'm out" button for everyone.

### Phase 5 — GameSet admin
Login → Boards (upload art, resize, drag-to-map, tile kinds/labels/icons/targets, active toggle) → Cards (kind × mode × intensity CRUD) → Settings.

### Phase 6 — Brand + compliance
Name/logo, 18+ gate for Drinks mode, "drink responsibly / never drive" notice, drink-free **Family** mode one tap away, own analytics IDs, fresh `.env`.

### Phase 7 — Deploy
New cPanel domain + DB, `git pull` routine, `php artisan migrate --seed`, `npm run build`, storage + uploads permissions.

## Later (brainstorm backlog)
Explicit "lovers" mode · Reflect / Double-up powers · hybrid TV "table" screen + phone controllers · night recap share image · themed boards · card packs per occasion.
