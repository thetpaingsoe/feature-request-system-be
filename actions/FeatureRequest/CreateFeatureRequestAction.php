<?php

use App\Http\Requests\FeatureRequest\FeatureRequestStoreRequest;
use services\FeatureRequest\FeatureRequestService;
use Throwable;
use Illuminate\Support\Facades\Log; 

class CreateFeatureRequestAction
{

    public function __construct(
        protected FeatureRequestService $featureRequest
    )
    {}

    public function handle(FeatureRequestStoreRequest $request) {
        try{

            $this->featureRequest->create();

        }catch(Throwable $e) {
            Log::error('Error creating feature request: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}