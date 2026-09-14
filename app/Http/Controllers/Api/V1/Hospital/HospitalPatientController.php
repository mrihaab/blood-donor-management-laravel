<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hospital\HospitalPatientApiRequest;
use App\Http\Resources\Api\V1\Hospital\PatientDetailResource;
use App\Http\Resources\Api\V1\Hospital\PatientResource;
use App\Models\Patient;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HospitalPatientController extends Controller
{
    /**
     * Display a paginated listing of the hospital patients.
     */
    public function index(Request $request): JsonResponse
    {
        $hospitalId = $request->user()->hospital_id;

        $request->validate([
            "search" => ["nullable", "string", "max:100"],
            "q" => ["nullable", "string", "max:100"],
            "status" => ["nullable", "string", "in:active,discharged,archived"],
            "blood_group_id" => ["nullable", "integer", "exists:blood_groups,id"],
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:50"],
        ]);

        $query = Patient::query()
            ->where("hospital_id", $hospitalId)
            ->with("bloodGroup");

        $search = $request->input("search", $request->input("q"));
        if ($search !== null && $search !== "") {
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                  ->orWhere("mrn", "like", "%{$search}%");
            });
        }

        if ($request->filled("status")) {
            $query->where("status", $request->input("status"));
        }

        if ($request->filled("blood_group_id")) {
            $query->where("blood_group_id", $request->input("blood_group_id"));
        }

        $perPage = (int) $request->input("per_page", 15);
        $patients = $query->orderByDesc("created_at")
            ->orderByDesc("id")
            ->paginate($perPage);

        return response()->json([
            "data" => PatientResource::collection($patients),
            "meta" => [
                "current_page" => $patients->currentPage(),
                "last_page" => $patients->lastPage(),
                "per_page" => $patients->perPage(),
                "total" => $patients->total(),
            ],
            "links" => [
                "first" => $patients->url(1),
                "last" => $patients->url($patients->lastPage()),
                "prev" => $patients->previousPageUrl(),
                "next" => $patients->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created patient for the authenticated hospital.
     */
    public function store(HospitalPatientApiRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated["hospital_id"] = $user->hospital_id;

        if (empty($validated["status"])) {
            $validated["status"] = "active";
        }

        $patient = Patient::create($validated);
        $patient->load("bloodGroup");

        // Dispatch real-time admin alert notification
        try {
            app(NotificationService::class)->notifyAdminPatientRegistered(
                $patient,
                $user->hospital->name ?? "Hospital"
            );
        } catch (\Throwable $e) {
            Log::warning("API Patient Registration Admin Notification skipped: " . $e->getMessage());
        }

        return response()->json([
            "data" => new PatientResource($patient),
        ], 201);
    }

    /**
     * Display the specified patient detail.
     * MANDATORY SECURITY RULE: Must use scoped query with findOrFail to return non-enumerating 404 for cross-hospital requests.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $patient = Patient::query()
            ->where("hospital_id", $request->user()->hospital_id)
            ->findOrFail($id);

        $patient->load([
            "bloodGroup",
            "bloodRequests" => function ($query) {
                $query->orderByDesc("created_at")->orderByDesc("id")->limit(10);
            },
        ]);

        return response()->json([
            "data" => new PatientDetailResource($patient),
        ]);
    }

    /**
     * Update the specified patient.
     * MANDATORY SECURITY RULE: Must use scoped query with findOrFail to return non-enumerating 404 for cross-hospital requests.
     */
    public function update(HospitalPatientApiRequest $request, int $id): JsonResponse
    {
        $patient = Patient::query()
            ->where("hospital_id", $request->user()->hospital_id)
            ->findOrFail($id);

        $validated = $request->validated();
        unset($validated["hospital_id"]);

        $patient->update($validated);

        return response()->json([
            "data" => new PatientResource($patient->fresh(["bloodGroup"])),
        ]);
    }
}
