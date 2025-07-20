<?php

namespace App\Services\FeatureRequest;

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
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('id', $search); // Assuming ID can be searched directly
            })
                ->when($filters['status'] ?? null, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($filters['date_start'] ?? null, function ($query, $dateStart) {
                    $query->where('submitted_at', '>=', $dateStart);
                })
                ->when($filters['date_end'] ?? null, function ($query, $dateEnd) {
                    // Add 23:59:59 to include the whole end day
                    $query->where('submitted_at', '<=', $dateEnd.' 23:59:59');
                })
                ->when($sorting['sort_by'] ?? null, function ($query, $sortBy) {
                    $sortDirection = $sorting['sort_direction'] ?? 'asc'; // Default to asc
                    // Ensure the column exists and is safe to sort by
                    $allowedSortColumns = ['id', 'title', 'email', 'status', 'submitted_at'];
                    if (in_array($sortBy, $allowedSortColumns)) {
                        $query->orderBy($sortBy, $sortDirection);
                    }
                })
                // Default sorting if no sort_by is provided
                ->orderBy('id', 'desc') // Or any default sorting you prefer
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

    public function updateStatus() {}

    public function updateNote() {}

    /* ?? */
    public function delete() {}
}
