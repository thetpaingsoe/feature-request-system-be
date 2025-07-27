<?php

namespace App\Http\Controllers\Api;

use App\Actions\SubmissionLog\RecordSubmissionLogAction;
use App\Actions\SubmissionLog\SearchSubmissionLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionLog\SubmissionLogReplyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionLogApiController extends Controller
{
    public function __construct(
        protected SearchSubmissionLogAction $searchSubmissionLogAction,
        protected RecordSubmissionLogAction $recordSubmissionLogAction
    ) {}

    public function search(Request $request, $id)
    {
        try {

            return response()->json($this->searchSubmissionLogAction->handle($request, $id), 200);
        } catch (Throwable $e) {
            Log::error('SubmissionApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }
    }

    // Accept the suggestion
    // Reject the suggestion
    // Reply the suggestion
    public function reply(SubmissionLogReplyRequest $request, $id)
    {
        try {
            return response()->json($this->recordSubmissionLogAction->handle($request, $id), 201);
        } catch (Throwable $e) {
            Log::error('SubmissionApiController::reply : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.'.$e->getMessage(),
            ], 500);
        }

    }
}
