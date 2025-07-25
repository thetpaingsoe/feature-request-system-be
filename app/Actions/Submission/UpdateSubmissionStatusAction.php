<?php

namespace App\Actions\Submission;

use App\Enums\SubmissionLogTypes;
use App\Services\Submission\SubmissionLogService;
use App\Services\Submission\SubmissionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateSubmissionStatusAction
{
    public function __construct(
        protected SubmissionService $submissionService,
        protected SubmissionLogService $submissionLogService
    ) {}

    public function handle($id, $request)
    {

        DB::beginTransaction();

        try {

            $validatedData = $request->validated();
            $userId = auth()->id();
            if (! isset($userId)) {
                throw new Exception("Can't get login user id. ", 404);
            }

            $submission = $this->submissionService->get($id);

            $data = [
                'from' => $submission->status,
                'to' => $validatedData['status'],
                'user_id' => $userId,
            ];

            $submission = $this->submissionService->updateStatus($submission, $validatedData['status']);

            $log = (object) [
                'submission_id' => $id,
                'type' => SubmissionLogTypes::StatusChange,
                'data' => $data,
            ];

            $this->submissionLogService->create($log);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UpdateSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
