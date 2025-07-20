<?php

namespace App\Http\Controllers\FeatureRequest;

use App\Actions\FeatureRequest\SearchFeatureRequestAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeatureRequestController extends Controller
{
    public function __construct(protected SearchFeatureRequestAction $searchFeatureRequest) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $rtnData = $this->searchFeatureRequest->handle($request);

        return Inertia::render('feature-requests/FeatureRequestList', $rtnData);

    }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit($id)
    // {
    //     $featureRequest = $this->featureRequestService->get($id);

    //     return Inertia::render('FeatureRequest/Edit', [
    //         'featureRequest' => $featureRequest,
    //     ]);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(UpdateFeatureRequest $request, $id)
    // {
    //     $data = $request->validated();
    //     $this->featureRequestService->update($id, $data);

    //     return redirect()->route('feature-requests.index')->with('success', 'Feature request updated successfully.');
    // }
}
