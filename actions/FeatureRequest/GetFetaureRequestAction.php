<?php

use Illuminate\Support\Facades\Log;
use services\FeatureRequest\FeatureRequestService;
use Throwable;

class GetFeatureRequestAction
{

    public function __construct(
        protected FeatureRequestService $featureRequest
    )
    {}

    public function handle($request) {
        try{

            $this->featureRequest->get(1);

        }catch(Throwable $e) {
            Log::error('Error getting feature request: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}