<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodInventory;
use App\Models\BloodGroup;
use App\Models\Donor;
use Illuminate\Http\Request;

class BloodInventoryController extends Controller
{
    public function index()
    {
        $inventory = BloodInventory::with(['bloodGroup', 'donor.user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $summary = BloodInventory::selectRaw('blood_groups.name as blood_group, SUM(blood_inventory.quantity) as total_quantity')
            ->join('blood_groups', 'blood_inventory.blood_group_id', '=', 'blood_groups.id')
            ->where('blood_inventory.status', 'available')
            ->where('blood_inventory.expiry_date', '>', now())
            ->groupBy('blood_groups.name')
            ->get();
            
        return view('admin.inventory.index', [
            'inventory' => $inventory,
            'summary' => $summary
        ]);
    }

    public function create()
    {
        $bloodGroups = BloodGroup::all();
        
        return view('admin.inventory.create', [
            'bloodGroups' => $bloodGroups
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'blood_group_id' => 'required|exists:blood_groups,id',
            'quantity' => 'required|integer|min:1',
            'collection_date' => 'required|date',
            'expiry_date' => 'required|date|after:collection_date',
            'location' => 'required|string|max:255',
            'donor_id' => 'nullable|exists:donors,id',
        ]);

        BloodInventory::create([
            'blood_group_id' => $request->blood_group_id,
            'quantity' => $request->quantity,
            'collection_date' => $request->collection_date,
            'expiry_date' => $request->expiry_date,
            'location' => $request->location,
            'donor_id' => $request->donor_id,
            'units_available' => 1, // 1 unit per record
            'units_requested' => 0,
            'status' => 'available'
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Blood inventory added successfully.');
    }

    public function show($id)
    {
        $inventory = BloodInventory::findOrFail($id);
            
        return view('admin.inventory.show', [
            'inventory' => $inventory
        ]);
    }

    public function edit($id)
    {
        $inventory = BloodInventory::with(['bloodGroup', 'donor.user'])->findOrFail($id);
        $bloodGroups = BloodGroup::all();
        $donors = Donor::with(['user', 'bloodGroup'])->where('status', 'active')->get();
        
        return view('admin.inventory.edit', [
            'inventory' => $inventory,
            'bloodGroups' => $bloodGroups,
            'donors' => $donors
        ]);
    }

    public function update(Request $request, $id)
    {
        $inventory = BloodInventory::findOrFail($id);
        
        $request->validate([
            'blood_group_id' => 'required|exists:blood_groups,id',
            'quantity' => 'required|integer|min:1',
            'collection_date' => 'required|date',
            'expiry_date' => 'required|date|after:collection_date',
            'location' => 'required|string|max:255',
            'status' => 'required|in:available,expired,used,reserved',
            'donor_id' => 'nullable|exists:donors,id',
        ]);

        $inventory->update([
            'blood_group_id' => $request->blood_group_id,
            'quantity' => $request->quantity,
            'collection_date' => $request->collection_date,
            'expiry_date' => $request->expiry_date,
            'location' => $request->location,
            'status' => $request->status,
            'donor_id' => $request->donor_id,
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Blood inventory updated successfully.');
    }

    public function destroy($id)
    {
        $inventory = BloodInventory::findOrFail($id);
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Blood inventory record deleted successfully.');
    }
}
