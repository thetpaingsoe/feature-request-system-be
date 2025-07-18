<?php

namespace actions\FeatureRequest;

use Illuminate\Support\Facades\Log;
use services\FeatureRequest\FeatureRequestService;
use Throwable;

class UpdateFeatureRequestAction {

    public function __construct(
        protected FeatureRequestService $featureRequest
    )
    {}

    public function handle($request) { 
        try{

            $this->featureRequest->updateStatus();

            $this->featureRequest->updateNote();

        }catch(Throwable $e) {
            Log::error('Error updating feature request: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
        
    }
}