<?php

namespace App\Services\ShareValue;

use App\Models\ShareValue;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShareValueService
{
    public function search(array $filters, array $sorting, int $page, int $perPage = 10)
    {
        try {
            $query = ShareValue::query();

            return $query
                ->when($filters['search'] ?? null, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('currency', 'like', '%'.$search.'%')
                            ->orWhere('id', $search);
                    });
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
            Log::error('ShareValueService::search : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
