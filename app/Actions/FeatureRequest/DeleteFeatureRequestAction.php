<?php

namespace App\Actions\FeatureRequest;

use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteFeatureRequestAction
{
    public function __construct(
        protected FeatureRequestService $featureRequestService
    ) {}

    public function handle($id, $request)
    {

        DB::beginTransaction();

        try {

            $featureRequest = $this->featureRequestService->get($id);

            $featureRequest = $this->featureRequestService->delete($featureRequest);            

            DB::commit();

            return $featureRequest;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error delete feature request: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }


    }
}
