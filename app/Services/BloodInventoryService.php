<?php

namespace App\Services;

use App\Models\BloodGroup;
use App\Models\BloodUnit;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BloodInventoryService
{
    protected BloodUnitService $bloodUnitService;
    protected InventoryTransactionService $transactionService;

    public function __construct(
        BloodUnitService $bloodUnitService,
        InventoryTransactionService $transactionService
    ) {
        $this->bloodUnitService = $bloodUnitService;
        $this->transactionService = $transactionService;
    }

    /**
     * Overview derived directly from BloodUnit as single source of truth.
     */
    public function getInventoryOverview()
    {
        $bloodGroups = BloodGroup::all();

        return $bloodGroups->map(function ($group) {
            // Count physical BloodUnit bags that are available AND not expired
            $availableUnits = BloodUnit::where('blood_group_id', $group->id)
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->count();

            $reservedUnits = BloodUnit::where('blood_group_id', $group->id)
                ->whereIn('status', ['reserved', 'allocated'])
                ->count();

            return [
                'blood_group_id'  => $group->id,
                'blood_group'     => $group->name,
                'units_available' => (int) $availableUnits,
                'units_requested' => (int) $reservedUnits,
                'last_updated'    => now()->format('M d, Y'),
            ];
        });
    }

    public function getGroupedInventoryStock()
    {
        $bloodGroups = BloodGroup::all();
        $threshold = SystemSetting::get('low_stock_threshold', 10);

        return $bloodGroups->map(function ($group) use ($threshold) {
            $available = BloodUnit::where('blood_group_id', $group->id)
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'));

            $availableCount = (clone $available)->count();

            $donorIntakeCount = (clone $available)->whereNotNull('donor_id')->count();
            $directIntakeCount = (clone $available)->whereNull('donor_id')->count();

            $expiringSoonCount = (clone $available)
                ->whereBetween('expiry_date', [now()->format('Y-m-d'), now()->addDays(7)->format('Y-m-d')])
                ->count();

            $reservedCount = BloodUnit::where('blood_group_id', $group->id)
                ->whereIn('status', ['reserved', 'allocated'])
                ->count();

            return [
                'blood_group_id'      => $group->id,
                'blood_group'         => $group->name,
                'units_available'     => (int) $availableCount,
                'units_reserved'      => (int) $reservedCount,
                'donor_intake_count'  => (int) $donorIntakeCount,
                'direct_intake_count' => (int) $directIntakeCount,
                'expiring_soon'       => (int) $expiringSoonCount,
                'is_low_stock'        => $availableCount < $threshold,
            ];
        });
    }

    public function getLowStockAlerts()
    {
        $threshold = SystemSetting::get('low_stock_threshold', 10);
        
        return $this->getInventoryOverview()->filter(function ($item) use ($threshold) {
            return $item['units_available'] < $threshold;
        })->values();
    }

    /**
     * Reserve physical blood units atomically with FEFO (First Expire, First Out) selection & pessimistic row locking.
     */
    public function reserveUnits(int $bloodGroupId, int $unitsToReserve, ?User $actor = null, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($bloodGroupId, $unitsToReserve, $actor, $reason) {
            // Fetch eligible units sorted by FEFO (earliest expiry first) with pessimistic lock
            $eligibleUnits = BloodUnit::where('blood_group_id', $bloodGroupId)
                ->where('status', 'available')
                ->where('expiry_date', '>=', now()->format('Y-m-d'))
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->take($unitsToReserve)
                ->get();

            if ($eligibleUnits->count() < $unitsToReserve) {
                return false;
            }

            foreach ($eligibleUnits as $unit) {
                $this->bloodUnitService->transitionStatus(
                    unit: $unit,
                    newStatus: 'reserved',
                    reason: $reason ?? 'Inventory reservation for blood request',
                    actor: $actor
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unit,
                    transactionType: 'reserved',
                    previousQuantity: $unit->volume_ml,
                    quantityChanged: 0,
                    resultingQuantity: $unit->volume_ml,
                    reason: $reason ?? 'Inventory reservation',
                    actor: $actor
                );
            }

            return true;
        });
    }

    /**
     * Process expired physical BloodUnit records and update status & log audit transactions.
     * Idempotent operation.
     */
    public function processExpiredUnits(): int
    {
        return DB::transaction(function () {
            $expiredUnits = BloodUnit::where('status', 'available')
                ->where('expiry_date', '<', now()->format('Y-m-d'))
                ->lockForUpdate()
                ->get();

            $count = 0;
            foreach ($expiredUnits as $unit) {
                $this->bloodUnitService->transitionStatus(
                    unit: $unit,
                    newStatus: 'expired',
                    reason: 'Automated expiration scan',
                    actor: null
                );

                $this->transactionService->logTransaction(
                    bloodUnit: $unit,
                    transactionType: 'expired',
                    previousQuantity: $unit->volume_ml,
                    quantityChanged: -$unit->volume_ml,
                    resultingQuantity: 0,
                    reason: 'Unit reached shelf life expiration date',
                    actor: null
                );
                $count++;
            }

            return $count;
        });
    }

    /**
     * Alias for processExpiredUnits to satisfy domain service contract.
     */
    public function processExpiries(): int
    {
        return $this->processExpiredUnits();
    }
}
