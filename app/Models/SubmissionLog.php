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

    // Relationships
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
