<?php

use App\Http\Controllers\api\v1\log\LogController;
use App\Http\Controllers\api\v1\log\LogActionController;
use Illuminate\Support\Facades\Route;

Route::prefix('logs')
    ->name('logs.')
    ->middleware('auth:admin')
    ->group(function () {
        Route::get('index/{id}', [LogController::class, 'index'])->name('index');
    });




