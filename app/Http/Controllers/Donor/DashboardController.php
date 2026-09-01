<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Appointment;
use App\Models\Donation;
use App\Services\DonorEligibilityService;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $donor = $user ? $user->donor : null;

        // If donor profile doesn't exist unexpectedly, resolve blood group dynamically
        if ($user && !$donor) {
            $bloodGroup = BloodGroup::first() ?? BloodGroup::firstOrCreate(
                ['name' => 'A+'],
                ['description' => 'A positive']
            );

            $donor = $user->donor()->create([
                'blood_group_id' => $bloodGroup->id,
                'gender' => 'other',
                'date_of_birth' => '2000-01-01',
                'contact_number' => $user->email ?? 'Not Provided',
                'address' => 'Please update your address',
                'city' => 'Unknown',
                'state' => 'Unknown',
                'zip_code' => '00000',
                'is_available' => true,
                'status' => 'active',
            ]);
        }

        // Get donor's stats strictly from DB
        $totalDonations = Donation::where('donor_id', optional($donor)->id)->count();
        $latestDonation = Donation::where('donor_id', optional($donor)->id)->latest()->first();
        
        $upcomingAppointment = Appointment::where('donor_id', optional($donor)->id)
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->first();

        $upcomingAppointments = Appointment::where('donor_id', optional($donor)->id)
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->count();
        
        $recentAppointments = Appointment::where('donor_id', optional($donor)->id)
            ->latest()
            ->limit(5)
            ->get();

        $bloodGroupName = optional(optional($donor)->bloodGroup)->name ?? 'Not Set';
        $bloodRequests = BloodRequest::when($bloodGroupName !== 'Not Set', function($query) use ($bloodGroupName) {
            return $query->where('blood_group', $bloodGroupName);
        })->count();

        // Calculate eligibility array safely via DonorEligibilityService
        $eligibilityService = app(DonorEligibilityService::class);
        $eligibility = $donor ? $eligibilityService->checkEligibility($donor) : [
            'eligible' => true,
            'reasons' => [],
            'is_deferred' => false,
            'deferral_type' => null,
            'active_deferral' => null,
            'days_since_last' => null,
            'days_until_eligible' => 0,
            'days_remaining' => 0,
            'last_donation_date' => null,
            'next_eligible_date' => now(),
        ];

        $nextEligibleDate = isset($eligibility['next_eligible_date']) && $eligibility['next_eligible_date']
            ? $eligibility['next_eligible_date']->format('Y-m-d')
            : now()->format('Y-m-d');
        $isEligible = $eligibility['eligible'] ?? true;

        $activeEmergencyRequests = BloodRequest::where('urgency_level', 'emergency')
            ->whereIn('status', ['pending', 'approved'])
            ->when($bloodGroupName !== 'Not Set', function($query) use ($bloodGroupName) {
                return $query->where('blood_group', $bloodGroupName);
            })
            ->latest()
            ->get();

        return view('donor.dashboard', [
            'totalDonations' => $totalDonations,
            'latestDonation' => $latestDonation,
            'nextEligibleDate' => $nextEligibleDate,
            'isEligible' => $isEligible,
            'eligibility' => $eligibility,
            'upcomingAppointment' => $upcomingAppointment,
            'upcomingAppointments' => $upcomingAppointments,
            'recentAppointments' => $recentAppointments,
            'bloodRequests' => $bloodRequests,
            'bloodGroup' => $bloodGroupName,
            'donor' => $donor,
            'activeEmergencyRequests' => $activeEmergencyRequests,
        ]);
    }
    
    public function history()
    {
        $user = auth()->user();
        $donor = $user ? $user->donor : null;
        
        if (!$donor) {
            return redirect()->route('donor.dashboard')->with('error', 'Please complete your donor profile first.');
        }
        
        $donations = Donation::where('donor_id', $donor->id)
            ->with('bloodGroup')
            ->latest()
            ->get();

        return view('donor.history', [
            'donations' => $donations,
            'donor' => $donor
        ]);
    }
}
