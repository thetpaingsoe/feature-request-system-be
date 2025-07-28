<?php

namespace App\Services\Submission;

use App\Enums\SubmissionLogTypes;
use App\Models\SubmissionLog;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionLogService
{
    public function search(?int $id, array $sorting, int $page, int $perPage = 10)
    {
        try {
            $query = SubmissionLog::query();

            return $query
                ->when(isset($id), function ($query) use ($id) {
                    $query->where('submission_id', '=', $id);
                })
                ->when(isset($sorting['sort_by'], $sorting['sort_direction']), function ($query) use ($sorting) {
                    if (in_array($sorting['sort_by'], ['id', 'created_at'])) {
                        $query->orderBy($sorting['sort_by'], $sorting['sort_direction']);
                    }
                }, function ($query) {
                    // Default sort if not specified
                    $query->orderBy('id', 'desc');
                })
                ->paginate($perPage, ['*'], 'page', $page)
                ->withQueryString();

        } catch (Throwable $e) {
            Log::error('SubmissionLogService::search : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function get($id)
    {
        try {
            return SubmissionLog::findOrFail($id);
        } catch (Throwable $e) {
            Log::error("SubmissionLogService::get($id) : ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function create($data)
    {
        try {
            $submissionLog = SubmissionLog::create([
                'submission_id' => $data->submission_id,
                'type' => $data->type,
                'data' => $data->data,
            ]);

            return $submissionLog;

        } catch (Throwable $e) {
            Log::error('SubmissionLogService::create : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $data;
    }

    public function delete(SubmissionLog $submissionLog): bool
    {
        try {
            return $submissionLog->delete();
        } catch (Throwable $e) {
            Log::error('SubmissionLogService::delete: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    // Create
    public function handleCreate($submissionId, $userId)
    {
        $data = [
            'user_id' => $userId,
        ];

        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::Create,
            'data' => $data,
        ];

        $this->create($log);
    }

    // Update
    public function handleUpdate($submissionId, $userId)
    {
        $data = [
            'user_id' => $userId,
        ];

        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::Update,
            'data' => $data,
        ];

        $this->create($log);
    }

    // Status Change
    public function handleStatusChange($submisionId, $userId, $params = [])
    {

        if (! isset($params['from']) || ! isset($params['to'])) {
            throw new Exception("Can't log status change because of missing from and to status.");
        }

        $statusChangeData = [
            'from' => $params['from'],
            'to' => $params['to'],
            'user_id' => $userId,
        ];

        $statusChangeLog = (object) [
            'submission_id' => $submisionId,
            'type' => SubmissionLogTypes::StatusChange,
            'data' => $statusChangeData,
        ];

        $this->create($statusChangeLog);

    }

    // Feedback Message
    public function handleFeedback($submissionId, $userId, $data = [])
    {

        if (! isset($data['note'])) {
            throw new Exception("Feedback note can't be blank.");
        }

        $noteData = [
            'message' => $data['note'],
            'user_id' => $userId,
        ];
        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::SuggestionMessage,
            'data' => $noteData,
        ];
        $this->create($log);
    }

    // Accept Suggestion
    public function handleAcceptSuggestion($submissionId, $userId, $params = [])
    {
        // status change
        $this->handleStatusChange($submissionId, $userId, $params);

        // record reject suggession
        $approveData = [
            'user_id' => $userId,
        ];
        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::SuggestionAccepted,
            'data' => $approveData,
        ];

        $this->create($log);
    }

    // Reject Suggestion
    public function handleRejectSuggestion($submissionId, $userId, $params = [])
    {

        // status change
        $this->handleStatusChange($submissionId, $userId, $params);

        // record reject suggession
        $approveData = [
            'user_id' => $userId,
        ];
        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::SuggestionRejected,
            'data' => $approveData,
        ];

        $this->create($log);
    }

    // Approve
    public function handleApprove($submissionId, $userId)
    {
        $approveData = [
            'user_id' => $userId,
        ];
        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::Approved,
            'data' => $approveData,
        ];

        $this->create($log);
    }

    // Reject
    public function handleReject($submissionId, $userId)
    {
        $rejectData = [
            'user_id' => $userId,
        ];
        $log = (object) [
            'submission_id' => $submissionId,
            'type' => SubmissionLogTypes::Rejected,
            'data' => $rejectData,
        ];

        $this->create($log);
    }
}
