<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BloodComponent;
use App\Models\BloodUnit;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * Process 1-Click Donation Intake directly from a Donor Appointment.
     * Auto-creates physical BloodUnit bag, sets donor_id, marks appointment completed,
     * and updates donor last_donation_date.
     */
    public function processIntake(Appointment $appointment, array $data, ?User $actor = null): BloodUnit
    {
        return DB::transaction(function () use ($appointment, $data, $actor) {
            $donor = $appointment->donor;
            if (!$donor) {
                throw new \InvalidArgumentException("Appointment #{$appointment->id} has no linked donor record.");
            }

            // Determine blood component
            $componentId = $data['component_id'] ?? null;
            if (empty($componentId)) {
                $defaultComponent = BloodComponent::firstOrCreate(
                    ['name' => 'Whole Blood'],
                    ['code' => 'WB', 'description' => 'Whole Blood Component', 'shelf_life_days' => 42]
                );
                $componentId = $defaultComponent->id;
            }

            $component = BloodComponent::find($componentId);
            $shelfLifeDays = $component ? ($component->shelf_life_days ?? 42) : 42;
            $expiryDate = now()->addDays((int)($data['expiration_days'] ?? $shelfLifeDays));

            $bloodGroup = $donor->bloodGroup;
            $groupName = $bloodGroup ? $bloodGroup->name : 'AP';
            $unitNumber = 'BAG-' . strtoupper(str_replace(['+', '-'], ['P', 'N'], $groupName)) . '-' . strtoupper(Str::random(6));

            // Create physical BloodUnit bag with donor link
            $unit = BloodUnit::create([
                'unit_number'      => $unitNumber,
                'blood_group_id'   => $donor->blood_group_id,
                'component_id'     => $componentId,
                'donor_id'         => $donor->id,
                'collection_date'  => now()->format('Y-m-d'),
                'expiry_date'      => $expiryDate->format('Y-m-d'),
                'status'           => 'available',
                'storage_location' => $data['storage_location'] ?? 'Central Blood Bank Storage Room A',
                'volume_ml'        => (int)($data['volume_ml'] ?? 450),
            ]);

            // Transition appointment status to completed
            $appointment->update(['status' => 'completed']);

            // Update donor last_donation_date to Today
            $donor->update(['last_donation_date' => now()->format('Y-m-d')]);

            // Notify Donor
            if ($donor->user) {
                $this->notificationService->createUserNotification(
                    $donor->user,
                    'donation',
                    "Donation Intake Completed! 🩸",
                    "Thank you for donating 1 unit of {$groupName} blood! Your donation has been safely intaken into central inventory (Bag #{$unitNumber}).",
                    ['unit_number' => $unitNumber, 'appointment_id' => $appointment->id]
                );
            }

            // Log activity
            activity()
                ->causedBy($actor ?? auth()->user())
                ->performedOn($unit)
                ->log("Completed donation intake from Appointment #{$appointment->id} into Blood Bag {$unitNumber}");

            return $unit;
        });
    }
}
