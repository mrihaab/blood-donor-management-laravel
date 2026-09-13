<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Hospital\HospitalDashboardResource;
use App\Models\BloodRequest;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HospitalDashboardController extends Controller
{
    /**
     * Get operational dashboard overview for the authenticated hospital user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $hospital = $user->hospital;

        if (!$hospital) {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        $hospitalId = $hospital->id;

        $kpis = [
            'total_patients' => Patient::where('hospital_id', $hospitalId)->count(),
            'total_requisitions' => BloodRequest::where('hospital_id', $hospitalId)->count(),
            'pending_requisitions' => BloodRequest::where('hospital_id', $hospitalId)->where('status', 'pending')->count(),
            'approved_requisitions' => BloodRequest::where('hospital_id', $hospitalId)->where('status', 'approved')->count(),
            'dispensed_requisitions' => BloodRequest::where('hospital_id', $hospitalId)->where('status', 'dispensed')->count(),
        ];

        $recentRequisitions = BloodRequest::where('hospital_id', $hospitalId)
            ->with('patient')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => new HospitalDashboardResource($hospital, $kpis, $recentRequisitions),
        ]);
    }
}
