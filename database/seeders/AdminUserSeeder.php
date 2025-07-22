<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@bdms.com'],
            [
                'name' => 'BDMS Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create a test donor user
        User::firstOrCreate(
            ['email' => 'donor@bdms.com'],
            [
                'name' => 'Test Donor',
                'password' => Hash::make('password'),
                'role' => 'donor',
                'email_verified_at' => now(),
            ]
        );
    }
}
