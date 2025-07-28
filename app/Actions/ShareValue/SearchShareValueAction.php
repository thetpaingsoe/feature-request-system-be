<?php

namespace App\Actions\ShareValue;

use App\Services\ShareValue\ShareValueService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchShareValueAction
{
    public function __construct(
        protected ShareValueService $shareValueService
    ) {}

    public function handle($request, $per_page = 50)
    {
        try {
            $filters = $request->only(['search']);
            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', $per_page);

            $shareValues = $this->shareValueService->search($filters, $sorting, $page, $perPage);

            $rtnData = [
                'shareValuesPagination' => $shareValues,
                'filters' => $filters,
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('SearchShareValueAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
