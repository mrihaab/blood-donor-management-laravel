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
        $base = (new PatientResource($this->resource))->toArray($request);
        $base["blood_requests"] = DashboardRequisitionResource::collection($this->whenLoaded("bloodRequests"));
        return $base;
    }
}
