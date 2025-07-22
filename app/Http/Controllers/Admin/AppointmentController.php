<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Donor;
use App\Models\Donation;
use App\Models\BloodGroup;
use App\Models\BloodInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['donor.user', 'donor.bloodGroup'])
            ->orderBy('appointment_date', 'desc')
            ->get();
            
        return view('admin.appointments.index', [
            'appointments' => $appointments
        ]);
    }

    public function create()
    {
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        return view('admin.appointments.create', [
            'donors' => $donors
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'location' => 'required|string|max:255',
            'units_to_donate' => 'required|integer|min:1|max:2',
            'notes' => 'nullable|string',
        ]);

        Appointment::create($request->all());

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['donor.user', 'donor.bloodGroup'])
            ->findOrFail($id);
            
        return view('admin.appointments.show', [
            'appointment' => $appointment
        ]);
    }

    public function edit($id)
    {
        $appointment = Appointment::with(['donor.user'])->findOrFail($id);
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        
        return view('admin.appointments.edit', [
            'appointment' => $appointment,
            'donors' => $donors
        ]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'location' => 'required|string|max:255',
            'units_to_donate' => 'required|integer|min:1|max:2',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($request->all());

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Mark appointment as completed
     */
    public function markCompleted($id)
    {
        try {
            DB::beginTransaction();
            
            $appointment = Appointment::with(['donor.bloodGroup'])->findOrFail($id);
            
            if ($appointment->status === 'completed') {
                return redirect()->route('admin.appointments.index')
                    ->with('error', 'Appointment is already marked as completed.');
            }
            
            $donor = $appointment->donor;
            $unitsTodonate = $appointment->units_to_donate ?? 1;
            
            // Create the donation record
            $donation = Donation::create([
                'donor_id' => $donor->id,
                'blood_group_id' => $donor->blood_group_id,
                'blood_group' => $donor->bloodGroup->name,
                'quantity' => $unitsTodonate * 450, // 450ml per unit
                'units' => $unitsTodonate,
                'donation_date' => now(),
                'status' => 'completed',
                'notes' => "Donation from appointment #{$appointment->id}",
                'collection_center' => $appointment->location ?? 'Main Blood Bank',
                'created_by' => auth()->id()
            ]);
            
            // Find blood group ID
            $bloodGroup = BloodGroup::where('name', $donor->bloodGroup->name)->first();
            if (!$bloodGroup) {
                throw new \Exception('Invalid blood group: ' . $donor->bloodGroup->name);
            }
            
            // Create inventory record for each unit donated
            for ($i = 0; $i < $unitsTodonate; $i++) {
                BloodInventory::create([
                    'blood_group_id' => $bloodGroup->id,
                    'quantity' => 450, // Standard blood unit is 450ml
                    'collection_date' => now(),
                    'expiry_date' => now()->addDays(42), // Blood expires in 42 days
                    'location' => $appointment->location ?? 'Main Blood Bank',
                    'donor_id' => $donor->id,
                    'units_available' => 1,
                    'units_requested' => 0,
                    'status' => 'available'
                ]);
            }
            
            // Update donor's last donation date
            $donor->update([
                'last_donation_date' => now()
            ]);
            
            // Mark appointment as completed
            $appointment->update(['status' => 'completed']);
            
            DB::commit();
            
            return redirect()->route('admin.appointments.index')
                ->with('success', 'Appointment marked as completed, donation recorded, and inventory updated.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.appointments.index')
                ->with('error', 'Failed to complete appointment: ' . $e->getMessage());
        }
    }

    /**
     * Mark appointment as cancelled
     */
    public function markCancelled($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Mark appointment as no show
     */
    public function markNoShow($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'no_show']);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment marked as no show.');
    }
}
