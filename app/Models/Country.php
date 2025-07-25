<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code', // e.g. "US", "SG", "TH"
    ];

    public function jurisdictionOfOperationForms()
    {
        return $this->hasMany(Submission::class, 'jurisdiction_of_operation_id');
    }

    // No direct relationship for target_jurisdictions (it's a JSON array)
}
