<?php

use App\Http\Controllers\Api\MarketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/servers', [MarketController::class, 'servers']);
    Route::get('/items/search', [MarketController::class, 'searchItems']);
    Route::get('/prices/{server}/{itemIds}', [MarketController::class, 'prices']);
    Route::post('/analyze', [MarketController::class, 'analyze']);
    Route::get('/opportunities/{server}', [MarketController::class, 'opportunities']);
});
