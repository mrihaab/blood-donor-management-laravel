<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Hospital\BloodGroupResource;
use App\Models\BloodGroup;
use Illuminate\Http\JsonResponse;

class HospitalLookupController extends Controller
{
    /**
     * Get deterministic list of canonical blood groups.
     */
    public function bloodGroups(): JsonResponse
    {
        $canonicalOrder = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        $groups = BloodGroup::select(['id', 'name'])
            ->whereIn('name', $canonicalOrder)
            ->get()
            ->sortBy(function ($group) use ($canonicalOrder) {
                return array_search($group->name, $canonicalOrder);
            })
            ->values();

        return response()->json([
            'data' => BloodGroupResource::collection($groups),
        ]);
    }
}
