<?php

namespace App\Actions\Country;

use App\Services\Country\CountryService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchCountryAction
{
    public function __construct(
        protected CountryService $countryService
    ) {}

    public function handle($request)
    {
        try {
            $filters = $request->only(['search']);
            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 50);

            $countries = $this->countryService->search($filters, $sorting, $page, $perPage);

            $rtnData = [
                'countriesPagination' => $countries,
                'filters' => $filters,
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('SearchCountryAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
