<?php

namespace App\Http\Controllers\Api;

use App\Actions\FeatureRequest\StoreFeatureRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureRequest\FeatureRequestStoreRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class FeatureRequestApiController extends Controller
{
    public function __construct(
        protected StoreFeatureRequestAction $createFeatureRequest
    ) {}

    public function index()
    {
        return 'List';
    }

    public function store(FeatureRequestStoreRequest $request)
    {
        try {
            return response()->json($this->createFeatureRequest->handle($request), 201);
        } catch (Throwable $e) {
            Log::error('FeatureRequestApiController::store : '.$e->getMessage(), ['exception' => $e]);
            return response()->json('Submitting error. '.$e->getMessage());
        }

    }
}
