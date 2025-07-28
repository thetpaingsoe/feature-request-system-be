<?php

namespace App\Http\Controllers\Country;

use App\Actions\Country\SearchCountryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CountryController extends Controller
{
    public function __construct(
        protected SearchCountryAction $searchCountryAction,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rtnData = $this->searchCountryAction->handle($request, per_page: 10);

        $flashedToastData = Session::pull('toast');
        if ($flashedToastData) {
            $rtnData['flash'] = $flashedToastData;
        }

        return Inertia::render('countries/Index', $rtnData);

    }
}
