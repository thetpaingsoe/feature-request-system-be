<?php

namespace App\Services\FeatureRequest;

use App\Models\FeatureRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class FeatureRequestService
{
    public function search() {}

    public function get($id) {}

    public function create($data)
    {
        try {
            $featureRequest = FeatureRequest::create([
                'title' => $data->title,
                'description' => $data->description,
                'email' => $data->email,
            ]);

            $featureRequest->refresh(); // to include full object

            return $featureRequest;

        } catch (Throwable $e) {
            Log::error('FeatureRequestService::create : '.$e->getMessage(), ['exception' => $e]);
        }

        return $data;
    }

    public function updateStatus() {}

    public function updateNote() {}

    /* ?? */
    public function delete() {}
}
