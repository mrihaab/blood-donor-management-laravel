<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('hospital');

        // Apply filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15);
        $hospitals = Hospital::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'hospitals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,donor,hospital',
            'hospital_id' => 'nullable|required_if:role,hospital|exists:hospitals,id',
            'status' => 'required|in:active,inactive,blocked'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'hospital_id' => $request->role === 'hospital' ? $request->hospital_id : null,
            'email_verified_at' => now(),
        ]);
        $user->role = $request->role;
        $user->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Created user account for {$user->email} with role {$user->role}");

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,donor,hospital',
            'hospital_id' => 'nullable|required_if:role,hospital|exists:hospitals,id',
            'status' => 'required|in:active,inactive,blocked',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;

        // Prevent changing master admin role
        if ($user->email !== $masterAdminEmail) {
            $user->role = $request->role;
        }

        if ($request->role === 'hospital') {
            $user->hospital_id = $request->hospital_id;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Updated user account for {$user->email}");

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');

        // Prevent deletion of master admin account or self
        if ($user->email === $masterAdminEmail || $user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'The master admin account cannot be deleted.');
        }

        // Check if user has related data
        $hasDonations = $user->role === 'donor' && 
            (\App\Models\Donation::where('donor_id', $user->id)->exists() ||
             \App\Models\BloodRequest::where('user_id', $user->id)->exists());

        if ($hasDonations) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user with existing donations or requests.');
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Deleted user account for {$user->email}");

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleStatus(User $user)
    {
        $masterAdminEmail = env('ADMIN_EMAIL', 'admin@example.com');

        if ($user->email === $masterAdminEmail) {
            return redirect()->route('admin.users.index')
                ->with('error', 'The master admin account status cannot be modified.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Toggled user status for {$user->email} to {$newStatus}");

        return redirect()->route('admin.users.index')
            ->with('success', 'User status updated successfully!');
    }
}
