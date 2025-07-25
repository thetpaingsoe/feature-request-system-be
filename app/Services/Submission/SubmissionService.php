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

    public function create($data)
    {
        try {
            $submission = Submission::create([
                'title' => $data->title,
                'description' => $data->description,
                'email' => $data->email,
            ]);

            $submission->refresh(); // to include full object

            return $submission;

        } catch (Throwable $e) {
            Log::error('SubmissionService::create : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $data;
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
