<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hospital\StoreHospitalRequisitionRequest;
use App\Http\Resources\Api\V1\Hospital\HospitalRequisitionDetailResource;
use App\Http\Resources\Api\V1\Hospital\HospitalRequisitionListItemResource;
use App\Models\BloodRequest;
use App\Models\Patient;
use App\Services\BloodRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HospitalRequisitionController extends Controller
{
    protected BloodRequestService $bloodRequestService;

    public function __construct(BloodRequestService $bloodRequestService)
    {
        $this->bloodRequestService = $bloodRequestService;
    }

    /**
     * List hospital-scoped requisitions with optional status filter, search, and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $hospitalId = $user->hospital_id;

        $query = BloodRequest::query()
            ->where('hospital_id', $hospitalId)
            ->with('patient');

        // Status filtering (authoritative domain status vocabulary)
        if ($request->filled('status')) {
            $status = trim((string) $request->input('status'));
            if (in_array($status, ['pending', 'approved', 'dispensed', 'rejected'], true)) {
                $query->where('status', $status);
            } else {
                // Invalid status query returns empty result or fails gracefully
                $query->whereRaw('1 = 0');
            }
        }

        // Text search across patient_name and patient MRN
        if ($request->filled('search')) {
            $search = mb_substr(trim((string) $request->input('search')), 0, 100);
            if ($search !== '') {
                $escapedSearch = str_replace(
                    ['!', '%', '_'],
                    ['!!', '!%', '!_'],
                    $search
                );
                $searchTerm = '%' . $escapedSearch . '%';

                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw("patient_name LIKE ? ESCAPE '!'", [$searchTerm])
                      ->orWhereHas('patient', function ($pq) use ($searchTerm) {
                          $pq->whereRaw("mrn LIKE ? ESCAPE '!'", [$searchTerm]);
                      });
                });
            }
        }

        // Pagination bounds
        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 1) {
            $perPage = 15;
        } elseif ($perPage > 50) {
            $perPage = 50;
        }

        $page = (int) $request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        $requisitions = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return HospitalRequisitionListItemResource::collection($requisitions);
    }

    /**
     * Display a specific hospital-scoped requisition.
     */
    public function show(Request $request, mixed $id): JsonResponse
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            return response()->json([
                'message' => 'The requested resource was not found.',
            ], 404);
        }

        $user = $request->user();
        $hospitalId = $user->hospital_id;

        $requisition = BloodRequest::query()
            ->where('hospital_id', $hospitalId)
            ->where('id', (int) $id)
            ->with('patient')
            ->first();

        if (!$requisition) {
            return response()->json([
                'message' => 'The requested resource was not found.',
            ], 404);
        }

        return (new HospitalRequisitionDetailResource($requisition))->response();
    }

    /**
     * Store a new blood requisition for the authenticated hospital.
     */
    public function store(StoreHospitalRequisitionRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $patient = Patient::query()
            ->where('hospital_id', $user->hospital_id)
            ->where('id', $validated['patient_id'])
            ->firstOrFail();

        $payload = [
            'hospital_id'     => $user->hospital_id,
            'hospital'        => $user->hospital->name ?? 'Hospital Center',
            'patient_id'      => $patient->id,
            'patient_name'    => $patient->name,
            'blood_group'     => $validated['blood_group'],
            'units_needed'    => (int) $validated['units_needed'],
            'urgency'         => $validated['urgency_level'],
            'city'            => $user->hospital->city ?? 'Metropolis',
            'reason'          => $validated['reason'] ?? null,
            'required_by'     => $validated['required_by'] ?? null,
            'ward_name'       => $validated['ward_name'] ?? null,
            'room_number'     => $validated['room_number'] ?? null,
            'bed_number'      => $validated['bed_number'] ?? null,
            'attendant_name'  => $validated['attendant_name'] ?? null,
            'attendant_phone' => $validated['attendant_phone'] ?? null,
        ];

        $requisition = $this->bloodRequestService->createRequest($payload, $user);
        $requisition->load('patient');

        return (new HospitalRequisitionDetailResource($requisition))
            ->response()
            ->setStatusCode(201);
    }
}
