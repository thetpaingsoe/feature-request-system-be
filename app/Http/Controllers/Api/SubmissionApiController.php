<?php

namespace App\Http\Controllers\Api;

use App\Actions\Submission\SearchSubmissionAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionApiController extends Controller
{
    public function __construct(
        protected SearchSubmissionAction $searchSubmissionAction
    ) {}

    public function search(Request $request)
    {
        try {
            return response()->json($this->searchSubmissionAction->handle($request), 200);
        } catch (Throwable $e) {
            Log::error('FeatureRequestApiController::store : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }

    }
}
