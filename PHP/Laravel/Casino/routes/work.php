<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Work\WorkController;
use App\Http\Controllers\Work\WorkActionController;

Route::prefix('work')
    ->name('work.')
    ->group( function () {
        Route::middleware('admin')->group(function () {
            Route::get('index', [WorkController::class, 'index'])->name('index');
        });
    });

