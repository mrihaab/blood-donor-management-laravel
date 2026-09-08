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
                'attendant_name'   => $data['attendant_name'] ?? null,
                'attendant_phone'  => $data['attendant_phone'] ?? null,
                'ward_name'        => $data['ward_name'] ?? null,
                'room_number'      => $data['room_number'] ?? null,
                'bed_number'       => $data['bed_number'] ?? null,
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

    public function getCompatibleABOArray(string $requestedGroup): array
    {
        $requestedGroup = strtoupper(trim($requestedGroup));
        return match ($requestedGroup) {
            'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
            'AB-' => ['AB-', 'A-', 'B-', 'O-'],
            'A+'  => ['A+', 'A-', 'O+', 'O-'],
            'A-'  => ['A-', 'O-'],
            'B+'  => ['B+', 'B-', 'O+', 'O-'],
            'B-'  => ['B-', 'O-'],
            'O+'  => ['O+', 'O-'],
            'O-'  => ['O-'],
            default => [$requestedGroup, 'O-'],
        };
    }

    public function getRecommendedCompatibleUnit(BloodRequest $request): ?array
    {
        $compatibleGroups = $this->getCompatibleABOArray($request->blood_group);

        foreach ($compatibleGroups as $groupName) {
            $group = BloodGroup::where('name', $groupName)->first();
            $groupId = $group ? $group->id : null;

            $query = BloodUnit::with(['bloodGroup', 'donor.user'])
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->orderBy('expiry_date', 'asc');

            $unit = null;
            if ($groupId) {
                $unit = (clone $query)->where('blood_group_id', $groupId)->first();
            }
            if (!$unit) {
                $unit = (clone $query)->where('blood_group', $groupName)->first();
            }

            if ($unit) {
                $matchType = ($groupName === strtoupper(trim($request->blood_group))) ? 'exact' : (($groupName === 'O-') ? 'universal' : 'compatible');
                return [
                    'unit' => $unit,
                    'group' => $groupName,
                    'match_type' => $matchType,
                ];
            }
        }

        return null;
    }

    public function getRecommendedFefoUnit(BloodRequest $request): ?BloodUnit
    {
        $compat = $this->getRecommendedCompatibleUnit($request);
        return $compat['unit'] ?? null;
    }

    public function dispenseUniversalFallbackRequest(BloodRequest $request, User $admin): bool
    {
        return DB::transaction(function () use ($request, $admin) {
            if ($request->status === 'dispensed') {
                throw new \InvalidArgumentException("Blood request #{$request->id} is already dispensed.");
            }

            $oNegGroup = BloodGroup::where('name', 'O-')->first();
            $oNegGroupId = $oNegGroup ? $oNegGroup->id : null;

            $unit = BloodUnit::where(function($q) use ($oNegGroupId) {
                    if ($oNegGroupId) {
                        $q->where('blood_group_id', $oNegGroupId);
                    }
                    $q->orWhere('blood_group', 'O-');
                })
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$unit) {
                throw new \RuntimeException("Cannot execute Universal Fallback: No available O- Negative blood bags in central inventory.");
            }

            $this->bloodUnitService->transitionStatus(
                unit: $unit,
                newStatus: 'dispensed',
                reason: "Emergency Universal O- Fallback Issue for Request #{$request->id} (Requested: {$request->blood_group})",
                actor: $admin
            );

            $this->transactionService->logTransaction(
                bloodUnit: $unit,
                transactionType: 'dispensed',
                previousQuantity: $unit->volume_ml,
                quantityChanged: -$unit->volume_ml,
                resultingQuantity: 0,
                reason: "Emergency Universal O- Fallback Issue for Request #{$request->id}",
                actor: $admin,
                referenceType: BloodRequest::class,
                referenceId: $request->id
            );

            $reasonNote = ($request->reason ? $request->reason . " | " : "") . "Fulfilled via Universal O- Fallback Bag #{$unit->unit_number} (Storage: " . ($unit->storage_location ?? 'Main Refrigerator - Shelf A') . ")";

            $request->update([
                'status'      => 'dispensed',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'reason'      => $reasonNote,
            ]);

            $this->notificationService->notifyHospitalStatusChange($request, 'dispensed');

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin emergency dispensed Universal O- Fallback unit #{$unit->unit_number} for request #{$request->id} ({$request->blood_group}).");

            return true;
        });
    }

    /**
     * Get real-time AI inventory balance status across all 8 blood groups.
     */
    public function getInventoryBalanceStatus(): array
    {
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $available = BloodUnit::where('status', 'available')
            ->where('expiry_date', '>=', now()->format('Y-m-d'))
            ->selectRaw('blood_group, COUNT(*) as cnt')
            ->groupBy('blood_group')
            ->pluck('cnt', 'blood_group')
            ->toArray();

        $imbalanced = [];
        foreach ($groups as $g) {
            $c = $available[$g] ?? 0;
            if ($c < 2) {
                $imbalanced[] = "$g ($c units)";
            }
        }

        if (empty($imbalanced)) {
            return [
                'is_balanced' => true,
                'badge_class' => 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'text' => '🟢 All 8 Blood Groups Balanced',
                'details' => 'Optimal reserve across all blood types'
            ];
        }

        return [
            'is_balanced' => false,
            'badge_class' => 'bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 animate-pulse',
            'text' => '🚨 Imbalance: ' . implode(', ', array_slice($imbalanced, 0, 2)) . (count($imbalanced) > 2 ? ' +more' : ''),
            'details' => 'Critical low stock detected in ' . count($imbalanced) . ' group(s)'
        ];
    }
}

