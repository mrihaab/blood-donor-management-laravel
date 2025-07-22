<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BloodGroupFactory extends Factory
{
    public function definition(): array
    {
        $types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        
        return [
            'name' => fake()->unique()->randomElement($types),
            'description' => fake()->sentence(),
        ];
    }
}