<?php

namespace App\Http\Controllers\Api;

use App\Actions\Submission\SearchSubmissionAction;
use App\Actions\Submission\StoreSubmissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\SubmissionStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionApiController extends Controller
{
    public function __construct(
        protected SearchSubmissionAction $searchSubmissionAction,
        protected StoreSubmissionAction $storeSubmissionAction
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

    public function store(SubmissionStoreRequest $request)
    {
        try {
            return response()->json($this->storeSubmissionAction->handle($request), 201);
        } catch (Throwable $e) {
            Log::error('SubmissionApiController::store : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.'.$e->getMessage(),
            ], 500);
        }

    }
}
