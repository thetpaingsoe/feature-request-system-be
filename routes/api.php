<?php

use App\Http\Controllers\Api\CompanyDesignationApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\ShareValueApiController;
use App\Http\Controllers\Api\SubmissionApiController;
use App\Http\Controllers\Api\SubmissionLogApiController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('submissions')->controller(SubmissionApiController::class)->group(function () {
        Route::get('/', 'search');
        Route::post('/', 'store');
        Route::get('/{id}', 'get');
        Route::put('/{id}', 'update');
        Route::post('/reply/{submission_id}', 'reply');
    });

    Route::prefix('submission-logs')->controller(SubmissionLogApiController::class)->group(function () {
        Route::get('/{submission_id}', 'search');
    });

    Route::get('/countries', [CountryApiController::class, 'search']);

    Route::get('/desinations', [CompanyDesignationApiController::class, 'search']);

    Route::get('/sharevalues', [ShareValueApiController::class, 'search']);

    Route::get('/user', [UserController::class, 'getAuthUser']);

    Route::post('/logout', [UserController::class, 'logout']);

});
