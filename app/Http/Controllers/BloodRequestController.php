<?php

namespace App\Http\Controllers;

use App\Http\Requests\Donor\StoreBloodRequest;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Services\BloodRequestService;

class BloodRequestController extends Controller
{
    protected BloodRequestService $bloodRequestService;

    public function __construct(BloodRequestService $bloodRequestService)
    {
        $this->bloodRequestService = $bloodRequestService;
    }

    public function index()
    {
        $user = auth()->user();
        $bloodRequests = BloodRequest::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('donor.blood-requests.index', [
            'requests' => $bloodRequests,
            'bloodRequests' => $bloodRequests,
        ]);
    }

    public function create()
    {
        $bloodGroups = BloodGroup::all();
        return view('donor.blood-requests.create', compact('bloodGroups'));
    }

    public function store(StoreBloodRequest $request)
    {
        $this->bloodRequestService->createRequest($request->validated(), auth()->user());

        return redirect()->route('donor.blood_requests.index')
            ->with('success', 'Emergency blood request submitted successfully. Matching donors are being notified.');
    }
}
