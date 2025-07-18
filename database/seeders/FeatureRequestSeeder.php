<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class FeatureRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Define possible statuses
        $statuses = ['pending', 'reviewed', 'approved', 'rejected'];

        // Loop 50 times to create 50 records
        for ($i = 0; $i < 50; $i++) {
            
            // Generate a random past date for submitted_at
            $submittedAt = Carbon::now()->subDays(rand(1, 365));

            // Randomly pick a status
            $status = $faker->randomElement($statuses);

            // Determine if updated_at should be later than submitted_at
            // If status is 'pending', updated_at might be same as created_at/submitted_at
            // If status is reviewed/approved/rejected, updated_at should be later
            $updatedAt = $submittedAt;
            if ($status !== 'pending') {
                $updatedAt = $faker->dateTimeBetween($submittedAt, 'now');
            }

            // Determine note is need or not.
            // If status is 'approved' or 'rejected', it will create some note.
            $note = '';
            if($status === 'approved' || 'rejected') {
                $note = $faker->paragraph(rand(1, 3)); // 1-3 sentences for note.
            }

            DB::table('feature_requests')->insert([
                'title' => $faker->sentence(rand(3, 8)), // A sentence for the title
                'description' => $faker->paragraph(rand(3, 7)), // 3-7 sentences for description
                'email' => $faker->unique()->safeEmail(), // Unique and safe email
                'submitted_at' => $submittedAt, // Use the generated Carbon date
                'status' => $status,
                'note' => $note,
                'created_at' => $submittedAt, // For consistency with submitted_at for initial creation
                'updated_at' => $updatedAt, // Can be same as created_at or later
            ]);
        }
    }
}
