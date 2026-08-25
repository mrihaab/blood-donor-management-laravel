<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $user->email_verified_at = now();
        $user->role = 'donor';
        $user->save();

        // Ensure default blood groups exist if DB unseeded
        if (BloodGroup::count() === 0) {
            $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            foreach ($bloodGroups as $group) {
                BloodGroup::firstOrCreate(
                    ['name' => $group],
                    ['description' => $group . ' blood group']
                );
            }
        }

        $bloodGroup = BloodGroup::first();

        // Create associated Donor profile record
        $user->donor()->create([
            'blood_group_id' => $bloodGroup->id,
            'contact_number' => 'Not Provided',
            'address' => 'Please complete your profile address',
            'city' => 'Not Provided',
            'state' => 'Not Provided',
            'zip_code' => '00000',
            'gender' => 'other',
            'date_of_birth' => '2000-01-01',
            'is_available' => true,
            'status' => 'active',
        ]);

        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::warning('Email verification notification skipped on registration: ' . $e->getMessage());
        }

        Auth::login($user);

        return Inertia::location(route('donor.dashboard'));
    }
}
