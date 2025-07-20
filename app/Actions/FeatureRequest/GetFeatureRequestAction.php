<?php

namespace App\Actions\FeatureRequest;

use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetFeatureRequestAction
{
    public function __construct(
        protected FeatureRequestService $featureRequest
    ) {}

    public function handle($id)
    {
        try {

            return $this->featureRequest->get($id);

        } catch (Throwable $e) {
            Log::error('Error getting feature request: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
