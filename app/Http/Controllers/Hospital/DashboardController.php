<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $hospital = $user->hospital ?? Hospital::find($user->hospital_id);

        if (!$hospital) {
            abort(403, 'Hospital association required to access Hospital Portal.');
        }

        $pendingCount = BloodRequest::where('hospital_id', $hospital->id)->where('status', 'pending')->count();
        $approvedCount = BloodRequest::where('hospital_id', $hospital->id)->where('status', 'approved')->count();
        $dispensedCount = BloodRequest::where('hospital_id', $hospital->id)->where('status', 'dispensed')->count();

        $recentRequests = BloodRequest::where('hospital_id', $hospital->id)
            ->with(['patient', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return view('hospital.dashboard', compact('hospital', 'pendingCount', 'approvedCount', 'dispensedCount', 'recentRequests'));
    }
}
