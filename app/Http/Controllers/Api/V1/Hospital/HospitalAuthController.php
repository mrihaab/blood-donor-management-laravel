<?php

namespace App\Http\Controllers\Api\V1\Hospital;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Hospital\HospitalLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HospitalAuthController extends Controller
{
    /**
     * Authenticate hospital user and issue a Sanctum token with hospital-mobile ability.
     */
    public function login(HospitalLoginRequest $request): JsonResponse
    {
        $normalizedEmail = Str::lower(trim($request->validated('email')));
        $password = $request->validated('password');
        $deviceName = $request->validated('device_name');

        // Resolve user
        $user = User::with('hospital')->where('email', $normalizedEmail)->first();

        // Verify password before revealing role/status/verification eligibility
        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Enforce hospital eligibility after password verification
        if (
            $user->role !== 'hospital' ||
            $user->status !== 'active' ||
            ! $user->hasVerifiedEmail() ||
            ! $user->hospital_id ||
            ! $user->hospital ||
            $user->hospital->status !== 'active'
        ) {
            return response()->json([
                'message' => 'Access denied. Account is ineligible.',
            ], 403);
        }

        // Issue Sanctum token with explicit hospital-mobile ability
        $token = $user->createToken($deviceName, ['hospital-mobile'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $this->formatUserResponse($user),
        ]);
    }

    /**
     * Get profile of authenticated hospital user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('hospital');

        return response()->json([
            'user' => $this->formatUserResponse($user),
        ]);
    }

    /**
     * Logout current device token.
     */
    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout all device tokens for user.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Format minimal approved user & hospital profile payload.
     */
    protected function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'hospital' => $user->hospital ? [
                'id' => $user->hospital->id,
                'name' => $user->hospital->name,
                'license_number' => $user->hospital->license_number,
                'status' => $user->hospital->status,
            ] : null,
        ];
    }
}
