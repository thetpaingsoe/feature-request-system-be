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

            if ($data['from'] != $data['to']) {
                $this->submissionLogService->create($log);
            }

            if (isset($validatedData['note'])) {
                $noteData = [
                    'message' => $validatedData['note'],
                    'user_id' => $userId,
                ];
                $log = (object) [
                    'submission_id' => $id,
                    'type' => SubmissionLogTypes::SuggestionMessage,
                    'data' => $noteData,
                ];
                $this->submissionLogService->create($log);
            }
            // dd($data['to'], SubmissionLogTypes::Approved->value, $data['to'] === SubmissionLogTypes::Approved->value);
            if($data['to'] == SubmissionLogTypes::Rejected->value) {

                $rejectData = [
                    'user_id' => $userId,
                ];
                $log = (object) [
                    'submission_id' => $id,
                    'type' => SubmissionLogTypes::Rejected,
                    'data' => $rejectData,
                ];
                // dd($log);
                $this->submissionLogService->create($log);

            }else if($data['to'] == SubmissionLogTypes::Approved->value) {
                $approveData = [
                    'user_id' => $userId,
                ];
                $log = (object) [
                    'submission_id' => $id,
                    'type' => SubmissionLogTypes::Approved,
                    'data' => $approveData,
                ];
                // dd($log);
                $this->submissionLogService->create($log);
            }

            

            DB::commit();

            return $submission;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('UpdateSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
