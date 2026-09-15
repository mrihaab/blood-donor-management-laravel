<?php

namespace App\Http\Resources\Api\V1\Hospital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalRequisitionDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => $this->patient ? [
                'id' => $this->patient->id,
                'mrn' => $this->patient->mrn,
                'name' => $this->patient->name,
                'gender' => $this->patient->gender,
                'date_of_birth' => $this->patient->date_of_birth ? $this->patient->date_of_birth->format('Y-m-d') : null,
                'contact_number' => $this->patient->contact_number,
            ] : [
                'id' => $this->patient_id,
                'mrn' => null,
                'name' => $this->patient_name,
                'gender' => null,
                'date_of_birth' => null,
                'contact_number' => null,
            ],
            'blood_group' => $this->blood_group,
            'units_needed' => (int) $this->units_needed,
            'urgency_level' => $this->urgency_level,
            'reason' => $this->reason,
            'status' => $this->status,
            'required_by' => $this->required_by ? $this->required_by->toIso8601String() : null,
            'ward_name' => $this->ward_name,
            'room_number' => $this->room_number,
            'bed_number' => $this->bed_number,
            'attendant_name' => $this->attendant_name,
            'attendant_phone' => $this->attendant_phone,
            'approved_at' => $this->approved_at ? $this->approved_at->toIso8601String() : null,
            'rejected_at' => $this->rejected_at ? $this->rejected_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
