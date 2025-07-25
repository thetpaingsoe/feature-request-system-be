<?php

namespace App\Actions\Submission;

use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetSubmissionAction
{
    public function __construct(
        protected SubmissionService $submission
    ) {}

    public function handle($id)
    {
        try {

            return $this->submission->get($id);

        } catch (Throwable $e) {
            Log::error('GetSubmissionAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
