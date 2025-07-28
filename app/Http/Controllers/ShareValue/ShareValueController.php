<?php

namespace App\Http\Controllers\ShareValue;

use App\Actions\ShareValue\SearchShareValueAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ShareValueController extends Controller
{
    public function __construct(
        protected SearchShareValueAction $searchShareValueAction,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchShareValueAction->handle($request, per_page: 10);

        $flashedToastData = Session::pull('toast');
        if ($flashedToastData) {
            $rtnData['flash'] = $flashedToastData;
        }

        return Inertia::render('share-values/Index', $rtnData);

    }
}
