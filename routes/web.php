<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('system-requests', function () {
    return Inertia::render('system-requests/SystemRequestList');
})->middleware(['auth', 'verified'])->name('system-requests');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
