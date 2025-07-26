<?php

use App\Http\Controllers\FeatureRequest\FeatureRequestController;
use App\Http\Controllers\Submission\SubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('home');

    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('feature-requests')->name('feature-requests.')->group(function () {
        Route::get('/', [FeatureRequestController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [FeatureRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [FeatureRequestController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeatureRequestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/', [SubmissionController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [SubmissionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SubmissionController::class, 'updateStatus'])->name('update-status');
        // Route::delete('/{id}', [SubmissionController::class, 'destroy'])->name('destroy');
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
