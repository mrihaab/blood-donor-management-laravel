<?php

namespace App\Services;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BloodRequestService
{
    protected BloodUnitService $bloodUnitService;
    protected InventoryTransactionService $transactionService;
    protected NotificationService $notificationService;

    public function __construct(
        BloodUnitService $bloodUnitService,
        InventoryTransactionService $transactionService,
        NotificationService $notificationService
    ) {
        $this->bloodUnitService = $bloodUnitService;
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
    }

    public function createRequest(array $data, ?User $user = null): BloodRequest
    {
        return DB::transaction(function () use ($data, $user) {
            $hospitalId = $data['hospital_id'] ?? null;
            if (!$hospitalId && !empty($data['hospital'])) {
                $hospital = Hospital::where('name', $data['hospital'])->first();
                if ($hospital) {
                    $hospitalId = $hospital->id;
                }
            }

            $patientId = $data['patient_id'] ?? null;
            if (!$patientId && !empty($data['patient_name'])) {
                $patient = Patient::where('name', $data['patient_name'])->first();
                if ($patient) {
                    $patientId = $patient->id;
                }
            }

            $request = BloodRequest::create([
                'user_id'          => $user ? $user->id : null,
                'hospital_id'      => $hospitalId,
                'patient_id'       => $patientId,
                'patient_name'     => $data['patient_name'],
                'blood_group'      => $data['blood_group'],
                'units_needed'     => $data['units_needed'] ?? 1,
                'hospital'         => $data['hospital'],
                'city'             => $data['city'] ?? 'Metropolis',
                'reason'           => $data['reason'] ?? null,
                'required_by'      => $data['required_by'] ?? null,
                'urgency_level'    => $data['urgency'] ?? 'emergency',
                'status'           => 'pending',
            ]);

            // Always notify Admin when a new requisition is created
            $this->notificationService->notifyAdminRequestCreated($request);

            $urgency = $data['urgency'] ?? 'emergency';
            if ($urgency === 'emergency') {
                $this->notificationService->notifyEligibleDonors($request);
            }

            activity()
                ->causedBy($user)
                ->performedOn($request)
                ->log("Blood request #{$request->id} created for {$request->patient_name} ({$request->blood_group})");

            return $request;
        });
    }

    public function approveRequest(BloodRequest $request, User $admin, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $notes) {
            if ($request->status === 'approved' || $request->status === 'dispensed') {
                throw new \InvalidArgumentException("Blood request #{$request->id} is already in '{$request->status}' status.");
            }

            $group = BloodGroup::where('name', $request->blood_group)->first();
            $groupId = $group ? $group->id : null;

            // Fetch available unexpired units sorted by FEFO (earliest expiry first) with pessimistic lock
            $eligibleUnits = BloodUnit::where('blood_group_id', $groupId)
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->take($request->units_needed)
                ->get();

            if ($eligibleUnits->count() < $request->units_needed) {
                throw new \RuntimeException("Insufficient available stock for blood group {$request->blood_group}. Needed: {$request->units_needed}, Available: {$eligibleUnits->count()}");
            }

            foreach ($eligibleUnits as $unit) {
                $this->bloodUnitService->transitionStatus(
                    unit: $unit,
                    newStatus: 'allocated',
                    reason: "Allocated for Blood Request #{$request->id}",
                    actor: $admin
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unit,
                    transactionType: 'allocated',
                    previousQuantity: $unit->volume_ml,
                    quantityChanged: 0,
                    resultingQuantity: $unit->volume_ml,
                    reason: "Allocated for Blood Request #{$request->id}",
                    actor: $admin,
                    referenceType: BloodRequest::class,
                    referenceId: $request->id
                );
            }

            $request->update([
                'status'      => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            $this->notificationService->notifyHospitalStatusChange($request, 'approved');

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin approved blood request #{$request->id} and allocated {$eligibleUnits->count()} units.");

            return true;
        });
    }

    public function rejectRequest(BloodRequest $request, User $admin, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            $request->update([
                'status'      => 'rejected',
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
            ]);

            $this->notificationService->notifyHospitalStatusChange($request, 'rejected', $reason);

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin rejected blood request #{$request->id}");

            return true;
        });
    }

    public function dispenseRequest(BloodRequest $request, User $admin): bool
    {
        return DB::transaction(function () use ($request, $admin) {
            if ($request->status === 'dispensed') {
                throw new \InvalidArgumentException("Blood request #{$request->id} is already dispensed.");
            }

            $group = BloodGroup::where('name', $request->blood_group)->first();
            $groupId = $group ? $group->id : null;

            // Fetch allocated/reserved units for this request or matching blood group sorted by FEFO
            $allocatedUnits = BloodUnit::where('blood_group_id', $groupId)
                ->whereIn('status', ['allocated', 'reserved'])
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->take($request->units_needed)
                ->get();

            if ($allocatedUnits->count() < $request->units_needed) {
                // If not pre-allocated, attempt allocating available units directly sorted by FEFO
                $allocatedUnits = BloodUnit::where('blood_group_id', $groupId)
                    ->where('status', 'available')
                    ->where('expiry_date', '>=', now()->format('Y-m-d'))
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->take($request->units_needed)
                    ->get();

                if ($allocatedUnits->count() < $request->units_needed) {
                    throw new \RuntimeException("Cannot dispense: Insufficient stock for request #{$request->id}.");
                }
            }

            foreach ($allocatedUnits as $unit) {
                $this->bloodUnitService->transitionStatus(
                    unit: $unit,
                    newStatus: 'dispensed',
                    reason: "Dispensed for Blood Request #{$request->id}",
                    actor: $admin
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unit,
                    transactionType: 'dispensed',
                    previousQuantity: $unit->volume_ml,
                    quantityChanged: -$unit->volume_ml,
                    resultingQuantity: 0,
                    reason: "Dispensed for Blood Request #{$request->id}",
                    actor: $admin,
                    referenceType: BloodRequest::class,
                    referenceId: $request->id
                );
            }

            $request->update([
                'status' => 'dispensed',
            ]);

            $this->notificationService->notifyHospitalStatusChange($request, 'dispensed');

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin dispensed blood request #{$request->id}.");

            return true;
        });
    }

    public function notifyMatchingDonors(BloodRequest $request): int
    {
        return $this->notificationService->notifyEligibleDonors($request);
    }
}
