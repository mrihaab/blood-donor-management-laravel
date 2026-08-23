<?php

namespace App\Http\Requests\Donor;

use Illuminate\Foundation\Http\FormRequest;

class StoreBloodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_name' => 'required|string|max:255',
            'blood_group' => 'required|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'units_needed' => 'required|integer|min:1|max:20',
            'hospital' => 'required|string|max:255',
            'hospital_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'urgency' => 'nullable|in:normal,urgent,emergency',
            'required_date' => 'nullable|date',
            'reason' => 'nullable|string|max:1000',
        ];
    }
}
