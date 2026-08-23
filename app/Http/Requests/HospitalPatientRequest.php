<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HospitalPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || ($user->isHospital() && $user->hospital_id !== null));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mrn' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'blood_group_id' => ['nullable', 'exists:blood_groups,id'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:active,discharged,archived'],
        ];
    }
}
