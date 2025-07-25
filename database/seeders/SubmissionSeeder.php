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
        // Create a user if not exists
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
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

        for ($i = 1; $i <= 5; $i++) {
            Submission::create([
                'user_id' => $user->id,
                'full_name' => "Applicant $i",
                'email' => "applicant$i@example.com",
                'company_name' => "Company $i",
                'alternative_company_name' => "Alt Co $i",
                'company_designation_id' => $designations->random()->id,
                'jurisdiction_of_operation_id' => $countries->random()->id,
                'target_jurisdictions' => $countries->random(3)->pluck('id')->toArray(),
                'number_of_shares' => rand(1000, 10000),
                'are_all_shares_issued' => (bool) rand(0, 1),
                'number_of_issued_shares' => rand(100, 9000),
                'share_value_id' => $shareValues->random()->id,
                'shareholders' => [
                    [
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'percentage' => 60,
                    ],
                    [
                        'name' => 'Jane Smith',
                        'email' => 'jane@example.com',
                        'percentage' => 40,
                    ],
                ],
                'beneficial_owners' => [
                    [
                        'name' => 'Emily Doe',
                        'relationship' => 'Spouse',
                    ],
                    [
                        'name' => 'Richard Roe',
                        'relationship' => 'Child',
                    ],
                ],
                'directors' => [
                    [
                        'name' => 'Mark Lee',
                        'position' => 'CEO',
                        'email' => 'mark@company.com',
                    ],
                    [
                        'name' => 'Lisa Chan',
                        'position' => 'CFO',
                        'email' => 'lisa@company.com',
                    ],
                ],
                'status' => 'pending',
            ]);
        }
    }
}
