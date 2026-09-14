<?php

namespace App\Http\Requests\Api\V1\Hospital;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHospitalPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has("name") && is_string($this->input("name"))) {
            $merge["name"] = trim($this->input("name"));
        }

        if ($this->has("mrn") && is_string($this->input("mrn"))) {
            $merge["mrn"] = strtoupper(trim($this->input("mrn")));
        }

        if ($this->has("gender") && is_string($this->input("gender"))) {
            $merge["gender"] = strtolower(trim($this->input("gender")));
        }

        foreach (["contact_number", "ward_name", "room_number", "bed_number"] as $field) {
            if ($this->has($field)) {
                $val = $this->input($field);
                if (is_string($val)) {
                    $val = trim($val);
                    $merge[$field] = $val === "" ? null : $val;
                }
            }
        }

        if (! empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        $hospitalId = $this->user()?->hospital_id;
        $canonicalBloodGroups = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];

        return [
            "name" => ["required", "string", "max:255"],
            "mrn" => [
                "required",
                "string",
                "max:100",
                Rule::unique("patients", "mrn")->where(function ($query) use ($hospitalId) {
                    return $query->where("hospital_id", $hospitalId);
                }),
            ],
            "gender" => ["required", "string", "in:male,female,other"],
            "date_of_birth" => ["required", "date_format:Y-m-d", "before:today"],
            "blood_group_id" => [
                "nullable",
                "integer",
                Rule::exists("blood_groups", "id")->where(function ($query) use ($canonicalBloodGroups) {
                    return $query->whereIn("name", $canonicalBloodGroups);
                }),
            ],
            "contact_number" => ["nullable", "string", "max:50"],
            "ward_name" => ["nullable", "string", "max:100"],
            "room_number" => ["nullable", "string", "max:50"],
            "bed_number" => ["nullable", "string", "max:50"],
            "id" => ["prohibited"],
            "hospital_id" => ["prohibited"],
            "user_id" => ["prohibited"],
            "status" => ["prohibited"],
            "created_at" => ["prohibited"],
            "updated_at" => ["prohibited"],
        ];
    }
}
