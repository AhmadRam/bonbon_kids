<?php

use Illuminate\Support\Facades\Route;
use Webkul\UPayments\Http\Controllers\StandardController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('upayments/standard')->group(function () {
        Route::get('/redirect', [StandardController::class, 'redirect'])->name('upayments.standard.redirect');

        Route::get('/callback', [StandardController::class, 'callback'])->name('upayments.payment.callback');

        Route::get('/cancel', [StandardController::class, 'cancel'])->name('upayments.payment.cancel');

        Route::post('/webhook', [StandardController::class, 'webhook'])->name('upayments.payment.webhook');
    });
});
