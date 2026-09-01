<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    protected DonorEligibilityService $eligibilityService;
    protected NotificationService $notificationService;

    public function __construct(
        DonorEligibilityService $eligibilityService,
        NotificationService $notificationService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->notificationService = $notificationService;
    }

    public function bookAppointment(Donor $donor, array $data): Appointment
    {
        $eligibility = $this->eligibilityService->checkEligibility($donor);

        if (!$eligibility['eligible']) {
            $reasonStr = !empty($eligibility['reasons']) ? implode(' ', $eligibility['reasons']) : 'Donor is currently ineligible.';
            throw new \InvalidArgumentException($reasonStr);
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

            $this->notificationService->notifyAdminAppointmentBooked($appointment);

            activity()
                ->causedBy($donor->user)
                ->performedOn($appointment)
                ->log("Appointment scheduled for {$appointment->appointment_date}");

            return $appointment;
        });
    }

    public function transitionState(Appointment $appointment, string $targetStatus, ?User $actor = null): bool
    {
        $allowedTransitions = [
            'scheduled'            => ['checked_in', 'screening', 'donation_in_progress', 'completed', 'cancelled', 'no_show'],
            'checked_in'           => ['screening', 'donation_in_progress', 'completed', 'cancelled', 'no_show'],
            'screening'            => ['donation_in_progress', 'completed', 'deferred', 'cancelled'],
            'donation_in_progress' => ['completed', 'cancelled'],
            'completed'            => ['cancelled'],
            'cancelled'            => ['scheduled'],
            'no_show'              => ['scheduled'],
            'deferred'             => ['scheduled'],
        ];

        $currentStatus = $appointment->status;

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($targetStatus, $allowedTransitions[$currentStatus], true)) {
            throw new \InvalidArgumentException("Invalid appointment status transition from {$currentStatus} to {$targetStatus}.");
        }

        return DB::transaction(function () use ($appointment, $targetStatus, $actor) {
            $appointment->update(['status' => $targetStatus]);

            $this->notificationService->notifyDonorAppointmentStatusChange($appointment, $targetStatus);

            activity()
                ->causedBy($actor ?? auth()->user())
                ->performedOn($appointment)
                ->log("Appointment #{$appointment->id} transitioned to {$targetStatus}");

            return true;
        });
    }

    public function updateStatus(Appointment $appointment, string $status, ?User $actor = null): bool
    {
        return $this->transitionState($appointment, $status, $actor);
    }
}
