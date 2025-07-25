<?php

namespace Database\Seeders;

use App\Models\SubmissionLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SubmissionLogSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // For submission_id = 1: create and update logs
        SubmissionLog::create([
            'submission_id' => 1,
            'type' => 'create',
            'data' => ['user_id' => '2'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        SubmissionLog::create([
            'submission_id' => 1,
            'type' => 'update',
            'data' => ['user_id' => '2'],
            'created_at' => $now->copy()->addMinutes(5),
            'updated_at' => $now->copy()->addMinutes(5),
        ]);

        // For submission_id = 2 to 5: only create logs
        foreach ([2, 3, 4, 5] as $id) {
            SubmissionLog::create([
                'submission_id' => $id,
                'type' => 'create',
                'data' => ['user_id' => '2'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
