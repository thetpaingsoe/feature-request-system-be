<?php

namespace Database\Seeders;

use App\Models\CompanyDesignation;
use App\Models\Country;
use App\Models\ShareValue;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Create a user if not exists
        $user = User::firstOrCreate(
            ['email' => 'user1@gmail.com'],
            [
                'name' => 'User1',
                'password' => bcrypt('password'), // You can change this
            ]
        );

        $designations = CompanyDesignation::all();
        $countries = Country::all();
        $shareValues = ShareValue::all();

        if ($designations->isEmpty() || $countries->isEmpty() || $shareValues->isEmpty()) {
            $this->command->error('Please run other seeders first: CompanyDesignation, Country, ShareValue');

            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            Submission::create([
                'user_id' => $user->id,
                'full_name' => $faker->name,
                'email' => $faker->email,
                'company_name' => $faker->company,
                'alternative_company_name' => $faker->company,
                'company_designation_id' => $designations->random()->id,
                'jurisdiction_of_operation_id' => $countries->random()->id,
                'target_jurisdictions' => $countries->random(3)->pluck('id')->toArray(),
                'number_of_shares' => rand(90000, 10000),
                'are_all_shares_issued' => 0,
                'number_of_issued_shares' => rand(40000, 80000),
                'share_value_id' => $shareValues->random()->id,
                'shareholders' => [
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                        'percentage' => 60,
                    ],
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                        'percentage' => 40,
                    ],
                ],
                'beneficial_owners' => [
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                    ],
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                    ],
                ],
                'directors' => [
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                    ],
                    [
                        'name' => $faker->name,
                        'email' => $faker->email,
                    ],
                ],
                'status' => 'pending',
            ]);
        }
    }
}
