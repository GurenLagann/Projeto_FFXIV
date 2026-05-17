<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\LodestoneController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketController::class, 'index'])->name('market.dashboard');
Route::post('/analyze', [MarketController::class, 'analyze'])->name('market.analyze');
Route::get('/history', [MarketController::class, 'history'])->name('market.history');
Route::get('/items', fn() => view('items.index'))->name('items.index');
Route::get('/analysis/{analysis}', [MarketController::class, 'showAnalysis'])->name('market.analysis.show');
Route::get('/analysis/{analysis}/export', [MarketController::class, 'export'])->name('market.analysis.export');

Route::middleware('auth')->group(function () {
    Route::resource('alerts', AlertController::class)->except('show');
    Route::patch('alerts/{alert}/toggle', [AlertController::class, 'toggle'])->name('alerts.toggle');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/character', [LodestoneController::class, 'show'])->name('lodestone.show');
    Route::post('/character/search', [LodestoneController::class, 'search'])->name('lodestone.search');
    Route::post('/character/by-id', [LodestoneController::class, 'byId'])->name('lodestone.byid');
    Route::post('/character/confirm', [LodestoneController::class, 'confirm'])->name('lodestone.confirm');
    Route::post('/character/verify', [LodestoneController::class, 'verify'])->name('lodestone.verify');
    Route::delete('/character', [LodestoneController::class, 'unlink'])->name('lodestone.unlink');
});

require __DIR__.'/auth.php';
