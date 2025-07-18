<?php

namespace App\DTOs\FeatureRequest;

use Spatie\LaravelData\Data;

class StoreFeatureRequestDto extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        public string $email,
    ) {}
}
