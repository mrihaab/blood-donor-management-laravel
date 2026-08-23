<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmergencyRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', BloodRequest::class);

        $query = BloodRequest::with(['patient', 'hospitalEntity', 'user']);

        // Filters
        if ($request->filled('urgency')) {
            $query->where('urgency_level', $request->input('urgency'));
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->input('blood_group'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->input('hospital_id'));
        }

        // Server-Side Priority Sorting:
        // 1. emergency -> urgent -> routine
        // 2. required_by ASC (earliest required first)
        // 3. created_at ASC (oldest request first)
        $requests = $query->orderByRaw("
            CASE 
                WHEN urgency_level = 'emergency' THEN 1
                WHEN urgency_level = 'urgent' THEN 2
                ELSE 3
            END ASC
        ")
        ->orderBy('required_by', 'asc')
        ->orderBy('created_at', 'asc')
        ->paginate(15);

        $hospitals = Hospital::all();
        $bloodGroups = BloodGroup::all();

        return view('admin.emergency-requests.index', compact('requests', 'hospitals', 'bloodGroups'));
    }
}
