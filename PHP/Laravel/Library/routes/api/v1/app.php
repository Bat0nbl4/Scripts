<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\api\v1\AppController;
use App\Http\Controllers\api\v1\SortController;

Route::get('/', [AppController::class, 'index'])->name('index');
Route::get('ChangeSortSettings', [SortController::class, 'ChangeSortSettings'])->name('ChangeSortSettings');
