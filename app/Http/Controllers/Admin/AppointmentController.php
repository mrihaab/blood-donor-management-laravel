<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Donor;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $appointments = Appointment::with(['donor.user', 'donor.bloodGroup'])
            ->latest()
            ->get();

        return view('admin.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $donors = Donor::with(['user', 'bloodGroup'])->get();
        return view('admin.appointments.create', compact('donors'));
    }

    public function store(StoreAppointmentRequest $request)
    {
        $donor = Donor::findOrFail($request->input('donor_id'));

        try {
            $this->appointmentService->bookAppointment($donor, $request->validated());
            return redirect()->route('admin.appointments.index')
                ->with('success', 'Appointment scheduled successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['appointment_date' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $appointment = Appointment::with(['donor.user', 'donor.bloodGroup'])->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function markCompleted($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->appointmentService->updateStatus($appointment, 'completed', auth()->user());
        return back()->with('success', 'Appointment marked as completed.');
    }

    public function markCancelled($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->appointmentService->updateStatus($appointment, 'cancelled', auth()->user());
        return back()->with('success', 'Appointment marked as cancelled.');
    }

    public function markNoShow($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->appointmentService->updateStatus($appointment, 'no_show', auth()->user());
        return back()->with('success', 'Appointment marked as no show.');
    }
}
