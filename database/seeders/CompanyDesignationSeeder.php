<?php

namespace Database\Seeders;

use App\Models\CompanyDesignation;
use Illuminate\Database\Seeder;

class CompanyDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            'CEO (Chief Executive Officer)',
            'CFO (Chief Financial Officer)',
            'COO (Chief Operating Officer)',
            'CIO (Chief Information Officer)',
            'CMO (Chief Marketing Officer)',
            'CTO (Chief Technology Officer)',
            'CHRO (Chief Human Resources Officer)',
            'CDO (Chief Data Officer)',
            'Other'
        ];

        foreach ($designations as $name) {
            CompanyDesignation::create(['name' => $name]);
        }
    }
}
