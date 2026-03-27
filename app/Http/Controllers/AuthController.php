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
        $token = $request->user()?->currentAccessToken();
        $tokenClass = $token ? get_class($token) : null;
        $hasExpiresAt = $token && property_exists($token, 'expires_at');
        @file_put_contents(
            base_path('.cursor/debug-4769f4.log'),
            json_encode([
                'sessionId' => '4769f4',
                'runId' => 'run1',
                'hypothesisId' => 'H5',
                'location' => 'AuthController.php:validateToken',
                'message' => 'validateToken backend entry',
                'data' => [
                    'userId' => $request->user()?->id,
                    'tokenClass' => $tokenClass,
                    'hasBearerToken' => !empty($plainTextToken),
                    'matchedBearerTokenInDb' => (bool) $matchedToken,
                    'hasExpiresAtProperty' => $hasExpiresAt,
                    'isApiRoute' => $request->is('api/*'),
                    'acceptHeader' => $request->header('Accept'),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ]) . PHP_EOL,
            FILE_APPEND
        );

        // Vérification stricte du Bearer token (pas la session/cookie).
        if (!$plainTextToken || !$matchedToken) {
            @file_put_contents(
                base_path('.cursor/debug-4769f4.log'),
                json_encode([
                    'sessionId' => '4769f4',
                    'runId' => 'run1',
                    'hypothesisId' => 'H6',
                    'location' => 'AuthController.php:validateToken',
                    'message' => 'Strict bearer validation failed',
                    'data' => [
                        'hasBearerToken' => !empty($plainTextToken),
                        'matchedBearerTokenInDb' => (bool) $matchedToken,
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                ]) . PHP_EOL,
                FILE_APPEND
            );
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($matchedToken->expires_at && $matchedToken->expires_at->isPast()) {
            @file_put_contents(
                base_path('.cursor/debug-4769f4.log'),
                json_encode([
                    'sessionId' => '4769f4',
                    'runId' => 'run1',
                    'hypothesisId' => 'H6',
                    'location' => 'AuthController.php:validateToken',
                    'message' => 'Bearer token expired',
                    'data' => [
                        'tokenId' => $matchedToken->id,
                        'expiresAt' => optional($matchedToken->expires_at)->toIso8601String(),
                    ],
                    'timestamp' => round(microtime(true) * 1000),
                ]) . PHP_EOL,
                FILE_APPEND
            );
            return response()->json(['message' => 'Token expired.'], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => $request->user(),
            'expires_at' => $matchedToken?->expires_at,
        ]);
    }



}
