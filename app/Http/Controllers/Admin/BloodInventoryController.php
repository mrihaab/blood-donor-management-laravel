<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\BloodInventory;
use App\Services\BloodInventoryService;
use Illuminate\Http\Request;

class BloodInventoryController extends Controller
{
    protected BloodInventoryService $inventoryService;

    public function __construct(BloodInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $inventoryOverview = $this->inventoryService->getInventoryOverview();
        $inventoryItems = BloodInventory::with(['bloodGroup', 'donor.user'])
            ->latest()
            ->get();

        return view('admin.inventory.index', compact('inventoryOverview', 'inventoryItems'));
    }

    public function create()
    {
        $bloodGroups = BloodGroup::all();
        return view('admin.inventory.create', compact('bloodGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'blood_group_id' => 'required|exists:blood_groups,id',
            'units_available' => 'required|integer|min:1',
            'collection_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'required|date|after:collection_date',
        ]);

        BloodInventory::create([
            'blood_group_id' => $request->blood_group_id,
            'quantity' => $request->units_available * 450,
            'units_available' => $request->units_available,
            'units_requested' => 0,
            'collection_date' => $request->collection_date,
            'expiry_date' => $request->expiry_date,
            'status' => 'available',
        ]);

        activity()
            ->causedBy(auth()->user())
            ->log("Added {$request->units_available} units to blood inventory");

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Stock added successfully.');
    }

    public function lowStockAlerts()
    {
        $lowStockAlerts = $this->inventoryService->getLowStockAlerts();
        return view('admin.inventory.low-stock', compact('lowStockAlerts'));
    }
}
