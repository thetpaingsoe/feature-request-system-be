<?php

namespace App\Actions\Submission;

use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function handle($id, $request)
    {

        DB::beginTransaction();

        try {

            $data = $request->validated();

            $submission = $this->submissionService->get($id);

            // @tod it should not update status here
            // $submission = $this->submissionService->updateStatus($submission, $data['status']);

            // no longer needed.
            $submission = $this->submissionService->updateNote($submission, $data['note']);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UpdateSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
