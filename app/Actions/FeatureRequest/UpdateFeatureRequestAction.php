<?php

namespace App\Actions\FeatureRequest;

use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateFeatureRequestAction
{
    public function __construct(
        protected FeatureRequestService $featureRequestService
    ) {}

    public function handle($id, $request)
    {

        DB::beginTransaction();

        try {

            $data = $request->validated();

            $featureRequest = $this->featureRequestService->get($id);
            
            $featureRequest = $this->featureRequestService->updateStatus($featureRequest, $data['status']);
            $featureRequest = $this->featureRequestService->updateNote($featureRequest, $data['note']);

            DB::commit();

            return $featureRequest;

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error updating feature request: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }


    }
}
