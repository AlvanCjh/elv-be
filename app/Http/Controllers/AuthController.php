<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Handle Registration
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'sometimes|in:member,supervisor',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'] ?? 'member',
        ]);

        $token = $user->createToken('agate_token')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token,
            'message' => 'Registration successful!'
        ], 201);
    }

    // Handle Login
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $fields['email'], 'password' => $fields['password']])) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        $user = Auth::user();

        if ($user->is_blocked) {
            Auth::logout();
            return response()->json(['message' => 'Your account has been blocked. Please contact the supervisor.'], 403);
        }
        $token = $user->createToken('myapptoken')->plainTextToken;

        return response()->json([
            'user' => $this->formatUser($user),
            'token' => $token
        ], 200);
    }

    // Return current authenticated user
    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    // List all users
    public function index()
    {
        return response()->json(User::all()->map(fn($user) => $this->formatUser($user)));
    }

    // Get currently online users (active in last 5 minutes)
    public function onlineUsers()
    {
        $users = User::where('last_seen_at', '>=', now()->subMinutes(5))
            ->get()
            ->map(fn($user) => $this->formatUser($user));

        return response()->json($users);
    }

    // Format user for frontend (Fuse expects 'role' as array)
    private function formatUser(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'displayName' => $user->name,
            'email' => $user->email,
            'role' => [$user->role],  // Fuse expects role as array
            'photoURL' => $user->photo_url ? asset($user->photo_url) : '',
            'isBlocked' => (bool) $user->is_blocked,
        ];
    }
}