<?php

namespace App\Actions\Submission;

use App\DTOs\Submission\StoreSubmissionDto;
use App\Enums\SubmissionLogTypes;
use App\Http\Requests\Submission\SubmissionStoreRequest;
use App\Services\Submission\SubmissionLogService;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService,
        protected SubmissionLogService $submissionLogService
    ) {}

    public function handle(SubmissionStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = StoreSubmissionDto::from($request->validated());

            $userId = auth()->id();

            $submission = $this->submissionService->create($data, $userId);

            $noteData = [
                'user_id' => $userId,
            ];

            $log = (object) [
                'submission_id' => $submission->id,
                'type' => SubmissionLogTypes::Create,
                'data' => $noteData,
            ];

            $this->submissionLogService->create($log);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('StoreSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
