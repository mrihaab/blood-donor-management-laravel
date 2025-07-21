<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\BloodGroup;
use App\Models\BloodInventory;
use Illuminate\Http\Request;
class DonationController extends Controller
{
    /**
     * Display a listing of donations.
     */
    public function index()
    {
        $donations = Donation::with(['donor.user', 'bloodGroup'])
            ->orderBy('donation_date', 'desc')
            ->get();

        return view('admin.donations.index', [
            'donations' => $donations
        ]);
    }

    /**
     * Show the form for creating a new donation.
     */
    public function create()
    {
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        
        return view('admin.donations.create', [
            'donors' => $donors
        ]);
    }

    /**
     * Store a newly created donation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'units' => 'required|integer|min:1|max:2',
            'donation_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $donor = Donor::with('bloodGroup')->findOrFail($request->donor_id);
            
            // Create the donation record
            $donation = Donation::create([
                'donor_id' => $request->donor_id,
                'blood_group_id' => $donor->blood_group_id,
                'blood_group' => $donor->bloodGroup->name,
                'quantity' => $request->units * 450, // 450ml per unit
                'units' => $request->units,
                'donation_date' => $request->donation_date,
                'status' => 'completed',
                'notes' => $request->notes,
                'collection_center' => 'Main Blood Bank',
                'created_by' => auth()->id()
            ]);
            
            // Find blood group ID
            $bloodGroup = BloodGroup::where('name', $donor->bloodGroup->name)->first();
            if (!$bloodGroup) {
                throw new \Exception('Invalid blood group: ' . $donor->blood_group);
            }
            
            // Create inventory record for each unit donated
            for ($i = 0; $i < $request->units; $i++) {
                BloodInventory::create([
                    'blood_group_id' => $bloodGroup->id,
                    'quantity' => 450, // Standard blood unit is 450ml
                    'collection_date' => $request->donation_date,
                    'expiry_date' => now()->parse($request->donation_date)->addDays(42), // Blood expires in 42 days
                    'location' => 'Main Blood Bank',
                    'donor_id' => $donor->id,
                    'units_available' => 1,
                    'units_requested' => 0,
                    'status' => 'available'
                ]);
            }
            
            // Update donor's last donation date
            $donor->update([
                'last_donation_date' => $request->donation_date
            ]);

            return redirect()->route('admin.donations.index')
                ->with('success', 'Donation recorded successfully and inventory updated.');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified donation.
     */
    public function show(string $id)
    {
        $donation = Donation::with(['donor.user', 'donor.bloodGroup'])
            ->findOrFail($id);

        return view('admin.donations.show', [
            'donation' => $donation
        ]);
    }

    /**
     * Show the form for editing the specified donation.
     */
    public function edit(string $id)
    {
        $donation = Donation::with(['donor.user'])->findOrFail($id);
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        
        return view('admin.donations.edit', [
            'donation' => $donation,
            'donors' => $donors
        ]);
    }

    /**
     * Update the specified donation.
     */
    public function update(Request $request, string $id)
    {
        $donation = Donation::findOrFail($id);
        
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'units' => 'required|integer|min:1|max:2',
            'donation_date' => 'required|date',
            'status' => 'required|in:completed,dispensed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $donation->update([
            'donor_id' => $request->donor_id,
            'units' => $request->units,
            'quantity' => $request->units * 450, // Update quantity based on units
            'donation_date' => $request->donation_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation updated successfully.');
    }

    /**
     * Remove the specified donation.
     */
    public function destroy(string $id)
    {
        $donation = Donation::findOrFail($id);
        $donation->delete();

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation deleted successfully.');
    }

    /**
     * Check donor eligibility
     */
    public function checkEligibility(Request $request)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id'
        ]);

        $donor = Donor::findOrFail($request->donor_id);
        
        // Check last donation date (56 days WHO guidelines)
        $lastDonation = Donation::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->latest('donation_date')
            ->first();
            
        $eligible = true;
        $message = 'Donor is eligible for donation.';
        
        if ($lastDonation) {
            $daysSinceLastDonation = now()->diffInDays($lastDonation->donation_date);
            if ($daysSinceLastDonation < 56) {
                $eligible = false;
                $message = 'Donor must wait ' . (56 - $daysSinceLastDonation) . ' more days before next donation.';
            }
        }

        return response()->json([
            'eligible' => $eligible,
            'message' => $message,
            'last_donation' => $lastDonation ? $lastDonation->donation_date : null
        ]);
    }
}
