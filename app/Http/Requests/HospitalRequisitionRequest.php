<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class HospitalRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isAdmin() || ($user->isHospital() && $user->hospital_id !== null));
    }

    public function rules(): array
    {
        $user = $this->user();
        $hospitalId = $user->hospital_id;

        return [
            'patient_id' => [
                'required',
                'exists:patients,id',
                function ($attribute, $value, $fail) use ($hospitalId, $user) {
                    if ($user->isHospital() && $hospitalId) {
                        $patient = Patient::find($value);
                        if ($patient && (int)$patient->hospital_id !== (int)$hospitalId) {
                            $fail('Selected patient does not belong to your hospital.');
                        }
                    }
                },
            ],
            'blood_group' => ['required', 'string', 'exists:blood_groups,name'],
            'units_needed' => ['required', 'integer', 'min:1', 'max:50'],
            'urgency' => ['nullable', 'string', 'in:routine,urgent,emergency'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
