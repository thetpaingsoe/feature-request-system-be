<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('feature-requests', function () {
    return Inertia::render('feature-requests/FeatureRequestList');
})->middleware(['auth', 'verified'])->name('feature-requests');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
