<?php

namespace App\Actions\Submission;

use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function handle($id, $request)
    {

        DB::beginTransaction();

        try {

            $submission = $this->submissionService->get($id);

            $submission = $this->submissionService->delete($submission);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('DeleteSubmissionAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
