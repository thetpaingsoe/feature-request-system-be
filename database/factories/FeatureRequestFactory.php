<?php

namespace Database\Factories;

use App\Enums\FeatureRequestStatus;
use App\Models\FeatureRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureRequestFactory extends Factory
{
    protected $model = FeatureRequest::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'email' => $this->faker->safeEmail,
            'status' => $this->faker->randomElement(FeatureRequestStatus::toArray()),
            'submitted_at' => $this->faker->dateTimeThisYear,
            'note' => $this->faker->optional()->paragraph,
        ];
    }
}
