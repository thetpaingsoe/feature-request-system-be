<?php

namespace App\Http\Controllers\Submission;

use App\Actions\Submission\DeleteSubmissionAction;
use App\Actions\Submission\GetSubmissionAction;
use App\Actions\Submission\SearchSubmissionAction;
use App\Actions\Submission\UpdateSubmissionAction;
use App\Actions\Submission\UpdateSubmissionStatusAction;
use App\Actions\SubmissionLog\SearchSubmissionLogAction;
use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Submission\SubmissionUpdateRequest;
use App\Http\Requests\Submission\SubmissionUpdateStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Throwable;

class SubmissionController extends Controller
{
    public function __construct(
        protected SearchSubmissionAction $searchSubmissionAction,
        protected GetSubmissionAction $getSubmissionAction,
        protected UpdateSubmissionAction $updateSubmissionAction,
        protected DeleteSubmissionAction $deleteSubmissionAction,
        protected SearchSubmissionLogAction $searchSubmissionLogAction,
        protected UpdateSubmissionStatusAction $updateSubmissionStatusAction
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchSubmissionAction->handle($request);

        $flashedToastData = Session::pull('toast');
        if ($flashedToastData) {
            $rtnData['flash'] = $flashedToastData;
        }

        return Inertia::render('submissions/Index', $rtnData);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $submission = $this->getSubmissionAction->handle($id);

        $submissionLogPagination = $this->searchSubmissionLogAction->handle($request, $id);

        $rtnData = [
            'submission' => $submission,
            'statuses' => SubmissionStatus::toArray(),

        ];
        $rtnData = array_merge($rtnData, $submissionLogPagination);

        return Inertia::render('submissions/Edit', $rtnData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubmissionUpdateRequest $request, $id)
    {
        $this->updateSubmissionAction->handle($id, $request);

        return redirect()->route('submissions.index')
            ->with('toast', [
                'title' => 'Updated !',
                'type' => 'success',
                'message' => 'Submission has been updated successfully!',
            ]
            );
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->deleteSubmissionAction->handle($id, $request);

            $currentQueryParams = $request->query();

            return redirect()->route('submissions.index', $currentQueryParams)
                ->with('toast', [
                    'title' => 'Deleted !',
                    'type' => 'success',
                    'message' => 'Submission deleted successfully!',
                ]
                );
        } catch (Throwable $e) {
            Log::error('Error deleting submission: '.$e->getMessage(), ['exception' => $e]);

            return back()->withErrors(['delete' => 'Failed to delete submission.']);
        }
    }

    public function updateStatus(SubmissionUpdateStatusRequest $request, $id)
    {
        $this->updateSubmissionStatusAction->handle($id, $request);

        return redirect()->back();
    }
}
