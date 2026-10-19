<?php

use App\Http\Controllers\AppDownloadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\GamesetBoardController;
use App\Http\Controllers\GamesetCardController;
use App\Http\Controllers\GamesetPackController;
use App\Http\Controllers\GamesetSettingsController;
use App\Http\Controllers\VoiceController;
use App\Http\Controllers\GamesetWordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfflineController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Players — no accounts. Party mode is one device; online mode is a room code + a name.
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/new', [HomeController::class, 'create'])->name('setup');
Route::get('/join', [HomeController::class, 'join'])->name('join');

// Public games, and the indexable pages that help people find the game.
Route::get('/rooms', [LobbyController::class, 'index'])->name('rooms.index');
// TV mode: a big-screen view of an online room (the phones play, the TV watches)
Route::get('/tv', [LobbyController::class, 'tvIndex'])->name('tv.index');
Route::get('/tv/go', [LobbyController::class, 'tvGo'])->name('tv.go');
Route::get('/tv/{code}', [RoomController::class, 'tv'])->name('tv.show');
Route::get('/rooms/list', [LobbyController::class, 'list'])->middleware('throttle:snl-state')->name('rooms.list');
Route::get('/how-to-play', [ContentController::class, 'howToPlay'])->name('how-to-play');
Route::get('/fun-facts', [ContentController::class, 'funFacts'])->name('fun-facts');
Route::get('/about', [ContentController::class, 'about'])->name('about');
Route::get('/privacy', [ContentController::class, 'privacy'])->name('privacy');
Route::get('/sitemap.xml', [ContentController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [ContentController::class, 'robots'])->name('robots');
Route::get('/ads.txt', [ContentController::class, 'adsTxt'])->name('ads.txt');

// The Android app: a download page and the file itself.
Route::get('/app', [AppDownloadController::class, 'page'])->name('app.page');
Route::get('/download/android', [AppDownloadController::class, 'android'])->name('app.android');
Route::get('/download/tv', [AppDownloadController::class, 'tv'])->name('app.tv');
Route::get('/download/windows', [AppDownloadController::class, 'windows'])->name('app.windows');

// Offline: the pack, the file list and the in-browser party table (must come before play/{room}).
Route::get('/offline/pack.json', [OfflineController::class, 'pack'])->name('offline.pack');
Route::get('/offline/assets.json', [OfflineController::class, 'assets'])->name('offline.assets');
Route::get('/play/local', [OfflineController::class, 'local'])->name('play.local');

Route::post('/new', [RoomController::class, 'store'])->middleware('throttle:snl-create')->name('rooms.store');
Route::post('/join', [RoomController::class, 'joinStore'])->middleware('throttle:snl-create')->name('join.store');

Route::prefix('play/{room:token}')->name('play.')->group(function () {
    Route::get('/', [RoomController::class, 'show'])->name('show');
    Route::get('/state', [RoomController::class, 'state'])->middleware('throttle:snl-state')->name('state');
    Route::post('/roll', [RoomController::class, 'roll'])->middleware('throttle:snl-play')->name('roll');
    Route::post('/decide', [RoomController::class, 'decide'])->middleware('throttle:snl-play')->name('decide');
    Route::post('/start', [RoomController::class, 'start'])->name('start');
    Route::post('/leave', [RoomController::class, 'leave'])->name('leave');
    Route::post('/react', [RoomController::class, 'react'])->middleware('throttle:snl-play')->name('react');
    Route::post('/mission', [RoomController::class, 'claimMission'])->middleware('throttle:snl-play')->name('mission');
    // voice chat (online rooms): LiveKit token, or the free phone-to-phone signalling
    Route::post('/voice/token', [VoiceController::class, 'token'])->middleware('throttle:snl-voice')->name('voice.token');
    Route::post('/voice/join', [VoiceController::class, 'join'])->middleware('throttle:snl-voice')->name('voice.join');
    Route::get('/voice/poll', [VoiceController::class, 'poll'])->middleware('throttle:snl-voice')->name('voice.poll');
    Route::post('/voice/signal', [VoiceController::class, 'signal'])->middleware('throttle:snl-voice')->name('voice.signal');
    Route::post('/voice/leave', [VoiceController::class, 'leave'])->middleware('throttle:snl-voice')->name('voice.leave');
    Route::post('/rematch', [RoomController::class, 'rematch'])->name('rematch');
    Route::post('/addplayer', [RoomController::class, 'addPlayer'])->middleware('throttle:snl-play')->name('addplayer');
});

/*
|--------------------------------------------------------------------------
| GameSet (admin)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:snl-login')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'gameset'])->prefix('gameset')->name('gameset.')->group(function () {
    Route::redirect('/', '/gameset/boards')->name('index');

    Route::get('/boards', [GamesetBoardController::class, 'index'])->name('boards.index');
    Route::post('/boards', [GamesetBoardController::class, 'store'])->name('boards.store');
    Route::post('/boards/{board}/settings', [GamesetBoardController::class, 'settings'])->name('boards.settings');
    Route::post('/boards/{board}/art', [GamesetBoardController::class, 'art'])->name('boards.art');
    Route::post('/boards/{board}/resize', [GamesetBoardController::class, 'resize'])->name('boards.resize');
    Route::post('/boards/{board}/tiles', [GamesetBoardController::class, 'tiles'])->name('boards.tiles');
    Route::post('/boards/{board}/positions', [GamesetBoardController::class, 'positions'])->name('boards.positions');
    Route::post('/boards/{board}/zone', [GamesetBoardController::class, 'zone'])->name('boards.zone');
    Route::delete('/boards/{board}', [GamesetBoardController::class, 'destroy'])->name('boards.destroy');

    Route::get('/cards', [GamesetCardController::class, 'index'])->name('cards.index');
    Route::post('/cards', [GamesetCardController::class, 'store'])->name('cards.store');
    Route::post('/cards/bulk', [GamesetCardController::class, 'bulk'])->name('cards.bulk');
    Route::put('/cards/{card}', [GamesetCardController::class, 'update'])->name('cards.update');
    Route::delete('/cards/{card}', [GamesetCardController::class, 'destroy'])->name('cards.destroy');

    Route::get('/settings', [GamesetSettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [GamesetSettingsController::class, 'save'])->name('settings.save');

    Route::get('/packs', [GamesetPackController::class, 'index'])->name('packs.index');
    Route::post('/packs', [GamesetPackController::class, 'store'])->name('packs.store');
    Route::put('/packs/{pack}', [GamesetPackController::class, 'update'])->name('packs.update');
    Route::delete('/packs/{pack}', [GamesetPackController::class, 'destroy'])->name('packs.destroy');
    Route::post('/packs/{pack}/cards', [GamesetPackController::class, 'addCards'])->name('packs.cards.store');
    Route::delete('/packs/{pack}/cards/{card}', [GamesetPackController::class, 'destroyCard'])->name('packs.cards.destroy');

    Route::get('/words', [GamesetWordController::class, 'index'])->name('words.index');
    Route::post('/words', [GamesetWordController::class, 'store'])->name('words.store');
    Route::post('/words/bulk', [GamesetWordController::class, 'bulk'])->name('words.bulk');
    Route::put('/words/{word}', [GamesetWordController::class, 'update'])->name('words.update');
    Route::delete('/words/{word}', [GamesetWordController::class, 'destroy'])->name('words.destroy');
});
