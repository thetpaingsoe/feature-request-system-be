<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDesignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
