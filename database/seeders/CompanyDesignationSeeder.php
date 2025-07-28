<?php

namespace Database\Seeders;

use App\Models\CompanyDesignation;
use Illuminate\Database\Seeder;

class CompanyDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            'CHRO (Chief Human Resources Officer)',
            'CDO (Chief Data Officer)',
            'CMO (Chief Marketing Officer)',
            'CIO (Chief Information Officer)',
            'CTO (Chief Technology Officer)',
            'COO (Chief Operating Officer)',
            'CFO (Chief Financial Officer)',
            'CEO (Chief Executive Officer)',
        ];

        foreach ($designations as $name) {
            CompanyDesignation::create(['name' => $name]);
        }
    }
}
