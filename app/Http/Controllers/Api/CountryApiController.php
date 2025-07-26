<?php

namespace App\Http\Controllers\Api;

use App\Actions\Country\SearchCountryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountryApiController extends Controller
{
    public function __construct(
        protected SearchCountryAction $searchCountryAction
    ) {}

    public function search(Request $request)
    {
        try {
            return response()->json($this->searchCountryAction->handle($request), 200);
        } catch (Throwable $e) {
            Log::error('CountryApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }

    }
}
