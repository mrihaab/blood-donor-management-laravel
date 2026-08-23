<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CertifyReturnedUnitRequest;
use App\Models\BloodUnit;
use App\Models\Transfusion;
use App\Services\TransfusionService;
use Illuminate\Http\Request;

class TransfusionAdminController extends Controller
{
    protected TransfusionService $transfusionService;

    public function __construct(TransfusionService $transfusionService)
    {
        $this->transfusionService = $transfusionService;
    }

    public function index(Request $request)
    {
        $query = Transfusion::with(['bloodRequest', 'patient.bloodGroup', 'hospital', 'administeredBy', 'transfusionUnits.bloodUnit', 'reactions']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('mrn', 'like', "%{$search}%");
            });
        }

        $transfusions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.transfusions.index', compact('transfusions'));
    }

    public function show(Transfusion $transfusion)
    {
        $transfusion->load([
            'bloodRequest',
            'patient.bloodGroup',
            'hospital',
            'administeredBy',
            'transfusionUnits.bloodUnit.donor.bloodGroup',
            'transfusionUnits.bloodUnit.component',
            'transfusionUnits.bloodUnit.transactions.user',
            'reactions.reportedBy',
            'reactions.bloodUnit',
        ]);

        return view('admin.transfusions.show', compact('transfusion'));
    }

    public function certifyReturnedUnit(CertifyReturnedUnitRequest $request, BloodUnit $unit)
    {
        $this->transfusionService->certifyReturnedUnit($unit, $request->validated(), auth()->user());

        return redirect()->back()->with('success', "Quarantine inspection processed for Unit #{$unit->unit_number}.");
    }
}
