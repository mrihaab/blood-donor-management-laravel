<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            "hospital_id" => null,
            "name" => $this->faker->name(),
            "mrn" => "MRN-" . strtoupper($this->faker->bothify("???###")),
            "gender" => $this->faker->randomElement(["male", "female", "other"]),
            "date_of_birth" => $this->faker->date("Y-m-d", "-18 years"),
            "blood_group_id" => null,
            "contact_number" => $this->faker->phoneNumber(),
            "status" => "active",
            "ward_name" => "General Ward",
            "room_number" => "101",
            "bed_number" => "A",
        ];
    }
}
