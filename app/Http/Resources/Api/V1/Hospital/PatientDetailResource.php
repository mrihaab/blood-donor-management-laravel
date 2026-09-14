<?php

namespace App\Http\Resources\Api\V1\Hospital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dob = $this->date_of_birth;
        $dobString = null;
        if ($dob) {
            $dobString = is_string($dob) ? substr($dob, 0, 10) : $dob->format("Y-m-d");
        }

        return [
            "id" => $this->id,
            "mrn" => $this->mrn,
            "name" => $this->name,
            "gender" => $this->gender,
            "date_of_birth" => $dobString,
            "contact_number" => $this->contact_number,
            "status" => $this->status,
            "ward_name" => $this->ward_name,
            "room_number" => $this->room_number,
            "bed_number" => $this->bed_number,
            "blood_group" => $this->whenLoaded("bloodGroup", function () {
                return $this->bloodGroup ? [
                    "id" => $this->bloodGroup->id,
                    "name" => $this->bloodGroup->name,
                ] : null;
            }),
            "created_at" => $this->created_at?->toISOString(),
            "updated_at" => $this->updated_at?->toISOString(),
            "blood_requests" => PatientRequisitionSummaryResource::collection($this->whenLoaded("bloodRequests")),
        ];
    }
}
