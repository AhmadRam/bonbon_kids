<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Webkul\Daftra\Http\Controllers\DaftraController;

/*
|--------------------------------------------------------------------------
| API Routes for Daftra Integration
|--------------------------------------------------------------------------
*/

Route::prefix('daftra')->group(function () {

    // Products Routes
    Route::get('/products', [DaftraController::class, 'getProducts']);
    Route::get('/products/{id}', [DaftraController::class, 'getProduct']);
    Route::get('/products/search', [DaftraController::class, 'searchProducts']);

    // Invoices Routes
    Route::get('/invoices', [DaftraController::class, 'getInvoices']);
    Route::get('/invoices/{id}', [DaftraController::class, 'getInvoice']);
    Route::post('/invoices', [DaftraController::class, 'createInvoice']);

    // Clients Routes
    Route::get('/clients', [DaftraController::class, 'getClients']);
    Route::post('/clients', [DaftraController::class, 'createClient']);
});
