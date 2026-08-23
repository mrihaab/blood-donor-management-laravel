<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDonorRequest;
use App\Http\Requests\Admin\UpdateDonorRequest;
use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\User;
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

    public function store(StoreDonorRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'donor',
            'status' => 'active',
        ]);

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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("New donor '{$user->name}' was created by admin");

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

    public function update(UpdateDonorRequest $request, $id)
    {
        $user = User::where('role', 'donor')->findOrFail($id);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

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

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Donor '{$user->name}' was updated by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'donor')->findOrFail($id);
        $userName = $user->name;
        
        $user->delete();
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Donor '{$userName}' was deleted by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor deleted successfully.');
    }
}
