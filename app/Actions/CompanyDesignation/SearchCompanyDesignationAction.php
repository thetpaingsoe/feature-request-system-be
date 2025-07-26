<?php

namespace App\Actions\CompanyDesignation;

use App\Services\CompanyDesignation\CompanyDesignationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchCompanyDesignationAction
{
    public function __construct(
        protected CompanyDesignationService $companyDesignationService
    ) {}

    public function handle($request)
    {
        try {
            $filters = $request->only(['search']);
            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 50);

            $companyDesignations = $this->companyDesignationService->search($filters, $sorting, $page, $perPage);

            $rtnData = [
                'companyDesignationsPagination' => $companyDesignations,
                'filters' => $filters,
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('class SearchCompanyDesignationAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
