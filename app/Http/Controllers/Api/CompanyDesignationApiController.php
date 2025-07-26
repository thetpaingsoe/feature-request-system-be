<?php

namespace App\Http\Controllers\Api;

use App\Actions\CompanyDesignation\SearchCompanyDesignationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CompanyDesignationApiController extends Controller
{
    public function __construct(
        protected SearchCompanyDesignationAction $searchCompanyDesignationAction
    ) {}

    public function search(Request $request)
    {
        try {
            return response()->json($this->searchCompanyDesignationAction->handle($request), 200);
        } catch (Throwable $e) {
            Log::error('CompanyDesignationApiController::search : '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'An unexpected server error occurred. Please try again later.',
            ], 500);
        }

    }
}
