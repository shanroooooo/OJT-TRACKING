<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ---------------------------------------------------------------
    // Public — no token required
    // ---------------------------------------------------------------

    /**
     * POST /api/auth/register
     *
     * Allowed roles on registration: student, supervisor
     * Admin accounts must be seeded directly — not self-registered.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['sometimes', 'in:student,supervisor'], // admin cannot self-register
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'student',
        ]);

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'message' => 'Your account has been deactivated. Contact the administrator.',
            ], 403);
        }

        // Single-session: revoke old tokens before issuing a new one
        $user->tokens()->delete();

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    // ---------------------------------------------------------------
    // Protected — requires valid Sanctum token
    // ---------------------------------------------------------------

    /**
     * GET /api/user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /**
     * Shape the user payload returned in responses.
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
        ];
    }
}
