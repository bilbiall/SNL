<?php

namespace App\Support;

use App\Models\Board;
use App\Models\Card;
use App\Models\Pack;
use App\Models\WordList;

/**
 * The offline "game pack": everything the in-browser engine needs to run a game with no server —
 * the rules config, every playable board with its squares, the card decks, the word lists and the avatar roster.
 * Served at /offline/pack.json and cached by the service worker; also baked into the Android app.
 */
class OfflinePack
{
    public static function build(): array
    {
        $boards = Board::playable()->map(function (Board $b) {
            return [
                'id' => $b->id, 'slug' => $b->slug, 'name' => $b->name, 'tile_count' => (int) $b->tile_count,
                'width' => (int) ($b->board_width ?: 1264), 'height' => (int) ($b->board_height ?: 848),
                'image' => $b->board_image ?: config('snl.default_board_art'), 'start_zone' => $b->start_zone,
                'tiles' => $b->tiles->map(fn ($t) => [
                    'number' => $t->number, 'kind' => $t->kind, 'icon' => $t->icon, 'label' => $t->label, 'target_number' => $t->target_number,
                    'pos_left' => $t->pos_left, 'pos_top' => $t->pos_top, 'pos_left_mobile' => $t->pos_left_mobile, 'pos_top_mobile' => $t->pos_top_mobile,
                ])->values()->all(),
            ];
        })->values()->all();

        $cfg = config('snl');
        $pack = [
            'config' => [
                'topics' => $cfg['topics'], 'topic_kinds' => $cfg['topic_kinds'],
                'modes' => $cfg['modes'], 'intensities' => $cfg['intensities'], 'players' => $cfg['players'], 'colors' => $cfg['colors'],
                'powers' => $cfg['powers'], 'tile_kinds' => $cfg['tile_kinds'], 'mode_icons' => $cfg['mode_icons'], 'spin_table' => $cfg['spin_table'],
                'random_pick_chance' => $cfg['random_pick_chance'], 'intensity_tuning' => $cfg['intensity_tuning'], 'spice_intros' => $cfg['spice_intros'], 'chill_passes' => $cfg['chill_passes'], 'free_event_chance' => $cfg['free_event_chance'], 'free_events' => $cfg['free_events'], 'event_icons' => $cfg['event_icons'], 'penalties' => $cfg['penalties'], 'timing' => $cfg['timing'], 'reactions' => $cfg['reactions'],
            ],
            'boards' => $boards,
            'cards' => self::cards(),
            'words' => WordList::where('is_active', true)->get(['slot', 'mode', 'intensity', 'text'])
                ->map(fn ($w) => ['slot' => $w->slot, 'mode' => $w->mode, 'intensity' => (int) $w->intensity, 'text' => $w->text])->values()->all(),
            'avatars' => Avatars::roster(),
            'icon_keys' => Icons::keys(),
        ];
        $pack['version'] = substr(md5(json_encode($pack)), 0, 12);

        return $pack;
    }

    /**
     * Every active card. Cards of a sponsored/themed pack are included only while the pack is live (switched on, inside its dates),
     * limited to the pack's modes, and carry the sponsor's name. A game that is already saved on a phone keeps the pack it started with.
     */
    private static function cards(): array
    {
        $live = Pack::live()->get()->keyBy('id');
        $out = [];
        foreach (Card::where('is_active', true)->get(['board_id', 'pack_id', 'topic', 'kind', 'mode', 'intensity', 'text', 'answer', 'explain', 'penalty', 'timer']) as $c) {
            $row = [
                'board_id' => $c->board_id, 'kind' => $c->kind, 'mode' => $c->mode, 'intensity' => (int) $c->intensity, 'text' => $c->text,
                'answer' => $c->answer, 'explain' => $c->explain, 'penalty' => (int) $c->penalty, 'timer' => $c->timer,
            ];
            if ($c->topic) {
                $row['topic'] = $c->topic; // a topic card (football, movies …): only drawn when the table picked that topic or Random
            }
            if (!$c->pack_id) {
                $out[] = $row;
                continue;
            }
            $pack = $live[$c->pack_id] ?? null;
            if (!$pack) {
                continue;
            }
            if ($pack->sponsor) {
                $row['sponsor'] = $pack->sponsor;
            }
            if (empty($pack->modes)) {
                $out[] = $row;
                continue;
            }
            $modes = $c->mode === 'all' ? $pack->modes : (in_array($c->mode, $pack->modes, true) ? [$c->mode] : []);
            foreach ($modes as $mode) {
                $out[] = array_merge($row, ['mode' => $mode]);
            }
        }

        return $out;
    }
}
