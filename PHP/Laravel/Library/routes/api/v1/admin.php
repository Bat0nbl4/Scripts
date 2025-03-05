<?php

use App\Http\Controllers\api\v1\admin\AdminController;
use App\Http\Controllers\api\v1\admin\AdminActionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\Users\UserActionController;
use App\Http\Controllers\api\v1\Books\BookActionController;
use App\Http\Controllers\api\v1\Books\BookController;
use App\Http\Controllers\api\v1\log\LogActionController;

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::middleware('guest:admin')->group(function (){
            Route::get('login', [AdminController::class, 'login'])->name('login');
            Route::post('auth', [AdminActionController::class, 'auth'])->name('auth');
        });

        Route::middleware('auth:admin')->group(function (){

            Route::get('users_panel', [AdminController::class, 'show_users_panel'])->name('users_panel');
            Route::get('books_panel', [AdminController::class, 'show_books_panel'])->name('books_panel');
            Route::get('logs_panel', [AdminController::class, 'show_logs_panel'])->name('logs_panel');

            Route::get('logout', [AdminActionController::class, 'logout'])->name('logout');

            Route::prefix('user')
                ->name('user.')
                ->group(function (){
                    Route::get('forceDelete/{id}', [UserActionController::class, 'forceDelete'])->name('forceDelete');
            });
            Route::prefix('book')
                ->name('book.')
                ->group(function (){
                    Route::get('forceDelete/{id}', [BookActionController::class, 'forceDelete'])->name('forceDelete');
                    Route::get('create_book/{book}', [BookController::class, 'create'])->name('edit');
                    Route::put('update', [BookActionController::class, 'update'])->name('update');
            });
        });
    });
