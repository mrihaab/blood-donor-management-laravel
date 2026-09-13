<?php

namespace App\Http\Resources\Api\V1\Hospital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardRequisitionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_name' => $this->patient_name ?? optional($this->patient)->name,
            'blood_group' => $this->blood_group,
            'units_needed' => (int) $this->units_needed,
            'urgency_level' => $this->urgency_level,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
