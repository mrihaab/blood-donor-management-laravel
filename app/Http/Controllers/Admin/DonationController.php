<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDonationRequest;
use App\Models\BloodGroup;
use App\Models\Donation;
use App\Models\Donor;
use App\Services\DonationService;
use App\Services\DonorEligibilityService;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    protected DonationService $donationService;
    protected DonorEligibilityService $eligibilityService;

    public function __construct(DonationService $donationService, DonorEligibilityService $eligibilityService)
    {
        $this->donationService = $donationService;
        $this->eligibilityService = $eligibilityService;
    }

    public function index()
    {
        $donations = Donation::with(['donor.user', 'bloodGroup'])
            ->latest()
            ->get();

        return view('admin.donations.index', compact('donations'));
    }

    public function create()
    {
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        $bloodGroups = BloodGroup::all();

        return view('admin.donations.create', compact('donors', 'bloodGroups'));
    }

    public function store(StoreDonationRequest $request)
    {
        $donor = Donor::findOrFail($request->input('donor_id'));

        try {
            $this->donationService->recordDonation($donor, $request->validated(), auth()->user());
            return redirect()->route('admin.donations.index')
                ->with('success', 'Donation recorded and added to inventory.');
        } catch (\Throwable $e) {
            return back()->withErrors(['donation_date' => $e->getMessage()]);
        }
    }

    public function checkEligibility(Request $request)
    {
        $donorId = $request->input('donor_id');
        $donor = Donor::findOrFail($donorId);
        $eligibility = $this->eligibilityService->checkEligibility($donor);

        return response()->json($eligibility);
    }
}
