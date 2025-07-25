<?php

namespace Database\Seeders;

use App\Models\CompanyDesignation;
use Illuminate\Database\Seeder;

class CompanyDesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            'LLC',
            'PLC',
            'Inc.',
            'GmbH',
            'Ltd.',
            'S.A.',
            'Pty Ltd',
            'Corp.',
        ];

        foreach ($designations as $name) {
            CompanyDesignation::create(['name' => $name]);
        }
    }
}
