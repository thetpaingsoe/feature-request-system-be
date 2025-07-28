<?php

namespace App\Actions\Submission;

use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetSubmissionCountsAction
{
    public function __construct(
        protected SubmissionService $submission
    ) {}

    public function handle()
    {
        try {
            return $this->submission->getDashboardCounts();

        } catch (Throwable $e) {
            Log::error('GetSubmissionCountsAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
