<?php

use App\Http\Controllers\FeatureRequest\FeatureRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FeatureRequestController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('feature-requests')->name('feature-requests.')->group(function () {
    Route::get('/', [FeatureRequestController::class, 'index'])->name('index');
    Route::get('/{id}/edit', [FeatureRequestController::class, 'edit'])->name('edit');
    Route::put('/{id}', [FeatureRequestController::class, 'update'])->name('update');
    Route::delete('/{id}', [FeatureRequestController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
