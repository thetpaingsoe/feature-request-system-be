<?php

use App\Http\Controllers\Api\FeatureRequestApiController;
use Illuminate\Support\Facades\Route;

Route::get('/feature-requests', [FeatureRequestApiController::class, 'index']);

Route::post('/feature-requests', [FeatureRequestApiController::class, 'store']);
