<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $table = 'submissions';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'company_name',
        'alternative_company_name',
        'company_designation_id',
        'jurisdiction_of_operation_id',
        'target_jurisdictions',
        'number_of_shares',
        'are_all_shares_issued',
        'number_of_issued_shares',
        'share_value_id',
        'shareholders',
        'beneficial_owners',
        'directors',
        'status',
    ];

    protected $casts = [
        'target_jurisdictions' => 'array',
        'shareholders' => 'array',
        'beneficial_owners' => 'array',
        'directors' => 'array',
        'are_all_shares_issued' => 'boolean',
    ];

    protected $appends = [
        'target_jurisdiction_names',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companyDesignation()
    {
        return $this->belongsTo(CompanyDesignation::class);
    }

    public function jurisdictionOfOperation()
    {
        return $this->belongsTo(Country::class, 'jurisdiction_of_operation_id');
    }

    public function shareValue()
    {
        return $this->belongsTo(ShareValue::class);
    }

    // Accessor to get target country names
    public function getTargetJurisdictionNamesAttribute()
    {
        if (! $this->target_jurisdictions) {
            return [];
        }

        return Country::whereIn('id', $this->target_jurisdictions)
            ->get(['id', 'name', 'code'])
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                    'code' => $country->code,
                ];
            })
            ->toArray();
    }
}
