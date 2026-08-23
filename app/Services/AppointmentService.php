<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    protected DonorEligibilityService $eligibilityService;

    public function __construct(DonorEligibilityService $eligibilityService)
    {
        $this->eligibilityService = $eligibilityService;
    }

    public function bookAppointment(Donor $donor, array $data): Appointment
    {
        $eligibility = $this->eligibilityService->checkEligibility($donor);

        if (!$eligibility['eligible']) {
            $nextDate = $eligibility['next_eligible_date']->format('Y-m-d');
            $daysLeft = $eligibility['days_until_eligible'];
            throw new \InvalidArgumentException("You are not eligible to donate again until {$nextDate}. Please wait {$daysLeft} more days.");
        }

        return DB::transaction(function () use ($donor, $data) {
            $appointment = Appointment::create([
                'donor_id' => $donor->id,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'] ?? $data['time_slot'] ?? '10:00:00',
                'units_to_donate' => $data['units_to_donate'] ?? 1,
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
                'location' => $data['location'] ?? 'Main Blood Bank',
            ]);

            activity()
                ->causedBy($donor->user)
                ->performedOn($appointment)
                ->log("Appointment scheduled for {$appointment->appointment_date}");

            return $appointment;
        });
    }

    public function updateStatus(Appointment $appointment, string $status, ?User $actor = null): bool
    {
        $validStatuses = ['scheduled', 'completed', 'cancelled', 'no_show'];
        
        if (!in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException("Invalid appointment status: {$status}");
        }

        return DB::transaction(function () use ($appointment, $status, $actor) {
            $appointment->update(['status' => $status]);

            activity()
                ->causedBy($actor ?? auth()->user())
                ->performedOn($appointment)
                ->log("Appointment #{$appointment->id} status updated to {$status}");

            return true;
        });
    }
}
