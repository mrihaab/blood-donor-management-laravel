<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::factory()->create([
            'user_id' => \App\Models\User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ]),
            'position' => 'System Administrator',
            'department' => 'IT',
        ]);

        Admin::factory(4)->create(); // Create 4 more random admins
    }
}