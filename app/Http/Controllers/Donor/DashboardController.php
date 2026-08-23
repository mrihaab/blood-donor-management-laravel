<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Appointment;
use App\Models\Donation;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $donor = $user->donor;

        // If donor profile doesn't exist unexpectedly, resolve blood group dynamically
        if (!$donor) {
            $bloodGroup = BloodGroup::first() ?? BloodGroup::firstOrCreate(
                ['name' => 'A+'],
                ['description' => 'A positive']
            );

            $donor = $user->donor()->create([
                'blood_group_id' => $bloodGroup->id,
                'gender' => 'other',
                'date_of_birth' => '2000-01-01',
                'contact_number' => $user->email,
                'address' => 'Please update your address',
                'city' => 'Unknown',
                'state' => 'Unknown',
                'zip_code' => '00000',
                'is_available' => true,
            ]);
        }

        // Get donor's stats strictly from DB
        $totalDonations = Donation::where('donor_id', optional($donor)->id)->count();
        $latestDonation = Donation::where('donor_id', optional($donor)->id)->latest()->first();
        $upcomingAppointments = Appointment::where('donor_id', optional($donor)->id)
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->count();
        
        $recentAppointments = Appointment::where('donor_id', optional($donor)->id)
            ->latest()
            ->limit(5)
            ->get();

        $bloodGroupName = optional($donor->bloodGroup)->name ?? 'Not Set';
        $bloodRequests = BloodRequest::when($bloodGroupName !== 'Not Set', function($query) use ($bloodGroupName) {
            return $query->where('blood_group', $bloodGroupName);
        })->count();

        // Single source of truth from Donor model methods
        $nextEligibleDate = $donor->getNextEligibleDate()->format('Y-m-d');
        $isEligible = $donor->isEligibleToDonate();

        return view('donor.dashboard', [
            'totalDonations' => $totalDonations,
            'latestDonation' => $latestDonation,
            'nextEligibleDate' => $nextEligibleDate,
            'isEligible' => $isEligible,
            'upcomingAppointments' => $upcomingAppointments,
            'recentAppointments' => $recentAppointments,
            'bloodRequests' => $bloodRequests,
            'bloodGroup' => $bloodGroupName,
            'donor' => $donor,
        ]);
    }
    
    public function history()
    {
        $user = auth()->user();
        $donor = $user->donor;
        
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
