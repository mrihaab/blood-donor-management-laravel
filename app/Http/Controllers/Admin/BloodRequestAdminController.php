<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\BloodInventory;
use App\Models\BloodGroup;
use App\Models\Notification;
use App\Models\Donor;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloodRequestAdminController extends Controller
{
    /**
     * Show all blood requests (with optional filters).
     */
    public function index(Request $request)
    {
        $query = BloodRequest::with('user'); // include user info for view

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->search . '%')
                  ->orWhere('reason', 'like', '%' . $request->search . '%')
                  ->orWhere('patient_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->get();

        return view('admin.blood-requests.index', [
            'requests' => $requests,
            'filters' => $request->only(['search', 'blood_group', 'status'])
        ]);
    }

    /**
     * Approve a blood request - just marks it as approved, doesn't dispense blood yet
     */
    public function approve($id)
    {
        try {
            $bloodRequest = BloodRequest::findOrFail($id);
            
            if ($bloodRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed.');
            }

            // Find blood group ID first
            $bloodGroup = BloodGroup::where('name', $bloodRequest->blood_group)->first();
            if (!$bloodGroup) {
                return back()->with('error', 'Invalid blood group.');
            }

            // Check blood inventory availability
            $totalAvailable = BloodInventory::where('blood_group_id', $bloodGroup->id)
                ->where('status', 'available')
                ->where('expiry_date', '>', now())
                ->sum('units_available');
            
            $unitsNeeded = $bloodRequest->units_needed ?? 1;
            if ($totalAvailable < $unitsNeeded) {
                return back()->with('error', "Insufficient blood units available. Available: {$totalAvailable}, Needed: {$unitsNeeded}");
            }

            // Just approve the request, don't dispense yet
            $bloodRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id()
            ]);

            // Create notification for the requester
            if ($bloodRequest->user) {
                Notification::create([
                    'user_id' => $bloodRequest->user_id,
                    'title' => 'Blood Request Approved',
                    'message' => "Your blood request for {$bloodRequest->blood_group} has been approved. Please contact the blood bank for collection details.",
                    'type' => 'success'
                ]);
            }

            return back()->with('success', 'Blood request approved successfully. You can now dispense blood from inventory.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve blood request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a blood request.
     */
    public function reject($id)
    {
        try {
            $bloodRequest = BloodRequest::findOrFail($id);
            
            if ($bloodRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed.');
            }

            $bloodRequest->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id()
            ]);

            // Create notification for the requester
            if ($bloodRequest->user) {
                Notification::create([
                    'user_id' => $bloodRequest->user_id,
                    'title' => 'Blood Request Rejected',
                    'message' => "Your blood request for {$bloodRequest->blood_group} has been rejected. Please contact the blood bank for more information.",
                    'type' => 'error'
                ]);
            }

            return back()->with('success', 'Blood request rejected successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject blood request: ' . $e->getMessage());
        }
    }

    /**
     * Assign donor to blood request.
     */
    public function assignDonor(Request $request, $id)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id'
        ]);

        try {
            DB::beginTransaction();

            $bloodRequest = BloodRequest::findOrFail($id);
            $donor = Donor::findOrFail($request->donor_id);

            // Check if donor blood group is compatible
            $donorBloodGroup = $donor->bloodGroup->name ?? null;
            if ($donorBloodGroup !== $bloodRequest->blood_group) {
                return back()->with('error', 'Donor blood group does not match the request.');
            }

            $bloodRequest->update([
                'assigned_donor_id' => $donor->id,
                'assigned_at' => now()
            ]);

            // Create notification for the donor
            if ($donor->user) {
                Notification::create([
                    'user_id' => $donor->user_id,
                    'title' => 'Blood Donation Request',
                    'message' => "You have been assigned to fulfill a blood request for {$bloodRequest->blood_group}. Patient: {$bloodRequest->patient_name}",
                    'type' => 'info'
                ]);
            }

            DB::commit();
            
            return back()->with('success', 'Donor assigned successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to assign donor: ' . $e->getMessage());
        }
    }

    /**
     * Notify eligible donors about urgent blood request.
     */
    public function notifyDonors($id)
    {
        try {
            $bloodRequest = BloodRequest::findOrFail($id);
            
            // Find all eligible donors with matching blood group who have donated before
            $bloodGroup = BloodGroup::where('name', $bloodRequest->blood_group)->first();
            if (!$bloodGroup) {
                return back()->with('error', 'Invalid blood group.');
            }
            
            // Only notify donors who have donated before and have matching blood group
            $donors = Donor::where('blood_group_id', $bloodGroup->id)
                          ->where('status', 'active')
                          ->whereHas('donations', function($query) {
                              $query->where('status', 'completed');
                          })
                          ->with('user')
                          ->get();

            $notificationCount = 0;
            foreach ($donors as $donor) {
                if ($donor->user) {
                    Notification::create([
                        'user_id' => $donor->user_id,
                        'title' => 'Urgent Blood Donation Needed',
                        'message' => "Urgent: {$bloodRequest->blood_group} blood needed for {$bloodRequest->patient_name} at {$bloodRequest->hospital}. Units needed: {$bloodRequest->units_needed}. Reason: {$bloodRequest->reason}",
                        'type' => 'urgent'
                    ]);
                    $notificationCount++;
                }
            }

            return back()->with('success', "Notification sent to {$notificationCount} eligible donors who have donated before.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to notify donors: ' . $e->getMessage());
        }
    }

    /**
     * Fulfill an approved blood request by creating donation record
     */
    public function fulfill(Request $request, $id)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'units' => 'required|integer|min:1|max:2',
            'donation_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $bloodRequest = BloodRequest::findOrFail($id);
            
            if ($bloodRequest->status !== 'approved') {
                return back()->with('error', 'Blood request must be approved before fulfillment.');
            }

            $donor = Donor::with('bloodGroup')->findOrFail($request->donor_id);
            
            // Check if donor blood group matches request
            $donorBloodGroup = $donor->bloodGroup->name ?? null;
            if ($donorBloodGroup !== $bloodRequest->blood_group) {
                return back()->with('error', 'Donor blood group does not match the request.');
            }

            // Create the donation record
            $donation = Donation::create([
                'donor_id' => $request->donor_id,
                'blood_group_id' => $donor->blood_group_id,
                'blood_group' => $donorBloodGroup,
                'quantity' => $request->units * 450, // 450ml per unit
                'units' => $request->units,
                'donation_date' => $request->donation_date,
                'status' => 'completed',
                'notes' => "Donation for blood request #{$bloodRequest->id} - Patient: {$bloodRequest->patient_name}",
                'collection_center' => 'Main Blood Bank',
                'created_by' => auth()->id()
            ]);
            
            // Find blood group ID
            $bloodGroup = BloodGroup::where('name', $donorBloodGroup)->first();
            if (!$bloodGroup) {
                throw new \Exception('Invalid blood group: ' . $donorBloodGroup);
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

            // Mark blood request as fulfilled
            $bloodRequest->update([
                'status' => 'fulfilled',
                'assigned_donor_id' => $donor->id,
                'assigned_at' => now()
            ]);

            // Create notification for the donor
            if ($donor->user) {
                Notification::create([
                    'user_id' => $donor->user_id,
                    'title' => 'Donation Recorded',
                    'message' => "Thank you for your donation! Your {$request->units} unit(s) of {$donorBloodGroup} blood has been recorded.",
                    'type' => 'success'
                ]);
            }

            // Create notification for the requester
            if ($bloodRequest->user) {
                Notification::create([
                    'user_id' => $bloodRequest->user_id,
                    'title' => 'Blood Request Fulfilled',
                    'message' => "Your blood request for {$bloodRequest->blood_group} has been fulfilled. Please contact the blood bank for collection.",
                    'type' => 'success'
                ]);
            }

            DB::commit();
            
            return back()->with('success', "Blood request fulfilled successfully! Donation of {$request->units} unit(s) recorded and inventory updated.");
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to fulfill blood request: ' . $e->getMessage());
        }
    }

    /**
     * Dispense blood from inventory to fulfill approved blood request
     */
    public function dispenseBlood(Request $request, $id)
    {
        $request->validate([
            'units_to_dispense' => 'required|integer|min:1|max:4'
        ]);

        try {
            DB::beginTransaction();

            $bloodRequest = BloodRequest::findOrFail($id);
            
            if ($bloodRequest->status !== 'approved') {
                return back()->with('error', 'Blood request must be approved before dispensing.');
            }

            // Find blood group ID
            $bloodGroup = BloodGroup::where('name', $bloodRequest->blood_group)->first();
            if (!$bloodGroup) {
                return back()->with('error', 'Invalid blood group.');
            }

            // Check blood inventory availability
            $totalAvailable = BloodInventory::where('blood_group_id', $bloodGroup->id)
                ->where('status', 'available')
                ->where('expiry_date', '>', now())
                ->sum('units_available');
            
            $unitsToDispense = $request->units_to_dispense;
            if ($totalAvailable < $unitsToDispense) {
                return back()->with('error', "Insufficient blood units available. Available: {$totalAvailable}, Needed: {$unitsToDispense}");
            }

            // Get inventory items to dispense (FIFO - First to expire, first to use)
            $inventoryItems = BloodInventory::where('blood_group_id', $bloodGroup->id)
                ->where('status', 'available')
                ->where('expiry_date', '>', now())
                ->whereNotNull('donor_id')  // Only get items with donor_id
                ->orderBy('expiry_date', 'asc')
                ->limit($unitsToDispense)
                ->get();
                
            if ($inventoryItems->count() < $unitsToDispense) {
                return back()->with('error', "Insufficient blood units with donor information available. Available: {$inventoryItems->count()}, Needed: {$unitsToDispense}");
            }
                
            $totalDispensed = 0;
            foreach ($inventoryItems as $item) {
                // Create dispensed record
                Donation::create([
                    'donor_id' => $item->donor_id,
                    'blood_group_id' => $bloodGroup->id,
                    'blood_group' => $bloodRequest->blood_group,
                    'quantity' => $item->quantity,
                    'units' => 1,
                    'donation_date' => now(),
                    'status' => 'dispensed',
                    'notes' => "Blood dispensed for request #{$bloodRequest->id} - Patient: {$bloodRequest->patient_name} at {$bloodRequest->hospital}",
                    'collection_center' => $item->location ?? 'Main Blood Bank',
                    'created_by' => auth()->id()
                ]);

                // Mark inventory as used
                $item->update([
                    'status' => 'used',
                    'units_available' => 0,
                    'units_requested' => 0
                ]);
                
                $totalDispensed++;
            }

            // Mark blood request as fulfilled
            $bloodRequest->update([
                'status' => 'fulfilled',
                'assigned_at' => now()
            ]);

            // Create notification for the requester
            if ($bloodRequest->user) {
                Notification::create([
                    'user_id' => $bloodRequest->user_id,
                    'title' => 'Blood Ready for Collection',
                    'message' => "Your blood request for {$unitsToDispense} unit(s) of {$bloodRequest->blood_group} has been fulfilled. Please collect from the blood bank.",
                    'type' => 'success'
                ]);
            }

            DB::commit();
            
            return back()->with('success', "Successfully dispensed {$totalDispensed} unit(s) of {$bloodRequest->blood_group} blood for patient {$bloodRequest->patient_name}.");
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to dispense blood: ' . $e->getMessage());
        }
    }
}
