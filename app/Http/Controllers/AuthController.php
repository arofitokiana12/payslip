<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function index()
    {
        $response= 'okr';
        return $response;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect',
            ], 401);
        }

        $user = Auth::user();



        // Créer le token avec expiration (si configurée)
        $expirationMinutes = config('sanctum.expiration');
        $expiresAt = $expirationMinutes ? Carbon::now()->addMinutes((int) $expirationMinutes) : null;
        $token = $user->createToken('auth-token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
            'expires_at' => $expiresAt?->toIso8601String(),
            'message' => 'Login successful',

        ]);
    }

    public function logout(Request $request)
    {
        // Vérifiez que $request->user() n'est pas null
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logout réussi'
        ]);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $plainTextToken = $request->bearerToken();
        $matchedToken = $plainTextToken ? PersonalAccessToken::findToken($plainTextToken) : null;

        // Vérification stricte du Bearer token (pas la session/cookie).
        if (!$plainTextToken || !$matchedToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($matchedToken->expires_at && $matchedToken->expires_at->isPast()) {
            return response()->json(['message' => 'Token expired.'], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => $request->user(),
            'expires_at' => $matchedToken?->expires_at,
        ]);
    }



}
