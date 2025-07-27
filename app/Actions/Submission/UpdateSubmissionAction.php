<?php

namespace App\Actions\Submission;

use App\DTOs\Submission\UpdateSubmissionDto;
use App\Enums\SubmissionLogTypes;
use App\Services\Submission\SubmissionLogService;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService,
        protected SubmissionLogService $submissionLogService
    ) {}

    public function handle($id, $request)
    {

        try {

            DB::beginTransaction();

            $data = UpdateSubmissionDto::from($request->validated());

            $userId = auth()->id();

            $submission = $this->submissionService->update($id, $data, $userId);

            $noteData = [
                'user_id' => $userId,
            ];

            $log = (object) [
                'submission_id' => $submission->id,
                'type' => SubmissionLogTypes::Update,
                'data' => $noteData,
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
