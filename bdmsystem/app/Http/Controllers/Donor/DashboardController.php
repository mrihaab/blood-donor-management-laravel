<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BloodRequest;
use App\Models\Appointment;
use App\Models\Donation;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $donor = $user->donor;

        // If donor profile doesn't exist, create a basic one with required fields
        if (!$donor) {
            $donor = $user->donor()->create([
                'blood_group_id' => 1, // Default to first blood group, user can update later
                'gender' => 'male', // Default, user can update
                'date_of_birth' => '1990-01-01', // Default, user can update
                'contact_number' => $user->email, // Temporary, user can update
                'address' => 'Please update your address',
                'city' => 'Unknown',
                'state' => 'Unknown',
                'zip_code' => '00000',
            ]);
        }

        // Get donor's stats
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

        // Calculate next eligible donation date (+56 days as per WHO guidelines)
        $nextEligibleDate = $latestDonation
            ? $latestDonation->donation_date
                ? date('Y-m-d', strtotime($latestDonation->donation_date . ' + 56 days'))
                : date('Y-m-d', strtotime($latestDonation->created_at . ' + 56 days'))
            : now()->toDateString();

        return view('donor.dashboard', [
            'totalDonations' => $totalDonations,
            'latestDonation' => $latestDonation,
            'nextEligibleDate' => $nextEligibleDate,
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
