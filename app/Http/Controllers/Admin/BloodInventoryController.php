<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\BloodUnit;
use App\Models\BloodComponent;
use App\Services\BloodInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $currentTab = $request->input('tab', 'grouped'); // 'grouped' or 'detailed'
        $sourceFilter = $request->input('source', 'all'); // 'all', 'donor', 'direct'
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 25;
        }

        // Default status filter to 'available' unless explicitly provided
        $statusFilter = $request->has('status') ? $request->input('status') : 'available';

        $inventoryOverview = $this->inventoryService->getInventoryOverview();
        $groupedInventory = $this->inventoryService->getGroupedInventoryStock();
        
        $query = BloodUnit::with(['bloodGroup', 'component', 'donor.user']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('unit_number', 'like', "%{$search}%");
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->input('blood_group_id'));
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Apply Origin / Source filter
        if ($sourceFilter === 'donor') {
            $query->whereNotNull('donor_id');
        } elseif ($sourceFilter === 'direct') {
            $query->whereNull('donor_id');
        }

        $bloodUnits = $query->latest()->paginate($perPage)->withQueryString();
        $bloodGroups = BloodGroup::all();
        $components = BloodComponent::all();

        // Calculate live status counts for top filter pills
        $statusCounts = [
            'available' => BloodUnit::where('status', 'available')->count(),
            'dispensed' => BloodUnit::where('status', 'dispensed')->count(),
            'allocated' => BloodUnit::whereIn('status', ['reserved', 'allocated'])->count(),
            'expired'   => BloodUnit::where('status', 'expired')->count(),
            'all'       => BloodUnit::count(),
        ];

        return view('admin.inventory.index', compact(
            'inventoryOverview',
            'groupedInventory',
            'bloodUnits',
            'bloodGroups',
            'components',
            'currentTab',
            'sourceFilter',
            'statusFilter',
            'statusCounts',
            'perPage'
        ));
    }

    public function create()
    {
        $this->authorize('create', BloodUnit::class);
        $bloodGroups = BloodGroup::all();
        $components = BloodComponent::all();
        return view('admin.inventory.create', compact('bloodGroups', 'components'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', BloodUnit::class);

        $request->validate([
            'blood_group_id' => 'required|exists:blood_groups,id',
            'blood_component_id' => 'nullable|exists:blood_components,id',
            'units' => 'required|integer|min:1|max:50',
            'expiration_days' => 'required|integer|min:1|max:365',
        ]);

        $bloodGroup = BloodGroup::findOrFail($request->blood_group_id);

        $componentId = $request->blood_component_id;
        if (empty($componentId)) {
            $defaultComponent = BloodComponent::firstOrCreate(
                ['name' => 'Whole Blood'],
                ['code' => 'WB', 'description' => 'Whole Blood Component', 'shelf_life_days' => 42]
            );
            $componentId = $defaultComponent->id;
        }

        $expirationDate = now()->addDays((int)$request->expiration_days);

        for ($i = 0; $i < (int)$request->units; $i++) {
            $unitNumber = 'BAG-' . strtoupper(str_replace(['+', '-'], ['P', 'N'], $bloodGroup->name)) . '-' . strtoupper(Str::random(6));
            BloodUnit::create([
                'unit_number' => $unitNumber,
                'blood_group_id' => $bloodGroup->id,
                'component_id' => $componentId,
                'collection_date' => now()->format('Y-m-d'),
                'expiry_date' => $expirationDate->format('Y-m-d'),
                'status' => 'available',
                'storage_location' => 'Central Blood Bank Storage Room A',
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->log("Manually ingested {$request->units} units of {$bloodGroup->name} into central inventory");

        return redirect()->route('admin.inventory.index', ['tab' => 'grouped'])
            ->with('success', "Successfully added {$request->units} blood unit bag(s) of {$bloodGroup->name} to inventory.");
    }

    public function show(BloodUnit $inventory)
    {
        $this->authorize('view', $inventory);

        $inventory->load(['bloodGroup', 'component', 'donor.user', 'inventoryTransactions.user']);

        return view('admin.inventory.show', ['unit' => $inventory]);
    }

    public function destroy(BloodUnit $inventory)
    {
        $this->authorize('delete', $inventory);
        $unitNumber = $inventory->unit_number;
        $inventory->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Discarded/deleted blood unit bag {$unitNumber}");

        return redirect()->route('admin.inventory.index')
            ->with('success', "Blood unit bag {$unitNumber} discarded/deleted successfully.");
    }

    public function lowStockAlerts()
    {
        $lowStockAlerts = $this.inventoryService->getLowStockAlerts();
        return view('admin.inventory.low-stock', compact('lowStockAlerts'));
    }
}
