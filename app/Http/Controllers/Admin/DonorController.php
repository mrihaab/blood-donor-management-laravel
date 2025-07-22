<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\User;
use App\Models\BloodGroup;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class DonorController extends Controller
{
    public function index()
    {
        $donors = User::where('role', 'donor')
            ->with(['donor.bloodGroup'])
            ->get();
        return view('admin.donors.index', ['donors' => $donors]);
    }

    public function create()
    {
        $bloodGroups = BloodGroup::all();
        return view('admin.donors.create', ['bloodGroups' => $bloodGroups]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:10',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'donor',
        ]);

        // Create donor profile
        Donor::create([
            'user_id' => $user->id,
            'blood_group_id' => $request->blood_group_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
        ]);

        // Log activity
        ActivityLog::logActivity("New donor '{$user->name}' was created by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor created successfully.');
    }

    public function show($id)
    {
        $donor = User::where('role', 'donor')
            ->with(['donor.bloodGroup', 'donor.donations'])
            ->findOrFail($id);
        
        return view('admin.donors.show', ['donor' => $donor]);
    }

    public function edit($id)
    {
        $donor = User::where('role', 'donor')
            ->with('donor.bloodGroup')
            ->findOrFail($id);
        $bloodGroups = BloodGroup::all();
        
        return view('admin.donors.edit', [
            'donor' => $donor,
            'bloodGroups' => $bloodGroups
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'donor')->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'blood_group_id' => 'required|exists:blood_groups,id',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:10',
        ]);

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update donor profile
        $user->donor->update([
            'blood_group_id' => $request->blood_group_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
        ]);

        // Log activity
        ActivityLog::logActivity("Donor '{$user->name}' was updated by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'donor')->findOrFail($id);
        $userName = $user->name;
        
        $user->delete(); // This will cascade delete the donor profile
        
        // Log activity
        ActivityLog::logActivity("Donor '{$userName}' was deleted by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor deleted successfully.');
    }
}
