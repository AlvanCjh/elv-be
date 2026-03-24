<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => ['nullable', Password::defaults()],
        ]);

        if ($request->name) {
            $user->name = $request->name;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $this->formatUser($user)
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->photo_url && !str_starts_with($user->photo_url, 'http')) {
             Storage::disk('public')->delete(str_replace('/storage/', '', $user->photo_url));
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->photo_url = Storage::url($path);
        $user->save();

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photoURL' => asset($user->photo_url)
        ]);
    }

    private function formatUser($user)
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'displayName' => $user->name,
            'email' => $user->email,
            'role' => [$user->role ?? 'member'],
            'photoURL' => $user->photo_url ? asset($user->photo_url) : '',
        ];
    }
}
