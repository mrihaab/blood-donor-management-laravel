<?php

namespace Database\Seeders;

use App\Models\BloodGroup;
use Illuminate\Database\Seeder;

class BloodGroupSeeder extends Seeder
{
    public function run()
    {
        $bloodGroups = [
            ['name' => 'A+', 'description' => 'A positive'],
            ['name' => 'A-', 'description' => 'A negative'],
            ['name' => 'B+', 'description' => 'B positive'],
            ['name' => 'B-', 'description' => 'B negative'],
            ['name' => 'AB+', 'description' => 'AB positive'],
            ['name' => 'AB-', 'description' => 'AB negative'],
            ['name' => 'O+', 'description' => 'O positive'],
            ['name' => 'O-', 'description' => 'O negative'],
        ];

        foreach ($bloodGroups as $group) {
            // Avoid duplicates by checking if it exists
            BloodGroup::firstOrCreate(['name' => $group['name']], [
                'description' => $group['description'],
            ]);
        }
    }
}
