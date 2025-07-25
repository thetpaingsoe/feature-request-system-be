<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',  // e.g. "USD", "THB"
        'amount',    // e.g. 1.00
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function getFormattedValueAttribute()
    {
        return $this->currency.' '.number_format($this->amount, 2);
    }
}
