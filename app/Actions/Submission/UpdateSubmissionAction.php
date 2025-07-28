<?php

namespace App\Actions\Submission;

use App\Actions\SubmissionLog\RecordSubmissionLogAction;
use App\DTOs\Submission\UpdateSubmissionDto;
use App\Enums\SubmissionLogTypes;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService,
        protected RecordSubmissionLogAction $recordSubmissionLogAction
    ) {}

    public function handle($id, $request)
    {

        try {

            DB::beginTransaction();

            $data = UpdateSubmissionDto::from($request->validated());

            $userId = auth()->id();

            $submission = $this->submissionService->update($id, $data, $userId);

            $params = [
                'type' => SubmissionLogTypes::Update,
            ];
            $this->recordSubmissionLogAction->handle($request, $submission->id, $params);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UpdateSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
