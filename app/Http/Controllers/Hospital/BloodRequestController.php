<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\HospitalRequisitionRequest;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Patient;
use App\Services\BloodRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BloodRequestController extends Controller
{
    protected BloodRequestService $bloodRequestService;

    public function __construct(BloodRequestService $bloodRequestService)
    {
        $this->bloodRequestService = $bloodRequestService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', BloodRequest::class);
        $user = Auth::user();
        $hospitalId = $user->hospital_id;

        $query = BloodRequest::where('hospital_id', $hospitalId)
            ->with(['patient', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $requests = $query->latest()->paginate(15);

        return view('hospital.requests.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize('create', BloodRequest::class);
        $user = Auth::user();

        $patients = Patient::where('hospital_id', $user->hospital_id)->get();
        $bloodGroups = BloodGroup::all();

        return view('hospital.requests.create', compact('patients', 'bloodGroups'));
    }

    public function store(HospitalRequisitionRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $patient = Patient::findOrFail($validated['patient_id']);

        // Strictly force server-side hospital identity
        $payload = array_merge($validated, [
            'hospital_id' => $user->hospital_id,
            'hospital' => $user->hospital->name ?? 'Hospital Center',
            'patient_name' => $patient->name,
            'city' => $user->hospital->city ?? 'Metropolis',
        ]);

        $bloodRequest = $this->bloodRequestService->createRequest($payload, $user);

        return redirect()->route('hospital.requests.show', $bloodRequest->id)
            ->with('success', 'Blood requisition submitted successfully.');
    }

    public function show(BloodRequest $bloodRequest)
    {
        $this->authorize('view', $bloodRequest);
        $bloodRequest->load(['patient', 'hospital', 'user']);

        return view('hospital.requests.show', compact('bloodRequest'));
    }
}
