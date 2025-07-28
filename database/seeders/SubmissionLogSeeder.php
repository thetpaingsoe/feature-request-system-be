<?php

namespace Database\Seeders;

use App\Models\Submission;
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

        // For submission_id = 2 to 5: only create logs
        foreach ([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20] as $id) {
            SubmissionLog::create([
                'submission_id' => $id,
                'type' => 'create',
                'data' => ['user_id' => '2'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->update(20, $now, 5);

        $this->statusChange(19, 1, "pending", "reviewing", $now, 6);

        $this->statusChange(20, 1, "pending", "reviewing", $now, 9);

        $this->statusChange(20, 1, "reviewing", "feedback", $now, 15);
        $this->feedback(20, 1, 
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus interdum massa non sollicitudin iaculis. Phasellus rutrum consequat velit, sed accumsan velit pharetra accumsan.",
            $now, 15
        );

        $this->statusChange(17, 1, "pending", "reviewing", $now, 19);
        $this->statusChange(16, 1, "pending", "reviewing", $now, 19);
        $this->statusChange(15, 1, "pending", "reviewing", $now, 19);

        $this->statusChange(19, 1, "reviewing", "feedback", $now, 24);
        $this->feedback(19, 1, 
            "Maecenas posuere orci vitae imperdiet pulvinar. Donec facilisis sed nibh in volutpat. In dignissim placerat nunc,",
            $now, 24
        );

        $this->statusChange(18, 1, "pending", "reviewing", $now, 25);

        $this->statusChange(16, 1, "reviewing", "feedback", $now, 26);
        $this->feedback(16, 1, 
            "Quisque hendrerit malesuada diam,",
            $now, 26
        );

        $this->statusChange(18, 1, "reviewing", "feedback", $now, 28);
        $this->feedback(18, 1, 
            "Suspendisse vitae urna felis. Quisque hendrerit malesuada diam,",
            $now, 28
        );

        $this->feedback(20, 2, 
            "Quisque sagittis pellentesque consequat.",
            $now, 29
        );

        $this->statusChange(14, 1, "pending", "reviewing", $now, 30);

        $this->update(19, $now, 34);

        $this->statusChange(15, 1, "reviewing", "feedback", $now, 36);
        $this->feedback(15, 1, 
            "Nam a viverra justo, id pellentesque lectus. ",
            $now, 36
        );

        $this->statusChange(20, 2, "feedback", "reviewing", $now, 40);
        $this->rejectSugg(20, 2, $now, 40);
        
        $this->feedback(18, 2, 
            "Nullam nulla sem, dignissim id porttitor quis, auctor ultrices turpis. Vestibulum vitae tincidunt elit, at tempus erat.",
            $now, 50
        );

        $this->statusChange(20, 1, "reviewing", "rejected", $now, 55);
        $this->reject(20, 1, $now, 55);

        $this->statusChange(19, 2, "feedback", "reviewing", $now, 60);
        $this->acceptSugg(19, 2, $now, 60);

        $this->statusChange(14, 1, "reviewing", "approved", $now, 65);
        $this->approve(14, 1, $now, 65);

    }

    function approve($id, $userId, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'approved',
            'data' => ['user_id' => $userId],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function reject($id, $userId, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'rejected',
            'data' => ['user_id' => $userId],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function acceptSugg($id, $userId, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'sugg_accept',
            'data' => ['user_id' => $userId],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function rejectSugg($id, $userId, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'sugg_reject',
            'data' => ['user_id' => $userId],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function feedback($id, $userId, $msg, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'sugg_message',
            'data' => ['user_id' => $userId, 'message' => $msg],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function update($id, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'update',
            'data' => ['user_id' => '2'],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
    }

    function statusChange($id, $userId, $from, $to, $now, $min) {
        SubmissionLog::create([
            'submission_id' => $id,
            'type' => 'status_change',
            'data' => ['user_id' => $userId, 'from' => $from, 'to' => $to],
            'created_at' => $now->copy()->addMinutes($min),
            'updated_at' => $now->copy()->addMinutes($min),
        ]);
        $submission = Submission::findOrFail($id);
        $submission->status = $to;
        $submission->save();
    }
}
