<?php

use App\Http\Controllers\api\v1\Books\BookActionController;
use App\Http\Controllers\api\v1\Books\BookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\Books\Genre\GenreController;
use App\Http\Controllers\api\v1\Books\Author\AuthorController;


Route::prefix('books')
    ->name('books.')
    ->group(function () {

        Route::get('index', [BookController::class, 'index'])->name('index');
        Route::get('show/{id}', [BookActionController::class, 'show'])->name('show');
        Route::get('book/{id}', [BookController::class, 'show'])->name('book');

        Route::prefix('genres')
            ->name('genres.')
            ->group(function () {
               Route::get('index', [GenreController::class, 'index'])->name('genres');
               Route::get('show/{genre}', [GenreController::class, 'show'])->name('show');
            });

        Route::prefix('authors')
            ->name('authors.')
            ->group(function () {
                Route::get('index', [AuthorController::class, 'index'])->name('authors');
                Route::get('show/{author}', [AuthorController::class, 'show'])->name('show');
            });

        Route::middleware('auth')->group(function () {
            Route::post('store', [BookActionController::class, 'store'])->name('store');
        });

        Route::middleware('auth.check')->group(function () {
            Route::get('create_book/{book?}', [BookController::class, 'create'])->name('create_book');
            Route::put('update', [BookActionController::class, 'update'])->name('update');
            Route::get('forceDelete/{id}', [BookActionController::class, 'forceDelete'])->name('forceDelete');
        });
    });
