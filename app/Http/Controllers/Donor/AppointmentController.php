<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::whereHas('donor', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['donor.bloodGroup'])
            ->orderBy('appointment_date', 'desc')
            ->get();
            
        return view('donor.appointments.index', [
            'appointments' => $appointments
        ]);
    }

    public function create()
    {
        return view('donor.appointments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string',
            'units_to_donate' => 'required|integer|min:1|max:2',
        ]);

        $donor = Auth::user()->donor;
        
        if (!$donor) {
            return back()->withErrors(['error' => 'Donor profile not found.']);
        }

        Appointment::create([
            'donor_id' => $donor->id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'notes' => $request->notes,
            'units_to_donate' => $request->units_to_donate,
            'status' => 'scheduled',
        ]);

        return redirect()->route('donor.appointments.index')
            ->with('success', 'Appointment scheduled successfully.');
    }

    public function show($id)
    {
        $appointment = Appointment::whereHas('donor', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['donor.bloodGroup'])
            ->findOrFail($id);
            
        return view('donor.appointments.show', [
            'appointment' => $appointment
        ]);
    }

    public function edit($id)
    {
        $appointment = Appointment::whereHas('donor', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);
            
        // Only allow editing of scheduled appointments
        if ($appointment->status !== 'scheduled') {
            return redirect()->route('donor.appointments.index')
                ->with('error', 'Only scheduled appointments can be edited.');
        }
        
        return view('donor.appointments.edit', [
            'appointment' => $appointment
        ]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::whereHas('donor', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->findOrFail($id);
            
        // Only allow editing of scheduled appointments
        if ($appointment->status !== 'scheduled') {
            return redirect()->route('donor.appointments.index')
                ->with('error', 'Only scheduled appointments can be edited.');
        }
        
        $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string',
            'units_to_donate' => 'required|integer|min:1|max:2',
        ]);

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'notes' => $request->notes,
            'units_to_donate' => $request->units_to_donate,
        ]);

        return redirect()->route('donor.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }
}
