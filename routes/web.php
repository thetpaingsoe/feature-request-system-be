<?php

use App\Http\Controllers\FeatureRequest\FeatureRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('feature-requests')->name('feature-requests.')->group(function () {
    Route::get('/', [FeatureRequestController::class, 'index'])->name('index');
    // Route::get('/{id}/edit', [FeatureRequestController::class, 'edit'])->name('edit');
    // Route::put('/{id}', [FeatureRequestController::class, 'update'])->name('update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
