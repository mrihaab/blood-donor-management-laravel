<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hospital\StoreHospitalPatientRequest;
use App\Http\Requests\Api\V1\Hospital\UpdateHospitalPatientRequest;
use App\Http\Resources\Api\V1\Hospital\PatientDetailResource;
use App\Http\Resources\Api\V1\Hospital\PatientListResource;
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
            "status" => ["nullable", "string", "in:active,discharged,archived"],
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:50"],
        ]);

        $query = Patient::query()
            ->where("hospital_id", $hospitalId)
            ->with("bloodGroup");

        $search = $request->input("search");
        if ($search !== null) {
            $search = trim($search);
        }
        if ($search !== null && $search !== "") {
            $escapedSearch = str_replace(
                ["!", "%", "_"],
                ["!!", "!%", "!_"],
                $search
            );
            $searchTerm = "%{$escapedSearch}%";
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw("name LIKE ? ESCAPE '!'", [$searchTerm])
                  ->orWhereRaw("mrn LIKE ? ESCAPE '!'", [$searchTerm]);
            });
        }

        if ($request->filled("status")) {
            $query->where("status", $request->input("status"));
        }

        $perPage = (int) $request->input("per_page", 15);
        $patients = $query->orderByDesc("created_at")
            ->orderByDesc("id")
            ->paginate($perPage);

        return response()->json([
            "data" => PatientListResource::collection($patients),
            "meta" => [
                "current_page" => $patients->currentPage(),
                "last_page" => $patients->lastPage(),
                "per_page" => $patients->perPage(),
                "total" => $patients->total(),
            ],
        ]);
    }

    /**
     * Store a newly created patient for the authenticated hospital.
     */
    public function store(StoreHospitalPatientRequest $request): JsonResponse
    {
        $hospitalId = $request->user()->hospital_id;
        $validated = $request->validated();
        $validated["hospital_id"] = $hospitalId;
        $validated["status"] = "active";

        $patient = Patient::create($validated);
        $patient->load([
            "bloodGroup",
            "bloodRequests" => function ($query) use ($hospitalId) {
                $query->where("hospital_id", $hospitalId)
                      ->orderByDesc("created_at")
                      ->orderByDesc("id")
                      ->limit(10);
            },
        ]);

        try {
            app(NotificationService::class)->notifyAdminPatientRegistered(
                $patient,
                $request->user()->hospital->name ?: "Hospital"
            );
        } catch (\Throwable $e) {
            Log::warning("Hospital patient registration notification failed.", [
                "exception" => $e::class,
            ]);
        }

        return response()->json([
            "data" => new PatientDetailResource($patient),
        ], 201);
    }

    /**
     * Display the specified patient detail.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $hospitalId = $request->user()->hospital_id;

        $patient = Patient::query()
            ->where("hospital_id", $hospitalId)
            ->findOrFail($id);

        $patient->load([
            "bloodGroup",
            "bloodRequests" => function ($query) use ($hospitalId) {
                $query->where("hospital_id", $hospitalId)
                      ->orderByDesc("created_at")
                      ->orderByDesc("id")
                      ->limit(10);
            },
        ]);

        return response()->json([
            "data" => new PatientDetailResource($patient),
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(UpdateHospitalPatientRequest $request, $id): JsonResponse
    {
        $hospitalId = $request->user()->hospital_id;

        $patient = Patient::query()
            ->where("hospital_id", $hospitalId)
            ->findOrFail($id);

        $patient->update($request->validated());

        $patient->load([
            "bloodGroup",
            "bloodRequests" => function ($query) use ($hospitalId) {
                $query->where("hospital_id", $hospitalId)
                      ->orderByDesc("created_at")
                      ->orderByDesc("id")
                      ->limit(10);
            },
        ]);

        return response()->json([
            "data" => new PatientDetailResource($patient),
        ]);
    }
}
