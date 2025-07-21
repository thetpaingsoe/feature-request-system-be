<?php

namespace App\Services\FeatureRequest;

use App\Enums\FeatureRequestStatus;
use App\Models\FeatureRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class FeatureRequestService
{
    public function search(array $filters, array $sorting, int $page, int $perPage = 10)
    {
        try {
            $query = FeatureRequest::query();

            return $query->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('id', $search);
                });
            })
                ->when($filters['status'] ?? null, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($filters['date_start'] ?? null, function ($query, $dateStart) {
                    $query->where('submitted_at', '>=', $dateStart);
                })
                ->when($filters['date_end'] ?? null, function ($query, $dateEnd) {
                    // Add 23:59:59 to include the whole end day
                    $query->where('submitted_at', '<=', $dateEnd); // .' 23:59:59');
                })
                ->when(isset($sorting['sort_by'], $sorting['sort_direction']), function ($query) use ($sorting) {
                    if (in_array($sorting['sort_by'], ['id', 'title', 'email', 'status', 'submitted_at'])) {
                        $query->orderBy($sorting['sort_by'], $sorting['sort_direction']);
                    } else {
                        $query->orderBy('id', 'desc');
                    }
                })
                ->paginate($perPage, ['*'], 'page', $page)
                ->withQueryString();

        } catch (Throwable $e) {
            Log::error('FeatureRequestService::search : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function get($id)
    {
        try {
            return FeatureRequest::findOrFail($id);
        } catch (Throwable $e) {
            Log::error("FeatureRequestService::get($id) : ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function create($data)
    {
        try {
            $featureRequest = FeatureRequest::create([
                'title' => $data->title,
                'description' => $data->description,
                'email' => $data->email,
            ]);

            $featureRequest->refresh(); // to include full object

            return $featureRequest;

        } catch (Throwable $e) {
            Log::error('FeatureRequestService::create : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        return $data;
    }

    public function updateStatus(FeatureRequest $featureRequest, string $newStatus): FeatureRequest
    {
        try {
            // Optional: Validate the status against your enum
            if (! in_array($newStatus, FeatureRequestStatus::toArray())) {
                throw new \InvalidArgumentException("Invalid status provided: {$newStatus}");
            }

            $featureRequest->update([
                'status' => $newStatus,
            ]);

            return $featureRequest;

        } catch (Throwable $e) {
            Log::error('FeatureRequestService::updateStatus: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function updateNote(FeatureRequest $featureRequest, ?string $newNote): FeatureRequest
    {
        try {
            $featureRequest->update([
                'note' => $newNote,
            ]);

            return $featureRequest;

        } catch (Throwable $e) {
            Log::error('FeatureRequestService::updateNote: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function delete(FeatureRequest $featureRequest): bool
    {
        try {
            return $featureRequest->delete();
        } catch (Throwable $e) {
            Log::error('FeatureRequestService::delete: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
