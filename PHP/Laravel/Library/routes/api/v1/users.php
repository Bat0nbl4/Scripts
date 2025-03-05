<?php

use App\Http\Controllers\api\v1\Users\UserActionController;
use App\Http\Controllers\api\v1\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->name('users.')
    ->group(function () {

        Route::get('index/{id}', [UserController::class, 'index'])->name('index');

        Route::middleware('guest')->group(function (){
            Route::get('create_user', [UserController::class, 'create'])->name('create_user');
            Route::post('store', [UserActionController::class, 'store'])->name('store');
            Route::get('login', [UserController::class, 'login'])->name('login');
            Route::post('auth', [UserActionController::class, 'auth'])->name('auth');
        });

        Route::middleware('auth')->group(function (){
            Route::get('lk/{comment?}', [UserController::class, 'lk'])->name('lk');
            Route::get('logout', [UserActionController::class, 'logout'])->name('logout');
            Route::put('update', [UserActionController::class, 'update'])->name('update');
            Route::get('edit_password', [UserActionController::class, 'edit_password'])->name('edit_password');
            Route::put('update_password', [UserActionController::class, 'update_password'])->name('update_password');
        });
    });




