<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDonorRequest;
use App\Http\Requests\Admin\UpdateDonorRequest;
use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DonorController extends Controller
{
    public function index(Request $request)
    {
        $query = Donor::with(['user', 'bloodGroup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('city', 'like', "%{$search}%")
              ->orWhere('contact_number', 'like', "%{$search}%");
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->input('blood_group_id'));
        }

        $donors = $query->latest()->paginate(15)->withQueryString();
            
        return view('admin.donors.index', compact('donors'));
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
            'email_verified_at' => now(),
        ]);

        $donor = Donor::create([
            'user_id' => $user->id,
            'blood_group_id' => $request->blood_group_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state ?? $request->city,
            'zip_code' => $request->zip_code ?? '00000',
            'status' => 'active',
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($donor)
            ->log("New donor '{$user->name}' was created by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor created successfully.');
    }

    public function show($id)
    {
        $donor = Donor::with(['user', 'bloodGroup', 'donations'])->findOrFail($id);
        return view('admin.donors.show', compact('donor'));
    }

    public function edit($id)
    {
        $donor = Donor::with(['user', 'bloodGroup'])->findOrFail($id);
        $bloodGroups = BloodGroup::all();
        return view('admin.donors.edit', compact('donor', 'bloodGroups'));
    }

    public function update(UpdateDonorRequest $request, $id)
    {
        $donor = Donor::with('user')->findOrFail($id);
        
        if ($donor->user) {
            $donor->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }

        $donor->update([
            'blood_group_id' => $request->blood_group_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state ?? $request->city,
            'zip_code' => $request->zip_code ?? '00000',
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($donor)
            ->log("Donor '{$donor->user->name}' was updated by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor updated successfully.');
    }

    public function destroy($id)
    {
        $donor = Donor::with('user')->findOrFail($id);
        $donorName = $donor->user->name ?? 'Donor';
        
        if ($donor->user) {
            $donor->user->delete();
        } else {
            $donor->delete();
        }
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($donor)
            ->log("Donor '{$donorName}' was deleted by admin");

        return redirect()->route('admin.donors.index')
            ->with('success', 'Donor deleted successfully.');
    }
}
