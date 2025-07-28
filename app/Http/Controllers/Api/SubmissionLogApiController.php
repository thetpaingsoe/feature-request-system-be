<?php

namespace App\Http\Controllers\Api;

use App\Actions\SubmissionLog\RecordSubmissionLogAction;
use App\Actions\SubmissionLog\SearchSubmissionLogAction;
use App\Http\Controllers\Controller;
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
            Log::error('SubmissionLogApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }
    }
}
