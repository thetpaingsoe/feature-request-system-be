<?php

use App\Http\Controllers\Api\CompanyDesignationApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\FeatureRequestApiController;
use App\Http\Controllers\Api\ShareValueApiController;
use App\Http\Controllers\Api\SubmissionApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/feature-requests', [FeatureRequestApiController::class, 'index']);

Route::post('/feature-requests', [FeatureRequestApiController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/submissions', [SubmissionApiController::class, 'search']);

    Route::get('/countries', [CountryApiController::class, 'search']);

    Route::get('/desinations', [CompanyDesignationApiController::class, 'search']);

    Route::get('/sharevalues', [ShareValueApiController::class, 'search']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', function (Request $request) {

        $user = $request->user();
        // $user->tokens()->delete();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    });
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return response()->json([
        'user' => $user,
        'token' => $user->createToken('auth_token')->plainTextToken,
    ]);

});
