<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ProfileController extends Controller
{
    // Show the edit form
    public function edit()
    {
        $user = Auth::user();
        $bloodGroups = BloodGroup::all();
        
        return view('donor.profile.edit', [
            'user' => $user,
            'donor' => optional($user)->donor,
            'bloodGroups' => $bloodGroups,
        ]);
    }

    // Update donor profile info
    public function update(Request $request)
    {
        $user = Auth::user();
        
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
            'health_info' => 'nullable|string',
            'is_available' => 'boolean',
        ]);
        
        // Update user information
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update or create donor profile
        if ($user->donor) {
            $user->donor->update([
                'blood_group_id' => $request->blood_group_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'health_info' => $request->health_info,
                'is_available' => $request->boolean('is_available', true),
            ]);
        } else {
            $user->donor()->create([
                'blood_group_id' => $request->blood_group_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'health_info' => $request->health_info,
                'is_available' => $request->boolean('is_available', true),
            ]);
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}
