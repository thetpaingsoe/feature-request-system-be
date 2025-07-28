<?php

namespace App\Actions\SubmissionLog;

use App\Services\Submission\SubmissionLogService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchSubmissionLogAction
{
    public function __construct(
        protected SubmissionLogService $submissionLog
    ) {}

    public function handle($request, $id = null, $perPage = 50)
    {
        try {

            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', $perPage);

            $submissionLogs = $this->submissionLog->search($id, $sorting, $page, $perPage);

            $rtnData = [
                'submissionLogsPagination' => $submissionLogs,
                'filters' => [],
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('SearchSubmissionLogAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
