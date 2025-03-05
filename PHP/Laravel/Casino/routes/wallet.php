<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Wallet\WalletActionController;

Route::prefix('wallet')
    ->name('wallet.')
    ->group( function () {
        Route::middleware('auth')->group(function (){
            Route::middleware('web')->group(function (){
                Route::get('create', [WalletController::class, 'create'])->name('create');
                Route::get('pay_page', [WalletController::class, 'pay_page'])->name('pay_page');
                Route::get('edit/{id}', [WalletController::class, 'edit'])->name('edit');
            });
            Route::middleware('api')->group(function (){
                Route::post('store', [WalletActionController::class, 'store'])->name('store');
                Route::post('pay', [WalletActionController::class, 'pay'])->name('pay');
                Route::post('editAction', [WalletActionController::class, 'edit'])->name('editAction');
                Route::get('set_active/{id}', [WalletActionController::class, 'set_active'])->name('set_active');
                Route::get('forceDelete/{id}', [WalletActionController::class, 'forceDelete'])->name('forceDelete');

                Route::post('add_owner', [WalletActionController::class, 'add_owner'])->name('add_owner');
                Route::get('remove_owner/{id}', [WalletActionController::class, 'remove_owner'])->name('remove_owner');
                Route::post('change_owner_status', [WalletActionController::class, 'change_owner_status'])->name('change_owner_status');

                Route::get('lock/{id}', [WalletActionController::class, 'LockWallet'])->name('lock');
                Route::get('unlock/{id}', [WalletActionController::class, 'UnlockWallet'])->name('unlock');
                Route::post('withdraw', [WalletActionController::class, 'Withdraw'])->name('withdraw');
            });
        });
    });
