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

    public function rsvpEmergency(\Illuminate\Http\Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->donor) {
            return redirect()->route('donor.profile.edit')->with('error', 'Please complete your donor profile first.');
        }

        $bloodRequest = \App\Models\BloodRequest::findOrFail($id);

        try {
            $appointmentData = [
                'appointment_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'notes' => "Emergency RSVP Arrival for Request #REQ-{$bloodRequest->id} at {$bloodRequest->hospital}",
            ];

            $appointment = $this->appointmentService->bookAppointment($user->donor, $appointmentData);

            // Log RSVP Response Activity
            activity()
                ->causedBy($user)
                ->performedOn($bloodRequest)
                ->log("Donor {$user->name} confirmed emergency donation RSVP for Request #REQ-{$bloodRequest->id} at {$bloodRequest->hospital}");

            // Notify Admin of Donor RSVP Arrival Confirmation
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                \App\Models\UserNotification::create([
                    'user_id' => $admin->id,
                    'type' => 'emergency_rsvp',
                    'title' => "🩸 EMERGENCY DONOR RSVP CONFIRMED",
                    'message' => "Donor {$user->name} ({$user->donor->bloodGroup->name ?? 'N/A'}) confirmed arrival RSVP for Request #REQ-{$bloodRequest->id} at {$bloodRequest->hospital}!",
                    'data' => [
                        'donor_id' => $user->donor->id,
                        'blood_request_id' => $bloodRequest->id,
                        'appointment_id' => $appointment->id,
                    ],
                ]);
            }

            return redirect()->route('donor.appointments.index')
                ->with('success', "🩸 Thank you! Your Emergency Donation RSVP for {$bloodRequest->hospital} has been confirmed. Your arrival appointment is set for today.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to confirm emergency RSVP: ' . $e->getMessage());
        }
    }
}
