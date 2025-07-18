<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the admin user already exists to prevent duplicates
        if (!DB::table('users')->where('email', 'admin@gmail.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin12345'), // Hash the password
                'email_verified_at' => now(), // Mark email as verified
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->command->info('Admin user already exists. Skipping creation.');
        }
    }
}
