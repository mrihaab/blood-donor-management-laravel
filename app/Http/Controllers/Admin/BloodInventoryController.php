<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\BloodUnit;
use App\Models\BloodComponent;
use App\Services\BloodInventoryService;
use Illuminate\Http\Request;

class BloodInventoryController extends Controller
{
    protected BloodInventoryService $inventoryService;

    public function __construct(BloodInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', BloodUnit::class);

        $inventoryOverview = $this->inventoryService->getInventoryOverview();
        
        $query = BloodUnit::with(['bloodGroup', 'component', 'donor.user']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('unit_number', 'like', "%{$search}%");
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->input('blood_group_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $bloodUnits = $query->latest()->paginate(15)->withQueryString();
        $bloodGroups = BloodGroup::all();
        $components = BloodComponent::all();

        return view('admin.inventory.index', compact('inventoryOverview', 'bloodUnits', 'bloodGroups', 'components'));
    }

    public function show(BloodUnit $inventory)
    {
        $this->authorize('view', $inventory);

        $inventory->load(['bloodGroup', 'component', 'donor.user', 'inventoryTransactions.user']);

        return view('admin.inventory.show', ['unit' => $inventory]);
    }

    public function lowStockAlerts()
    {
        $lowStockAlerts = $this->inventoryService->getLowStockAlerts();
        return view('admin.inventory.low-stock', compact('lowStockAlerts'));
    }
}
