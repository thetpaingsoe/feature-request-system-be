<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the admin user already exists to prevent duplicates
        if (! DB::table('users')->where('email', 'admin@gmail.com')->exists()) {

            $admin = User::create([
                'name' => 'Manager',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin12345'), // Hash the password
                'email_verified_at' => now(), // Mark email as verified
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $admin->assignRole('admin');

        } else {
            $this->command->info('Admin user already exists. Skipping creation.');
        }

        if (! DB::table('users')->where('email', 'user1@gmail.com')->exists()) {

            $admin = User::create([
                'name' => 'Jeff',
                'email' => 'user1@gmail.com',
                'password' => Hash::make('user12345'), // Hash the password
                'email_verified_at' => now(), // Mark email as verified
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $admin->assignRole('user');

        } else {
            $this->command->info('User1 user already exists. Skipping creation.');
        }
    }
}
