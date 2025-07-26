<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            AdminUserSeeder::class,
            FeatureRequestSeeder::class,
            CompanyDesignationSeeder::class,
            ShareValueSeeder::class,
            CountrySeeder::class,
            SubmissionSeeder::class,
            SubmissionLogSeeder::class,
        ]);
    }
}
