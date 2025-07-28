<?php

namespace App\Actions\Submission;

use App\Actions\SubmissionLog\RecordSubmissionLogAction;
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
        protected SubmissionLogService $submissionLogService,
        protected RecordSubmissionLogAction $recordSubmissionLogAction
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

            $data = [];
            if (isset($validatedData['status'])) {
                $data = [
                    'from' => $submission->status,
                    'to' => $validatedData['status'],
                ];
                if ($data['from'] != $data['to']) {
                    $submission = $this->submissionService->updateStatus($submission, $validatedData['status']);
                }
            }
            $this->recordSubmissionLogAction->handle($request, $id, $data);

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UpdateSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
