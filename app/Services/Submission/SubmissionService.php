<?php

namespace App\Services\Submission;

use App\Enums\SubmissionStatus;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionService
{
    public function search(array $filters, array $sorting, int $page, int $perPage = 10)
    {
        try {
            $query = Submission::query();

            return $query
                ->with(['companyDesignation', 'jurisdictionOfOperation', 'shareValue', 'user']) // eager load relations
                ->when($filters['search'] ?? null, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('company_name', 'like', '%'.$search.'%')
                            ->orWhere('alternative_company_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('id', $search);
                    });
                })
                ->when($filters['status'] ?? null, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($filters['user_id'] ?? null, function ($query, $user_id) {
                    $query->where('user_id', $user_id);
                })
                ->when($filters['date_start'] ?? null, function ($query, $dateStart) {
                    $query->whereDate('created_at', '>=', $dateStart);
                })
                ->when($filters['date_end'] ?? null, function ($query, $dateEnd) {
                    $query->whereDate('created_at', '<=', $dateEnd);
                })
                ->when(isset($sorting['sort_by'], $sorting['sort_direction']), function ($query) use ($sorting) {
                    if (in_array($sorting['sort_by'], ['id', 'company_name', 'email', 'status', 'created_at'])) {
                        $query->orderBy($sorting['sort_by'], $sorting['sort_direction']);
                    }
                }, function ($query) {
                    // Default sort if not specified
                    $query->orderBy('id', 'desc');
                })
                ->paginate($perPage, ['*'], 'page', $page)
                ->withQueryString();

        } catch (Throwable $e) {
            Log::error('SubmissionService::search : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function get($id)
    {
        try {
            return Submission::with(['companyDesignation', 'jurisdictionOfOperation', 'shareValue', 'user'])
                ->findOrFail($id);
        } catch (Throwable $e) {
            Log::error("SubmissionService::get($id) : ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function create($data, $userId)
    {
        try {
            $submission = Submission::create([
                'user_id' => $userId,
                'full_name' => $data->full_name,
                'email' => $data->email,
                'company_name' => $data->company_name,
                'alternative_company_name' => $data->alternative_company_name,
                'company_designation_id' => $data->company_designation_id,
                'jurisdiction_of_operation_id' => $data->jurisdiction_of_operation_id,
                'target_jurisdictions' => $data->target_jurisdictions,
                'number_of_shares' => $data->number_of_shares,
                'are_all_shares_issued' => $data->are_all_shares_issued,
                'number_of_issued_shares' => $data->number_of_issued_shares,
                'share_value_id' => $data->share_value_id,
                'shareholders' => $data->shareholders,
                'beneficial_owners' => $data->beneficial_owners,
                'directors' => $data->directors,
            ]);

            return $submission;

        } catch (Throwable $e) {
            Log::error('SubmissionService::create : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $data;
    }

    public function update(int $submissionId, $data, int $userId): Submission
    {
        try {
            // Find the submission by ID. Use findOrFail to automatically throw a 404 if not found.
            $submission = Submission::findOrFail($submissionId);

            // Ensure the user trying to update the submission actually owns it
            // or has the necessary permissions.
            if ($submission->user_id !== $userId) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You are not authorized to update this submission.');
            }

            // Update the submission attributes
            $submission->update([
                'full_name' => $data->full_name,
                'email' => $data->email,
                'company_name' => $data->company_name,
                'alternative_company_name' => $data->alternative_company_name,
                'company_designation_id' => $data->company_designation_id,
                'jurisdiction_of_operation_id' => $data->jurisdiction_of_operation_id,
                'target_jurisdictions' => $data->target_jurisdictions,
                'number_of_shares' => $data->number_of_shares,
                'are_all_shares_issued' => $data->are_all_shares_issued,
                'number_of_issued_shares' => $data->number_of_issued_shares,
                'share_value_id' => $data->share_value_id,
                'shareholders' => $data->shareholders,
                'beneficial_owners' => $data->beneficial_owners,
                'directors' => $data->directors,
            ]);

            return $submission;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('SubmissionService::update : Submission not found for ID '.$submissionId, ['exception' => $e]);
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('SubmissionService::update : Unauthorized attempt to update submission ID '.$submissionId.' by user '.$userId, ['exception' => $e]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('SubmissionService::update : '.$e->getMessage(), ['exception' => $e, 'submission_id' => $submissionId, 'user_id' => $userId]);
            throw $e;
        }
    }

    public function updateStatus(Submission $submission, string $newStatus): Submission
    {
        try {
            // Optional: Validate the status against your enum
            if (! in_array($newStatus, SubmissionStatus::toArray())) {
                throw new \InvalidArgumentException("Invalid status provided: {$newStatus}");
            }

            $submission->update([
                'status' => $newStatus,
            ]);

            return $submission;

        } catch (Throwable $e) {
            Log::error('SubmissionService::updateStatus: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function updateNote(Submission $submission, ?string $newNote): Submission
    {
        try {
            $submission->update([
                'note' => $newNote,
            ]);

            return $submission;

        } catch (Throwable $e) {
            Log::error('SubmissionService::updateNote: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function delete(Submission $submission): bool
    {
        try {
            return $submission->delete();
        } catch (Throwable $e) {
            Log::error('SubmissionService::delete: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
