<?php

namespace App\Actions\Submission;

use App\DTOs\Submission\StoreSubmissionDto;
use App\Http\Requests\Submission\SubmissionStoreRequest;
use App\Services\Submission\SubmissionService;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreSubmissionAction
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    public function handle(SubmissionStoreRequest $request)
    {
        try {

            $data = StoreSubmissionDto::from($request->validated());

            return $this->submissionService->create($data);

        } catch (Throwable $e) {
            Log::error('StoreSubmissionAction::handle : '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
