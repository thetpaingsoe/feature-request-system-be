<?php

namespace App\DTOs\Submission;

use Spatie\LaravelData\Data;

class StoreSubmissionDto extends Data
{
    public function __construct(

        public string $full_name,
        public string $email,
        public string $company_name,
        public string $alternative_company_name,
        public int $company_designation_id,
        public int $jurisdiction_of_operation_id,
        public array $target_jurisdictions,
        public int $number_of_shares,
        public bool $are_all_shares_issued,
        public int $number_of_issued_shares,
        public int $share_value_id,
        public string $shareholders,
        public string $beneficial_owners,
        public string $directors,

        // Note: 'user_id'
        // Note: 'status'
    ) {}
}
