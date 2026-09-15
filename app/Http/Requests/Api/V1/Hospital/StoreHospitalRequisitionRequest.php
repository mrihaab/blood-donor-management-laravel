<?php

namespace App\Http\Requests\Api\V1\Hospital;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class StoreHospitalRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->role === 'hospital' && $user->hospital_id !== null;
    }

    public function rules(): array
    {
        $user = $this->user();
        $hospitalId = $user ? $user->hospital_id : null;

        return [
            'patient_id' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($hospitalId) {
                    $patient = Patient::find($value);
                    if (!$patient || (int) $patient->hospital_id !== (int) $hospitalId) {
                        $fail('Selected patient does not belong to your hospital.');
                    }
                },
            ],
            'blood_group' => [
                'required',
                'string',
                'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            ],
            'units_needed' => [
                'required',
                'integer',
                'min:1',
                'max:50',
            ],
            'urgency_level' => [
                'required',
                'string',
                'in:routine,urgent,emergency',
            ],
            'required_by' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'attendant_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'attendant_phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'ward_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'room_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'bed_number' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $inputs = [];

        // Support 'urgency' alias mapping to 'urgency_level'
        if ($this->has('urgency') && !$this->has('urgency_level')) {
            $inputs['urgency_level'] = $this->input('urgency');
        }

        foreach (['reason', 'attendant_name', 'attendant_phone', 'ward_name', 'room_number', 'bed_number'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $trimmed = trim($this->input($field));
                $inputs[$field] = $trimmed === '' ? null : $trimmed;
            }
        }

        if (!empty($inputs)) {
            $this->merge($inputs);
        }
    }
}
