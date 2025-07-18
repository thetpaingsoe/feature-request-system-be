<?php

namespace App\Actions\FeatureRequest;

use App\DTOs\FeatureRequest\StoreFeatureRequestDto;
use App\Http\Requests\FeatureRequest\FeatureRequestStoreRequest;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreFeatureRequestAction
{
    public function __construct(
        protected FeatureRequestService $featureRequestService
    ) {}

    public function handle(FeatureRequestStoreRequest $request)
    {
        try {

            $data = StoreFeatureRequestDto::from($request->validated());

            return $this->featureRequestService->create($data);

        } catch (Throwable $e) {
            Log::error('StoreFeatureRequestAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
