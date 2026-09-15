<?php

namespace App\Http\Resources\Api\V1\Hospital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalRequisitionListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_name' => $this->patient_name,
            'patient' => $this->patient ? [
                'id' => $this->patient->id,
                'mrn' => $this->patient->mrn,
                'name' => $this->patient->name,
            ] : [
                'id' => $this->patient_id,
                'mrn' => null,
                'name' => $this->patient_name,
            ],
            'blood_group' => $this->blood_group,
            'units_needed' => (int) $this->units_needed,
            'urgency_level' => $this->urgency_level,
            'status' => $this->status,
            'required_by' => $this->required_by ? $this->required_by->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
