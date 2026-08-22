<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        ]);
        $user->role = 'donor';
        $user->save();

        // Create associated Donor profile record
        $bloodGroup = BloodGroup::first() ?? BloodGroup::firstOrCreate(
            ['name' => 'A+'],
            ['description' => 'A positive']
        );

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
        ]);

        event(new Registered($user));

        Auth::login($user);

        return Inertia::location(route('donor.dashboard'));
    }
}
