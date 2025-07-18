<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/feature-requests', function (Request $request) {
            
    return "Feature Requests ( POST ) ";
});
//->middleware('auth:sanctum');
