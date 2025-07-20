<?php

namespace App\Actions\FeatureRequest;

use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchFeatureRequestAction
{
    public function __construct(
        protected FeatureRequestService $featureRequest
    ) {}

    public function handle($request)
    {
        try {

            $filters = $request->only(['search', 'status', 'date_start', 'date_end']);
            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            $featureRequests = $this->featureRequest->search($filters, $sorting, $page, $perPage);

            $rtnData = [
                'featureRequestsPagnication' => $featureRequests,
                'filters' => $filters,
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('Error searching feature request: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
