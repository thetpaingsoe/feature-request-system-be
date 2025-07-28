<?php

namespace App\Http\Controllers;

use App\Actions\Submission\GetSubmissionCountsAction;
use App\Actions\SubmissionLog\SearchSubmissionLogAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected SearchSubmissionLogAction $searchSubmissionLogAction,
        protected GetSubmissionCountsAction $getSubmissionCountsAction
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchSubmissionLogAction->handle($request, null, 10);

        $counts = $this->getSubmissionCountsAction->handle();

        $rtnData['counts'] = $counts;
        $flashedToastData = Session::pull('toast');
        if ($flashedToastData) {
            $rtnData['flash'] = $flashedToastData;
        }

        return Inertia::render('Dashboard', $rtnData);

    }
}
