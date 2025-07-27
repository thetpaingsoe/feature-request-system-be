<?php

namespace App\Actions\SubmissionLog;

use App\Enums\SubmissionLogTypes;
use App\Services\Submission\SubmissionLogService;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordSubmissionLogAction
{
    public function __construct(
        protected SubmissionLogService $submissionLogService
    ) {}

    public function handle($request, $id, $fromToData = [])
    {
        try {

            $validatedData = $request->validated();

            $userId = auth()->id();

            // Status Change
            if (isset($fromToData) && count($fromToData) > 0) {
                $data = [
                    'from' => $fromToData['from'],
                    'to' => $fromToData['to'],
                    'user_id' => $userId,
                ];

                $statusChangeLog = (object) [
                    'submission_id' => $id,
                    'type' => SubmissionLogTypes::StatusChange,
                    'data' => $data,
                ];
                if ($data['from'] != $data['to']) {
                    $this->submissionLogService->create($statusChangeLog);
                }

                // Reject
                if ($data['to'] == SubmissionLogTypes::Rejected->value) {

                    $rejectData = [
                        'user_id' => $userId,
                    ];
                    $log = (object) [
                        'submission_id' => $id,
                        'type' => SubmissionLogTypes::Rejected,
                        'data' => $rejectData,
                    ];

                    $this->submissionLogService->create($log);

                }

                // Approve
                elseif ($data['to'] == SubmissionLogTypes::Approved->value) {
                    $approveData = [
                        'user_id' => $userId,
                    ];
                    $log = (object) [
                        'submission_id' => $id,
                        'type' => SubmissionLogTypes::Approved,
                        'data' => $approveData,
                    ];

                    $this->submissionLogService->create($log);
                }
            }

            // Feedback
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

        } catch (Throwable $e) {
            Log::error('SearchSubmissionLogAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
