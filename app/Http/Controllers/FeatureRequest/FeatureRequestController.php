<?php

namespace App\Http\Controllers\FeatureRequest;

use App\Actions\FeatureRequest\DeleteFeatureRequestAction;
use App\Actions\FeatureRequest\GetFeatureRequestAction;
use App\Actions\FeatureRequest\SearchFeatureRequestAction;
use App\Actions\FeatureRequest\UpdateFeatureRequestAction;
use App\Enums\FeatureRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeatureRequest\FeatureRequestUpdateRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Throwable;

class FeatureRequestController extends Controller
{
    public function __construct(
        protected SearchFeatureRequestAction $searchFeatureRequestAction,
        protected GetFeatureRequestAction $getFeatureRequestAction,
        protected UpdateFeatureRequestAction $updateFeatureRequestAction,
        protected DeleteFeatureRequestAction $deleteFeatureRequestAction,
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

    public function destroy(Request $request, $id)
    {
        try {
            $this->deleteFeatureRequestAction->handle($id, $request); 

            $currentQueryParams = $request->query();
            
            return redirect()->route('feature-requests.index', $currentQueryParams)
                             ->with('success', 'Feature request deleted successfully!');
        } catch (Throwable $e) {
            Log::error('Error deleting feature request: '.$e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['delete' => 'Failed to delete feature request.']);
        }
    }
}
