<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BloodRequest;
use App\Models\Donor;
use App\Models\Transfusion;
use App\Models\TransfusionReaction;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected DonorEligibilityService $eligibilityService;
    protected BloodGroupCompatibilityService $compatibilityService;

    public function __construct(
        DonorEligibilityService $eligibilityService,
        BloodGroupCompatibilityService $compatibilityService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->compatibilityService = $compatibilityService;
    }

    /**
     * Create a user notification in user_notifications table gracefully.
     */
    public function createUserNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null
    ): ?UserNotification {
        try {
            return UserNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to create user notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify administrators when any blood requisition is filed by a hospital.
     */
    public function notifyAdminRequestCreated(BloodRequest $request): int
    {
        $admins = User::where('role', 'admin')->get();
        $count = 0;
        $urgencyUpper = strtoupper($request->urgency_level);

        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                $request->urgency_level === 'emergency' ? 'emergency' : 'blood_request',
                "NEW REQUISITION: #REQ-{$request->id} ({$urgencyUpper})",
                "Hospital '{$request->hospital}' requested {$request->units_needed} unit(s) of {$request->blood_group}.",
                [
                    'blood_request_id' => $request->id,
                    'hospital_id' => $request->hospital_id,
                    'blood_group' => $request->blood_group,
                    'units_needed' => $request->units_needed,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Notify administrators when an emergency requisition is filed (legacy alias).
     */
    public function notifyAdminEmergencyRequest(BloodRequest $request): int
    {
        return $this->notifyAdminRequestCreated($request);
    }

    /**
     * Notify administrators when a donor schedules an appointment.
     */
    public function notifyAdminAppointmentBooked(Appointment $appointment): int
    {
        $admins = User::where('role', 'admin')->get();
        $count = 0;
        $donorName = optional(optional($appointment->donor)->user)->name ?? 'Donor';

        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                'appointment',
                "NEW APPOINTMENT: Donor {$donorName}",
                "Donor {$donorName} booked a donation appointment for {$appointment->appointment_date}.",
                ['appointment_id' => $appointment->id]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Notify donor when appointment status changes.
     */
    public function notifyDonorAppointmentStatusChange(Appointment $appointment, string $status): void
    {
        if (optional($appointment->donor)->user) {
            $statusUpper = strtoupper($status);
            $this->createUserNotification(
                $appointment->donor->user,
                'appointment',
                "APPOINTMENT STATUS: {$statusUpper}",
                "Your donation appointment for {$appointment->appointment_date} has been marked as {$status}.",
                ['appointment_id' => $appointment->id, 'status' => $status]
            );
        }
    }

    /**
     * Notify hospital user when requisition status updates (approved, rejected, dispensed).
     */
    public function notifyHospitalStatusChange(BloodRequest $request, string $status, ?string $reason = null): int
    {
        $count = 0;
        if ($request->user_id) {
            $user = User::find($request->user_id);
            if ($user) {
                $statusUpper = strtoupper($status);
                $title = "REQUISITION STATUS: #REQ-{$request->id} {$statusUpper}";
                $message = "Your requisition for {$request->units_needed} unit(s) of {$request->blood_group} has been {$status}.";
                if ($reason) {
                    $message .= " Reason: {$reason}";
                }

                $this->createUserNotification(
                    $user,
                    $status,
                    $title,
                    $message,
                    [
                        'blood_request_id' => $request->id,
                        'status' => $status,
                        'reason' => $reason,
                    ]
                );
                $count++;
            }
        }
        return $count;
    }

    /**
     * Dispatch emergency notifications strictly to eligible and medically compatible donors.
     */
    public function notifyEligibleDonors(BloodRequest $request): int
    {
        $compatibleGroups = $this->compatibilityService->getCompatibleDonorGroups($request->blood_group);

        // Fetch candidate donors matching compatible blood groups
        $candidateDonors = Donor::whereHas('bloodGroup', function ($q) use ($compatibleGroups) {
            $q->whereIn('name', $compatibleGroups);
        })->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with(['user', 'bloodGroup'])->get();

        $notifiedCount = 0;

        foreach ($candidateDonors as $donor) {
            // Validate eligibility server-side (account active + no medical deferral + 56-day interval)
            $eligibility = $this->eligibilityService->checkEligibility($donor);

            if ($eligibility['eligible']) {
                try {
                    $donor->user->notify(new EmergencyBloodRequestNotification($request));

                    $this->createUserNotification(
                        $donor->user,
                        'emergency',
                        "URGENT: Emergency Donation Appeal ({$request->blood_group})",
                        "An emergency blood request matching your blood type was created at {$request->hospital}.",
                        [
                            'blood_request_id' => $request->id,
                            'blood_group' => $request->blood_group,
                            'hospital' => $request->hospital,
                        ]
                    );
                    $notifiedCount++;
                } catch (\Throwable $e) {
                    Log::error("Donor notification failed for User #{$donor->user_id}: " . $e->getMessage());
                }
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($request)
            ->log("Dispatched emergency notifications to {$notifiedCount} eligible donors for request #{$request->id}");

        return $notifiedCount;
    }

    public function sendEmergencyBroadcast(BloodRequest $bloodRequest): int
    {
        return $this->notifyEligibleDonors($bloodRequest);
    }

    public function notifyTransfusionStarted(Transfusion $transfusion): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                'transfusion',
                "Transfusion #TR-{$transfusion->id} Started",
                "Transfusion commenced for patient {$transfusion->patient->name} at {$transfusion->hospital->name}.",
                ['transfusion_id' => $transfusion->id]
            );
        }
    }

    public function notifyTransfusionCompleted(Transfusion $transfusion): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                'transfusion',
                "Transfusion #TR-{$transfusion->id} Completed",
                "Transfusion completed successfully for patient {$transfusion->patient->name}.",
                ['transfusion_id' => $transfusion->id]
            );
        }
    }

    public function notifyTransfusionReaction(TransfusionReaction $reaction): void
    {
        $admins = User::where('role', 'admin')->get();
        $severityUpper = strtoupper($reaction->severity);
        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                'reaction',
                "ALERT: {$severityUpper} Transfusion Reaction Recorded",
                "Transfusion #TR-{$reaction->transfusion_id} recorded a {$reaction->severity} reaction: {$reaction->symptoms}",
                [
                    'transfusion_id' => $reaction->transfusion_id,
                    'severity' => $reaction->severity,
                ]
            );
        }
    }

    public function notifyTransfusionStopped(Transfusion $transfusion, string $reason): void
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $this->createUserNotification(
                $admin,
                'transfusion',
                "WARNING: Transfusion #TR-{$transfusion->id} Stopped",
                "Transfusion stopped for patient {$transfusion->patient->name}. Reason: {$reason}",
                ['transfusion_id' => $transfusion->id, 'reason' => $reason]
            );
        }
    }
}
