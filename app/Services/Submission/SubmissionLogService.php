<?php

namespace App\Services\Submission;

use App\Models\SubmissionLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubmissionLogService
{
    public function search(int $id, array $sorting, int $page, int $perPage = 10)
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
}
