<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionLog extends Model
{
    use HasFactory;

    protected $table = 'submission_logs';

    protected $fillable = [
        'submission_id',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $appends = [
        'user_object',
        'status_change',
        'feedback_message',
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    // Accessor to get target country names
    public function getUserObjectAttribute()
    {
        $userId = $this->data['user_id'] ?? null;

        if ($userId) {
            return User::select('id', 'name', 'email')->find($userId);
            // return User::find($userId);

        }

        return null;
    }

    public function getStatusChangeAttribute()
    {
        $from = $this->data['from'] ?? null;
        $to = $this->data['to'] ?? null;
        if ($from && $to) {
            return [
                'from' => $from,
                'to' => $to,
            ];

        }

        return null;
    }

    public function getFeedbackMessageAttribute()
    {
        $message = $this->data['message'] ?? null;
        if ($message) {
            return $message;

        }

        return null;
    }
}
