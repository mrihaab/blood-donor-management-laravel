<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\HospitalPatientRequest;
use App\Models\BloodGroup;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);
        $user = Auth::user();
        $hospitalId = $user->hospital_id;

        $query = Patient::where('hospital_id', $hospitalId)->with('bloodGroup');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mrn', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15);

        return view('hospital.patients.index', compact('patients'));
    }

    public function create()
    {
        $this->authorize('create', Patient::class);
        $bloodGroups = BloodGroup::all();
        return view('hospital.patients.create', compact('bloodGroups'));
    }

    public function store(HospitalPatientRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $validated['hospital_id'] = $user->hospital_id;

        $patient = Patient::create($validated);

        return redirect()->route('hospital.patients.show', $patient->id)
            ->with('success', 'Patient record created successfully.');
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);
        $patient->load(['bloodGroup', 'bloodRequests']);
        return view('hospital.patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        $this->authorize('update', $patient);
        $bloodGroups = BloodGroup::all();
        return view('hospital.patients.edit', compact('patient', 'bloodGroups'));
    }

    public function update(HospitalPatientRequest $request, Patient $patient)
    {
        $this->authorize('update', $patient);
        $validated = $request->validated();
        // Server identity protection: hospital_id cannot be mutated
        unset($validated['hospital_id']);

        $patient->update($validated);

        return redirect()->route('hospital.patients.show', $patient->id)
            ->with('success', 'Patient record updated successfully.');
    }
}
