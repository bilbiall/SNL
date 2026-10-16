<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamesetBoardController;
use App\Http\Controllers\GamesetCardController;
use App\Http\Controllers\HomeController;
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
    Route::post('/rematch', [RoomController::class, 'rematch'])->name('rematch');
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
    Route::delete('/boards/{board}', [GamesetBoardController::class, 'destroy'])->name('boards.destroy');

    Route::get('/cards', [GamesetCardController::class, 'index'])->name('cards.index');
    Route::post('/cards', [GamesetCardController::class, 'store'])->name('cards.store');
    Route::post('/cards/bulk', [GamesetCardController::class, 'bulk'])->name('cards.bulk');
    Route::put('/cards/{card}', [GamesetCardController::class, 'update'])->name('cards.update');
    Route::delete('/cards/{card}', [GamesetCardController::class, 'destroy'])->name('cards.destroy');
});
