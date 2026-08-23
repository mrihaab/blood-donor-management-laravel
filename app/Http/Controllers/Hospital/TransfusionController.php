<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransfusionReactionRequest;
use App\Http\Requests\StoreTransfusionRequest;
use App\Models\BloodRequest;
use App\Models\Patient;
use App\Models\Transfusion;
use App\Services\TransfusionService;
use Illuminate\Http\Request;

class TransfusionController extends Controller
{
    protected TransfusionService $transfusionService;

    public function __construct(TransfusionService $transfusionService)
    {
        $this->transfusionService = $transfusionService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Transfusion::class);

        $hospitalId = auth()->user()->hospital_id;

        $query = Transfusion::where('hospital_id', $hospitalId)
            ->with(['bloodRequest', 'patient.bloodGroup', 'transfusionUnits.bloodUnit', 'reactions']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfusions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('hospital.transfusions.index', compact('transfusions'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Transfusion::class);

        $hospitalId = auth()->user()->hospital_id;

        $approvedRequests = BloodRequest::where('hospital_id', $hospitalId)
            ->whereIn('status', ['approved', 'dispensed'])
            ->with('patient')
            ->get();

        $patients = Patient::where('hospital_id', $hospitalId)->get();

        return view('hospital.transfusions.create', compact('approvedRequests', 'patients'));
    }

    public function store(StoreTransfusionRequest $request)
    {
        $this->authorize('create', Transfusion::class);

        $hospitalId = auth()->user()->hospital_id;

        $bloodRequest = BloodRequest::where('id', $request->blood_request_id)
            ->where('hospital_id', $hospitalId)
            ->firstOrFail();

        $patient = Patient::where('id', $request->patient_id)
            ->where('hospital_id', $hospitalId)
            ->firstOrFail();

        $transfusion = $this->transfusionService->createTransfusion($bloodRequest, $patient, auth()->user(), $request->notes);

        return redirect()->route('hospital.transfusions.show', $transfusion->id)
            ->with('success', "Transfusion #TR-{$transfusion->id} scheduled successfully.");
    }

    public function show(Transfusion $transfusion)
    {
        $this->authorize('view', $transfusion);

        $transfusion->load([
            'bloodRequest',
            'patient.bloodGroup',
            'hospital',
            'administeredBy',
            'transfusionUnits.bloodUnit.donor.bloodGroup',
            'transfusionUnits.bloodUnit.component',
            'reactions.reportedBy',
        ]);

        return view('hospital.transfusions.show', compact('transfusion'));
    }

    public function issue(Request $request, Transfusion $transfusion)
    {
        $this->authorize('update', $transfusion);

        $request->validate([
            'unit_ids'   => ['required', 'array', 'min:1'],
            'unit_ids.*' => ['integer', 'exists:blood_units,id'],
            'notes'      => ['nullable', 'string'],
        ]);

        $this->transfusionService->issueUnits($transfusion, $request->unit_ids, auth()->user(), $request->notes);

        return redirect()->back()->with('success', 'Blood units issued for transfusion.');
    }

    public function start(Request $request, Transfusion $transfusion)
    {
        $this->authorize('update', $transfusion);

        $this->transfusionService->startTransfusion($transfusion, auth()->user(), $request->notes);

        return redirect()->back()->with('success', "Transfusion #TR-{$transfusion->id} started.");
    }

    public function complete(Request $request, Transfusion $transfusion)
    {
        $this->authorize('update', $transfusion);

        $this->transfusionService->completeTransfusion($transfusion, auth()->user(), $request->notes);

        return redirect()->back()->with('success', "Transfusion #TR-{$transfusion->id} completed successfully.");
    }

    public function stop(Request $request, Transfusion $transfusion)
    {
        $this->authorize('update', $transfusion);

        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $this->transfusionService->stopTransfusion($transfusion, auth()->user(), $request->reason);

        return redirect()->back()->with('warning', "Transfusion #TR-{$transfusion->id} stopped.");
    }

    public function recordReaction(StoreTransfusionReactionRequest $request, Transfusion $transfusion)
    {
        $this->authorize('recordReaction', $transfusion);

        $reaction = $this->transfusionService->recordReaction($transfusion, $request->validated(), auth()->user());

        return redirect()->back()->with('danger', "Transfusion reaction ({$reaction->severity}) recorded and alerted.");
    }
}
