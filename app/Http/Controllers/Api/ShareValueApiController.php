<?php

namespace App\Http\Controllers\Api;

use App\Actions\ShareValue\SearchShareValueAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShareValueApiController extends Controller
{
    public function __construct(
        protected SearchShareValueAction $searchShareValueAction
    ) {}

    public function search(Request $request)
    {
        try {
            return response()->json($this->searchShareValueAction->handle($request), 200);
        } catch (Throwable $e) {
            Log::error('ShareValueApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }

    }
}
