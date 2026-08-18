<?php

namespace Database\Seeders;

use App\Models\Donor;
use Illuminate\Database\Seeder;

class DonorSeeder extends Seeder
{
    public function run()
    {
        $donorUser = \App\Models\User::factory()->create([
            'name' => 'Test Donor',
            'email' => 'donor@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'donor',
        ]);
        Donor::factory()->create(['user_id' => $donorUser->id]);

        Donor::factory(49)->create(); // Create 49 more random donor records
    }
}