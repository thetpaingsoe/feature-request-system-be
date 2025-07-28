<?php

namespace App\Actions\SubmissionLog;

use App\Enums\SubmissionLogTypes;
use App\Enums\SubmissionStatus;
use App\Services\Submission\SubmissionLogService;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordSubmissionLogAction
{
    public function __construct(
        protected SubmissionLogService $submissionLogService
    ) {}

    public function handle($request, $submisionId, $params = [])
    {
        try {

            $validatedData = $request->validated();
            $userId = auth()->id();

            if (isset($params['type'])) { // We know the exact log type
                switch ($params['type']) {
                    case SubmissionLogTypes::Create:
                        $this->submissionLogService->handleCreate($submisionId, $userId);
                        break;
                    case SubmissionLogTypes::Update:
                        $this->submissionLogService->handleUpdate($submisionId, $userId);
                        break;

                }
            } else { // We need to figure it out
                // Checking is status change
                if ($this->isStatusChangeLog($params)) {
                    $this->submissionLogService->handleStatusChange($submisionId, $userId, $params);
                }

                // Checking is feedback
                if ($this->isFeedbackReplyLog($validatedData)) {
                    $this->submissionLogService->handleFeedback($submisionId, $userId, $validatedData);
                }

                // Checking is Approve
                if ($this->isApproveLog($params)) {
                    $this->submissionLogService->handleApprove($submisionId, $userId);
                }
                // Checking is Reject
                elseif ($this->isRejectLog($params)) {
                    $this->submissionLogService->handleReject($submisionId, $userId);
                }

                // Checking is Accept Suggestion
                if ($this->isAcceptSuggestion($validatedData)) {

                    $this->submissionLogService->handleAcceptSuggestion($submisionId, $userId, $params);
                }
                // Checking is Reject Suggestion
                elseif ($this->isRejectSuggestion($validatedData)) {

                    $this->submissionLogService->handleRejectSuggestion($submisionId, $userId, $params);
                }
            }

        } catch (Throwable $e) {
            Log::error('RecordSubmissionLogAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }

    public function isStatusChangeLog($params = [])
    {
        if (isset($params['from'])
            && ! isset($params['to'])
            && $params['from'] != $params['to']
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function isFeedbackReplyLog($params = [])
    {
        if (isset($params['note']) && $params['note'] != '') {
            return true;
        } else {
            return false;
        }
    }

    public function isApproveLog($params)
    {
        if (isset($params['to'])
            && $params['to'] == SubmissionLogTypes::Approved->value) {
            return true;
        } else {
            return false;
        }
    }

    public function isRejectLog($params)
    {
        if (isset($params['to'])
            && $params['to'] == SubmissionLogTypes::Rejected->value) {
            return true;
        } else {
            return false;
        }
    }

    public function isAcceptSuggestion($data)
    {
        if (isset($data['status'])
            && $data['status'] == SubmissionStatus::Reviewing->value
            && isset($data['action'])
            && $data['action'] == 'accept') {
            return true;
        } else {
            return false;
        }
    }

    public function isRejectSuggestion($data)
    {
        if (isset($data['status'])
            && $data['status'] == SubmissionStatus::Reviewing->value
            && isset($data['action'])
            && $data['action'] == 'reject') {
            return true;
        } else {
            return false;
        }
    }
}
