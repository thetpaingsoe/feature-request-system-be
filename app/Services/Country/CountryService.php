<?php

namespace App\Services\Country;

use App\Models\Country;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountryService
{
    public function search(array $filters, array $sorting, int $page, int $perPage = 10)
    {
        try {
            $query = Country::query();

            return $query
                ->when($filters['search'] ?? null, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%')
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
            Log::error('CountryService::search : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
