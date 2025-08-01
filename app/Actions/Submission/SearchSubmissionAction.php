<?php

namespace App\Actions\Submission;

use App\Models\User;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchSubmissionAction
{
    public function __construct(
        protected SubmissionService $submission
    ) {}

    public function handle($request)
    {
        try {

            $filters = $request->only(['search', 'status', 'date_start', 'date_end']);
            $sorting = $request->only(['sort_by', 'sort_direction']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            $user = $request->user();
            // When user is not admin, auto filter by user id
            if (! $user->hasRole('admin')) {
                $filters['user_id'] = $user->id;
            }

            $submissions = $this->submission->search($filters, $sorting, $page, $perPage);

            $rtnData = [
                'submissionsPagination' => $submissions,
                'filters' => $filters,
                'sorting' => $sorting,
            ];

            return $rtnData;
        } catch (Throwable $e) {
            Log::error('SearchSubmissionAction: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }

    }
}
