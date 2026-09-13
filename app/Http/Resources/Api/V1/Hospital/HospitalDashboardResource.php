<?php

namespace App\Http\Resources\Api\V1\Hospital;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalDashboardResource extends JsonResource
{
    protected array $kpis;
    protected mixed $recentRequisitions;

    public function __construct($hospital, array $kpis, $recentRequisitions)
    {
        parent::__construct($hospital);
        $this->kpis = $kpis;
        $this->recentRequisitions = $recentRequisitions;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hospital' => [
                'id' => $this->id,
                'name' => $this->name,
                'license_number' => $this->license_number,
                'city' => $this->city,
                'status' => $this->status,
            ],
            'kpis' => $this->kpis,
            'recent_requisitions' => DashboardRequisitionResource::collection($this->recentRequisitions),
        ];
    }
}
