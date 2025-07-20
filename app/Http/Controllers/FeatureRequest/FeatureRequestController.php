<?php

namespace App\Http\Controllers\FeatureRequest;

use App\Actions\FeatureRequest\GetFeatureRequestAction;
use App\Actions\FeatureRequest\SearchFeatureRequestAction;
use App\Actions\FeatureRequest\UpdateFeatureRequestAction;
use App\Enums\FeatureRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureRequest\FeatureRequestUpdateRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeatureRequestController extends Controller
{
    public function __construct(
        protected SearchFeatureRequestAction $searchFeatureRequestAction,
        protected GetFeatureRequestAction $getFeatureRequestAction,
        protected UpdateFeatureRequestAction $updateFeatureRequestAction,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchFeatureRequestAction->handle($request);

        return Inertia::render('feature-requests/Index', $rtnData);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $featureRequest = $this->getFeatureRequestAction->handle($id);
        
        return Inertia::render('feature-requests/Edit', [
            'featureRequest' => $featureRequest,
            'statuses' => FeatureRequestStatus::toArray()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeatureRequestUpdateRequest $request, $id)
    {
        $this->updateFeatureRequestAction->handle($id, $request);

        return redirect()->route('feature-requests.index')->with('success', 'Feature request updated successfully.');
    }
}
