<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $token = Str::random(80);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'api_token' => $token,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function loginWithGoogle(Request $request)
    {
        $validated = $request->validate([
            'id_token' => 'required|string',
        ]);

        // Verify token with Google via OAuth2 TokenInfo endpoint
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $validated['id_token'],
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $payload = $response->json();
        
        // Verify audience (must match GOOGLE_CLIENT_ID)
        $aud = $payload['aud'] ?? null;
        if ($aud !== config('services.google.client_id')) {
            return response()->json(['message' => 'Token audience mismatch'], 401);
        }

        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'Google User';
        $googleId = $payload['sub'] ?? null;
        $avatar = $payload['picture'] ?? null;

        if (!$email) {
            return response()->json(['message' => 'Email not provided by Google'], 400);
        }

        // Find existing user or create new one
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(Str::random(16)), // dummy password
                'google_id' => $googleId,
                'google_avatar' => $avatar,
            ]
        );

        // Update google fields if user existed but fields missing or changed
        if ($user->google_id !== $googleId || $user->google_avatar !== $avatar) {
            $user->google_id = $googleId;
            $user->google_avatar = $avatar;
            $user->save();
        }

        // Generate API token
        $token = Str::random(80);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'message' => 'Login dengan Google berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah!'
            ], 401);
        }

        $token = Str::random(80);
        $user->api_token = $token;
        $user->save();

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return response()->json([
            'message' => 'Logout berhasil!'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
