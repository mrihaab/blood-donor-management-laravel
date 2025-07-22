<?php

namespace App\Http\Controllers;

use App\Models\BloodRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class BloodRequestController extends Controller
{
    /**
     * Show all requests for the logged-in donor.
     */
    public function index(Request $request)
    {
        $requests = BloodRequest::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('donor.blood-requests.index', [
            'requests' => $requests,
        ]);
    }

    /**
     * Show blood request creation form.
     */
    public function create()
    {
        return view('donor.blood-requests.create');
    }

    /**
     * Store a new blood request by the donor.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'blood_group' => 'required|string',
            'patient_name' => 'required|string|max:255',
            'hospital' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'units_needed' => 'required|integer|min:1|max:4',
            'reason' => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        BloodRequest::create($data);

        return redirect()->route('donor.blood_requests.index')
            ->with('success', 'Blood request submitted successfully.');
    }
}
