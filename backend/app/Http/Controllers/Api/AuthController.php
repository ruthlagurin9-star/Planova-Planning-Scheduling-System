<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Register a regular user.
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        $token = $user->createToken('planova-token')->plainTextToken;

        return response()->json([
            'message' => 'Account registered successfully.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
            'token' => $token,
        ], 201);
    }

    // Log in and issue an API token.
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check(
            $validated['password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Revoke previous tokens for this account.
        $user->tokens()->delete();

        $token = $user->createToken('planova-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
            'token' => $token,
        ]);
    }

    // Log out by revoking the current token.
    public function logout(Request $request)
{
    $token = $request->user()->currentAccessToken();

    if ($token) {
        $token->delete();
    }

    return response()->json([
        'message' => 'Logout successful.'
    ]);
}
}
