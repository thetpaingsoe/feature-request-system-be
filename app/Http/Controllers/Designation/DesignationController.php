<?php

namespace App\Http\Controllers\Designation;

use App\Actions\CompanyDesignation\SearchCompanyDesignationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class DesignationController extends Controller
{
    public function __construct(
        protected SearchCompanyDesignationAction $searchDesignationAction,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchDesignationAction->handle($request, per_page: 10);

        $flashedToastData = Session::pull('toast');
        if ($flashedToastData) {
            $rtnData['flash'] = $flashedToastData;
        }

        return Inertia::render('designations/Index', $rtnData);

    }
}
