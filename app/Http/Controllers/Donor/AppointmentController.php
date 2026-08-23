<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Donor\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index()
    {
        $user = auth()->user();
        $appointments = $user->donor
            ? Appointment::where('donor_id', $user->donor->id)->latest()->get()
            : collect();

        return view('donor.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->donor) {
            return redirect()->route('donor.profile.edit')
                ->with('error', 'Please complete your donor profile before scheduling an appointment.');
        }

        return view('donor.appointments.create');
    }

    public function store(StoreAppointmentRequest $request)
    {
        $user = auth()->user();
        if (!$user->donor) {
            return redirect()->route('donor.profile.edit')
                ->with('error', 'Please complete your donor profile first.');
        }

        try {
            $this->appointmentService->bookAppointment($user->donor, $request->validated());
            return redirect()->route('donor.appointments.index')
                ->with('success', 'Appointment booked successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['appointment_date' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('view', $appointment);

        return view('donor.appointments.show', compact('appointment'));
    }

    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('cancel', $appointment);

        $this->appointmentService->updateStatus($appointment, 'cancelled', auth()->user());

        return back()->with('success', 'Appointment cancelled successfully.');
    }
}
