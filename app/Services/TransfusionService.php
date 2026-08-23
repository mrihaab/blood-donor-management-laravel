<?php

namespace App\Services;

use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Patient;
use App\Models\Transfusion;
use App\Models\TransfusionReaction;
use App\Models\TransfusionUnit;
use App\Models\UnitInspection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransfusionService
{
    protected BloodUnitService $bloodUnitService;
    protected InventoryTransactionService $transactionService;
    protected NotificationService $notificationService;
    protected BloodGroupCompatibilityService $compatibilityService;

    protected const ALLOWED_TRANSITION_MAP = [
        'scheduled' => ['issued', 'cancelled'],
        'issued'    => ['started', 'cancelled'],
        'started'   => ['completed', 'stopped'],
        'stopped'   => ['completed', 'cancelled'],
        'completed' => [], // Terminal
        'cancelled' => [], // Terminal
    ];

    public function __construct(
        BloodUnitService $bloodUnitService,
        InventoryTransactionService $transactionService,
        NotificationService $notificationService,
        BloodGroupCompatibilityService $compatibilityService
    ) {
        $this->bloodUnitService = $bloodUnitService;
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
        $this->compatibilityService = $compatibilityService;
    }

    public function validateStateTransition(Transfusion $transfusion, string $newStatus): void
    {
        $currentStatus = $transfusion->status;
        if ($currentStatus === $newStatus) {
            return;
        }

        $allowed = self::ALLOWED_TRANSITION_MAP[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid transfusion transition: Cannot move from '{$currentStatus}' to '{$newStatus}'.");
        }
    }

    public function createTransfusion(BloodRequest $request, Patient $patient, User $actor, ?string $notes = null): Transfusion
    {
        return DB::transaction(function () use ($request, $patient, $actor, $notes) {
            if ((int)$request->patient_id !== (int)$patient->id && $request->patient_name !== $patient->name) {
                throw new \InvalidArgumentException("Patient #{$patient->id} does not match blood request #{$request->id}.");
            }

            if ((int)$request->hospital_id !== (int)$patient->hospital_id) {
                throw new \InvalidArgumentException("Hospital mismatch between request and patient.");
            }

            if ($request->status !== 'approved' && $request->status !== 'dispensed') {
                throw new \InvalidArgumentException("Cannot schedule transfusion for non-approved request #{$request->id}.");
            }

            $transfusion = Transfusion::create([
                'blood_request_id' => $request->id,
                'patient_id'       => $patient->id,
                'hospital_id'      => $patient->hospital_id,
                'status'           => 'scheduled',
                'notes'            => $notes,
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Transfusion #TR-{$transfusion->id} scheduled for patient {$patient->name}.");

            return $transfusion;
        });
    }

    public function issueUnits(Transfusion $transfusion, array $unitIds, User $actor, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($transfusion, $unitIds, $actor, $notes) {
            $this->validateStateTransition($transfusion, 'issued');

            $units = BloodUnit::whereIn('id', $unitIds)
                ->lockForUpdate()
                ->get();

            if ($units->count() !== count($unitIds)) {
                throw new \InvalidArgumentException("One or more specified blood units could not be found.");
            }

            foreach ($units as $unit) {
                // Verify unit is not expired or discarded
                if ($unit->status === 'expired' || $unit->expiry_date < now()->format('Y-m-d')) {
                    throw new \InvalidArgumentException("Unit {$unit->unit_number} is expired.");
                }
                if ($unit->status === 'discarded') {
                    throw new \InvalidArgumentException("Unit {$unit->unit_number} is discarded.");
                }

                // ABO/Rh Compatibility Check
                if (!$this->compatibilityService->validatePatientUnitCompatibility($transfusion->patient, $unit)) {
                    activity()
                        ->causedBy($actor)
                        ->performedOn($transfusion)
                        ->log("SECURITY ALERT: Attempted to issue incompatible unit {$unit->unit_number} ({$unit->bloodGroup->name}) to patient {$transfusion->patient->name} ({$transfusion->patient->bloodGroup->name}).");

                    throw new \InvalidArgumentException("Blood unit {$unit->unit_number} ({$unit->bloodGroup->name}) is incompatible with patient {$transfusion->patient->name} ({$transfusion->patient->bloodGroup->name}).");
                }

                // Prevent double assignment to active transfusions
                $activeAssignment = TransfusionUnit::where('blood_unit_id', $unit->id)
                    ->whereIn('disposition', ['issued', 'transfused'])
                    ->where('transfusion_id', '!=', $transfusion->id)
                    ->exists();

                if ($activeAssignment) {
                    throw new \InvalidArgumentException("Unit {$unit->unit_number} is already attached to another active transfusion.");
                }

                // Transition unit to dispensed if currently allocated/reserved/available
                if ($unit->status !== 'dispensed') {
                    $this->bloodUnitService->transitionStatus(
                        unit: $unit,
                        newStatus: 'dispensed',
                        reason: "Dispensed for Transfusion #TR-{$transfusion->id}",
                        actor: $actor
                    );

                    $this->transactionService->logTransaction(
                        bloodUnit: $unit,
                        transactionType: 'dispensed',
                        previousQuantity: $unit->volume_ml,
                        quantityChanged: -$unit->volume_ml,
                        resultingQuantity: 0,
                        reason: "Issued for Transfusion #TR-{$transfusion->id}",
                        actor: $actor,
                        referenceType: Transfusion::class,
                        referenceId: $transfusion->id
                    );
                }

                TransfusionUnit::firstOrCreate(
                    [
                        'transfusion_id' => $transfusion->id,
                        'blood_unit_id' => $unit->id,
                    ],
                    [
                        'issued_at'   => now(),
                        'disposition' => 'issued',
                        'notes'       => $notes,
                    ]
                );
            }

            $transfusion->update([
                'status' => 'issued',
                'notes'  => $notes ?? $transfusion->notes,
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Issued {$units->count()} unit(s) for Transfusion #TR-{$transfusion->id}.");

            return true;
        });
    }

    public function startTransfusion(Transfusion $transfusion, User $actor, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($transfusion, $actor, $notes) {
            $this->validateStateTransition($transfusion, 'started');

            $transfusion->update([
                'status'          => 'started',
                'started_at'      => now(),
                'administered_by' => $actor->id,
                'notes'           => $notes ?? $transfusion->notes,
            ]);

            TransfusionUnit::where('transfusion_id', $transfusion->id)
                ->whereNull('started_at')
                ->update(['started_at' => now()]);

            $this->notificationService->notifyTransfusionStarted($transfusion);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Transfusion #TR-{$transfusion->id} started by {$actor->name}.");

            return true;
        });
    }

    public function completeTransfusion(Transfusion $transfusion, User $actor, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($transfusion, $actor, $notes) {
            $this->validateStateTransition($transfusion, 'completed');

            $transfusion->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'notes'        => $notes ?? $transfusion->notes,
            ]);

            $transfusionUnits = TransfusionUnit::where('transfusion_id', $transfusion->id)->get();
            foreach ($transfusionUnits as $tUnit) {
                $tUnit->update([
                    'completed_at' => now(),
                    'disposition'  => 'transfused',
                ]);

                $unit = BloodUnit::where('id', $tUnit->blood_unit_id)->lockForUpdate()->first();
                if ($unit && $unit->status !== 'transfused') {
                    $this->bloodUnitService->transitionStatus(
                        unit: $unit,
                        newStatus: 'transfused',
                        reason: "Transfusion #TR-{$transfusion->id} completed successfully",
                        actor: $actor
                    );

                    $this->transactionService->logTransaction(
                        bloodUnit: $unit,
                        transactionType: 'transfused',
                        previousQuantity: 0,
                        quantityChanged: 0,
                        resultingQuantity: 0,
                        reason: "Transfusion #TR-{$transfusion->id} completed",
                        actor: $actor,
                        referenceType: Transfusion::class,
                        referenceId: $transfusion->id
                    );
                }
            }

            $this->notificationService->notifyTransfusionCompleted($transfusion);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Transfusion #TR-{$transfusion->id} completed.");

            return true;
        });
    }

    public function stopTransfusion(Transfusion $transfusion, User $actor, string $reason): bool
    {
        return DB::transaction(function () use ($transfusion, $actor, $reason) {
            $this->validateStateTransition($transfusion, 'stopped');

            $transfusion->update([
                'status' => 'stopped',
                'notes'  => trim(($transfusion->notes ?? '') . " | STOPPED: " . $reason),
            ]);

            $this->notificationService->notifyTransfusionStopped($transfusion, $reason);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Transfusion #TR-{$transfusion->id} stopped. Reason: {$reason}");

            return true;
        });
    }

    public function cancelTransfusion(Transfusion $transfusion, User $actor, string $reason): bool
    {
        return DB::transaction(function () use ($transfusion, $actor, $reason) {
            $this->validateStateTransition($transfusion, 'cancelled');

            $transfusion->update([
                'status' => 'cancelled',
                'notes'  => trim(($transfusion->notes ?? '') . " | CANCELLED: " . $reason),
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($transfusion)
                ->log("Transfusion #TR-{$transfusion->id} cancelled.");

            return true;
        });
    }

    public function recordReaction(Transfusion $transfusion, array $data, User $reporter): TransfusionReaction
    {
        return DB::transaction(function () use ($transfusion, $data, $reporter) {
            $severity = strtolower($data['severity']);

            $reaction = TransfusionReaction::create([
                'transfusion_id' => $transfusion->id,
                'blood_unit_id'  => $data['blood_unit_id'] ?? null,
                'reaction_type'  => $data['reaction_type'],
                'severity'       => $severity,
                'symptoms'       => $data['symptoms'],
                'onset_at'       => $data['onset_at'] ?? now(),
                'reported_at'    => now(),
                'reported_by'    => $reporter->id,
                'action_taken'   => $data['action_taken'] ?? null,
                'outcome'        => $data['outcome'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);

            // Auto-stop transfusion if severe or life-threatening
            if (in_array($severity, ['severe', 'life_threatening'], true)) {
                if ($transfusion->status === 'started') {
                    $this->stopTransfusion($transfusion, $reporter, "Auto-stopped due to {$severity} transfusion reaction: {$reaction->symptoms}");
                }

                // Mark unit as discarded if severe reaction occurs
                if ($reaction->blood_unit_id) {
                    $unit = BloodUnit::where('id', $reaction->blood_unit_id)->lockForUpdate()->first();
                    if ($unit && in_array($unit->status, ['dispensed', 'allocated'], true)) {
                        $this->bloodUnitService->transitionStatus(
                            unit: $unit,
                            newStatus: 'discarded',
                            reason: "Severe reaction recorded during transfusion #TR-{$transfusion->id}",
                            actor: $reporter
                        );

                        $this->transactionService->logTransaction(
                            bloodUnit: $unit,
                            transactionType: 'discarded',
                            previousQuantity: 0,
                            quantityChanged: 0,
                            resultingQuantity: 0,
                            reason: "Severe transfusion reaction: {$reaction->symptoms}",
                            actor: $reporter,
                            referenceType: TransfusionReaction::class,
                            referenceId: $reaction->id
                        );
                    }
                }
            }

            // Dispatch admin notification safely without throwing exception on email failure
            try {
                $this->notificationService->notifyTransfusionReaction($reaction);
            } catch (\Throwable $e) {
                // Log failure gracefully to prevent database transaction rollback
                \Illuminate\Support\Facades\Log::error("Failed sending reaction notification: " . $e->getMessage());
            }

            activity()
                ->causedBy($reporter)
                ->performedOn($reaction)
                ->log("Transfusion reaction ({$severity}) recorded for Transfusion #TR-{$transfusion->id}");

            return $reaction;
        });
    }

    /**
     * Quarantine Inspection & Return Safety Protocol.
     */
    public function certifyReturnedUnit(BloodUnit $unit, array $inspectionData, User $inspector): UnitInspection
    {
        return DB::transaction(function () use ($unit, $inspectionData, $inspector) {
            $unitLocked = BloodUnit::where('id', $unit->id)->lockForUpdate()->first();

            $coldChain = (bool)($inspectionData['cold_chain_intact'] ?? false);
            $sealIntact = (bool)($inspectionData['seal_intact'] ?? false);
            $elapsedMinutes = (int)($inspectionData['elapsed_time_minutes'] ?? 999);
            $visualPassed = (bool)($inspectionData['visual_inspection_passed'] ?? false);

            $isCertifiedSafe = $coldChain && $sealIntact && ($elapsedMinutes <= 30) && $visualPassed;
            $decision = $isCertifiedSafe ? 'certified_safe' : 'discarded';

            $inspection = UnitInspection::create([
                'blood_unit_id'            => $unitLocked->id,
                'inspector_id'             => $inspector->id,
                'cold_chain_intact'        => $coldChain,
                'seal_intact'              => $sealIntact,
                'elapsed_time_minutes'     => $elapsedMinutes,
                'visual_inspection_passed' => $visualPassed,
                'decision'                 => $decision,
                'notes'                    => $inspectionData['notes'] ?? null,
                'inspected_at'             => now(),
            ]);

            // Transition status to returned first if currently dispensed
            if ($unitLocked->status === 'dispensed') {
                $this->bloodUnitService->transitionStatus(
                    unit: $unitLocked,
                    newStatus: 'returned',
                    reason: "Returned to blood bank for safety inspection",
                    actor: $inspector
                );
            }

            if ($decision === 'certified_safe') {
                $this->bloodUnitService->transitionStatus(
                    unit: $unitLocked,
                    newStatus: 'available',
                    reason: "Certified safe after clinical quarantine inspection",
                    actor: $inspector
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unitLocked,
                    transactionType: 'returned_to_stock',
                    previousQuantity: 0,
                    quantityChanged: $unitLocked->volume_ml,
                    resultingQuantity: $unitLocked->volume_ml,
                    reason: "Returned to available inventory after passing safety certification",
                    actor: $inspector,
                    referenceType: UnitInspection::class,
                    referenceId: $inspection->id
                );
            } else {
                $this->bloodUnitService->transitionStatus(
                    unit: $unitLocked,
                    newStatus: 'discarded',
                    reason: "Failed clinical return safety inspection",
                    actor: $inspector
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unitLocked,
                    transactionType: 'discarded',
                    previousQuantity: 0,
                    quantityChanged: 0,
                    resultingQuantity: 0,
                    reason: "Discarded after failing return safety inspection",
                    actor: $inspector,
                    referenceType: UnitInspection::class,
                    referenceId: $inspection->id
                );
            }

            activity()
                ->causedBy($inspector)
                ->performedOn($unitLocked)
                ->log("Unit #{$unitLocked->unit_number} quarantine inspection result: {$decision}");

            return $inspection;
        });
    }
}
