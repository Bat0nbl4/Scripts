<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserActionController;

Route::prefix('user')
    ->name('user.')
    ->group( function () {
        Route::middleware('guest')->group(function (){
            Route::middleware('api')->group(function () {
                Route::post('store', [UserActionController::class, 'store'])->name('store');
                Route::post('auth', [UserActionController::class, 'auth'])->name('auth');
                Route::post('logout', [UserActionController::class, 'logout'])->name('logout');
            });
            Route::middleware('web')->group(function () {
                Route::get('create', [UserController::class, 'create'])->name('create');
                Route::get('login', [UserController::class, 'login'])->name('login');
                Route::get('lk', [UserController::class, 'lk'])->name('lk');
            });
        });

        Route::middleware('auth')->group(function (){
            Route::middleware('api')->group(function () {
                Route::get('logout', [UserActionController::class, 'logout'])->name('logout');
            });
            Route::middleware('web')->group(function () {
                Route::get('lk', [UserController::class, 'lk'])->name('lk');
                Route::get('get_session', [UserActionController::class, 'get_session'])->name('get_session');
            });
        });
    });
