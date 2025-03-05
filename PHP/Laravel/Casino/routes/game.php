<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\Game\GameActionController;

use App\Http\Controllers\Hack\HackController;
use App\Http\Controllers\Hack\HackActionController;

Route::prefix('game')
    ->name('game.')
    ->group( function () {
        Route::middleware('wallet')->group(function () {
            Route::get('init', [GameActionController::class, 'init'])->name('init');
            Route::get('get_RCard', [GameActionController::class, 'get_RCard'])->name('get_RCard');
            Route::get('get_RSet', [GameActionController::class, 'get_RSet'])->name('get_RSet');
            Route::post('set_bet', [GameActionController::class, 'set_bet'])->name('set_bet');
            Route::get('get_bet', [GameActionController::class, 'get_bet'])->name('get_bet');
            Route::get('get_cards', [GameActionController::class, 'get_cards'])->name('get_cards');
            Route::get('table', [GameController::class, 'table'])->name('table');
            Route::get('restart', [GameActionController::class, 'game_restart'])->name('restart');

            Route::post('bring_out', [GameActionController::class, 'bring_out'])->name('bring_out');
            Route::get('game_over', [GameActionController::class, 'game_over'])->name('game_over');
        });
    });

Route::prefix('hack')
    ->name('hack.')
    ->group( function () {
        Route::middleware('wallet')->group(function () {
            Route::get('menu', [HackController::class, 'menu'])->name('menu');
            Route::get('init/{difficulty}', [HackActionController::class, 'init'])->name('init');
            Route::post('try_password', [HackActionController::class, 'try_password'])->name('try_password');
            Route::get('hack', [HackController::class, 'hack'])->name('hack');
        });
    });
