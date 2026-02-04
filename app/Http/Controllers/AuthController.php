<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        $response= 'ok';
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

        // 🔐 Supprimer anciens tokens
        $user->tokens()->delete();

        // 🔑 Créer le token
        $token =$user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
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



}
