<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HospitalAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Hospital::class);

        $query = Hospital::withCount(['bloodRequests', 'patients', 'users']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $hospitals = $query->latest()->paginate(15)->withQueryString();

        return view('admin.hospitals.index', compact('hospitals'));
    }

    public function create()
    {
        $this->authorize('create', Hospital::class);
        return view('admin.hospitals.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Hospital::class);

        $request->validate([
            // Hospital Information
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:hospitals,license_number',
            'email' => 'required|email|max:255|unique:hospitals,email',
            'contact_phone' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'status' => 'required|in:active,inactive',

            // Primary Staff Account Information
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request, &$hospital, &$staffUser) {
            $hospital = Hospital::create([
                'name' => $request->name,
                'license_number' => $request->license_number,
                'email' => $request->email,
                'contact_phone' => $request->contact_phone,
                'contact_person' => $request->staff_name,
                'city' => $request->city,
                'state' => $request->city,
                'address' => $request->address,
                'status' => $request->status,
            ]);

            $staffUser = new User([
                'name' => $request->staff_name,
                'email' => $request->staff_email,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'hospital_id' => $hospital->id,
                'email_verified_at' => now(),
            ]);
            $staffUser->role = 'hospital';
            $staffUser->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($hospital)
                ->log("Registered new hospital {$hospital->name} and staff account {$staffUser->email}");
        });

        return redirect()->route('admin.hospitals.index')
            ->with('success', "Hospital '{$request->name}' and staff account created successfully!");
    }

    public function show(Hospital $hospital)
    {
        $this->authorize('view', $hospital);

        $hospital->load(['users', 'patients', 'bloodRequests' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('admin.hospitals.show', compact('hospital'));
    }

    public function edit(Hospital $hospital)
    {
        $this->authorize('update', $hospital);
        return view('admin.hospitals.edit', compact('hospital'));
    }

    public function update(Request $request, Hospital $hospital)
    {
        $this->authorize('update', $hospital);

        $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255|unique:hospitals,license_number,' . $hospital->id,
            'email' => 'required|email|max:255|unique:hospitals,email,' . $hospital->id,
            'contact_phone' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $hospital->update($request->only([
            'name', 'license_number', 'email', 'contact_phone', 'city', 'address', 'status'
        ]));

        activity()
            ->causedBy(auth()->user())
            ->performedOn($hospital)
            ->log("Updated hospital details for {$hospital->name}");

        return redirect()->route('admin.hospitals.index')
            ->with('success', "Hospital '{$hospital->name}' updated successfully!");
    }
}
