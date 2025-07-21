<?php

namespace Database\Seeders;

use App\Models\Donor;
use Illuminate\Database\Seeder;

class DonorSeeder extends Seeder
{
    public function run()
    {
        Donor::factory(50)->create(); // Create 50 donor records
    }
}