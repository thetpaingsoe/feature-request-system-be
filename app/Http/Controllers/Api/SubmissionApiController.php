<?php

namespace App\Http\Controllers\Api;

use App\Actions\Submission\GetSubmissionAction;
use App\Actions\Submission\SearchSubmissionAction;
use App\Actions\Submission\StoreSubmissionAction;
use App\Actions\Submission\UpdateSubmissionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\SubmissionStoreRequest;
use App\Http\Requests\Submission\SubmissionUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionApiController extends Controller
{
    public function __construct(
        protected SearchSubmissionAction $searchSubmissionAction,
        protected StoreSubmissionAction $storeSubmissionAction,
        protected UpdateSubmissionAction $updateSubmissionAction,
        protected GetSubmissionAction $getSubmissionAction
    ) {}

    public function search(Request $request)
    {
        try {
            return response()->json($this->searchSubmissionAction->handle($request), 200);
        } catch (Throwable $e) {
            Log::error('FeatureRequestApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }
    }

    public function get(Request $request, $id)
    {
        try {
            return response()->json($this->getSubmissionAction->handle($id), 200);
        } catch (Throwable $e) {
            Log::error('FeatureRequestApiController::get : '.$e->getMessage(), ['exception' => $e]);

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

    public function update(SubmissionUpdateRequest $request, $id)
    {
        try {            
            return response()->json($this->updateSubmissionAction->handle($id, $request), 201);
        } catch (Throwable $e) {
            Log::error('SubmissionApiController::store : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.'.$e->getMessage(),
            ], 500);
        }

    }
}
