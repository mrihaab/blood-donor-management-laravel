<?php

namespace App\Services;

use App\Models\BloodGroup;
use App\Models\BloodInventory;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class BloodInventoryService
{
    public function getInventoryOverview()
    {
        $bloodGroups = BloodGroup::all();

        return $bloodGroups->map(function ($group) {
            $availableUnits = BloodInventory::where('blood_group_id', $group->id)
                ->where('status', 'available')
                ->where('expiry_date', '>', now())
                ->sum('units_available');

            $requestedUnits = BloodInventory::where('blood_group_id', $group->id)
                ->where('status', 'reserved')
                ->sum('units_requested');

            return [
                'blood_group_id' => $group->id,
                'blood_group' => $group->name,
                'units_available' => (int) $availableUnits,
                'units_requested' => (int) $requestedUnits,
                'last_updated' => now()->format('M d, Y'),
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

    public function reserveUnits(int $bloodGroupId, int $unitsToReserve): bool
    {
        return DB::transaction(function () use ($bloodGroupId, $unitsToReserve) {
            $inventoryItems = BloodInventory::where('blood_group_id', $bloodGroupId)
                ->where('status', 'available')
                ->where('expiry_date', '>', now())
                ->where('units_available', '>', 0)
                ->lockForUpdate()
                ->get();

            $totalAvailable = $inventoryItems->sum('units_available');

            if ($totalAvailable < $unitsToReserve) {
                return false;
            }

            $remainingNeeded = $unitsToReserve;

            foreach ($inventoryItems as $item) {
                if ($remainingNeeded <= 0) break;

                $deduct = min($item->units_available, $remainingNeeded);
                $item->decrement('units_available', $deduct);
                $item->increment('units_requested', $deduct);

                if ($item->fresh()->units_available <= 0) {
                    $item->update(['status' => 'reserved']);
                }

                $remainingNeeded -= $deduct;
            }

            return true;
        });
    }
}
