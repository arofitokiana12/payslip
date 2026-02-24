<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ===================================================================
    // INDEX — GET /api/users
    // Retourne la liste des utilisateurs avec filtres
    // Super_admin : voit tous les users
    // Admin : voit uniquement les users de son entreprise
    // ===================================================================
    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $query = User::with(['role', 'company']);

        // Filtrer par entreprise si l'utilisateur n'est pas super_admin
        if (!$currentUser->isSuperAdmin()) {
            $query->where('company_id', $currentUser->company_id);
        }

        // Filtrer par entreprise (query param)
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filtrer par rôle
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filtrer par statut actif
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Recherche
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('user_name', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    // ===================================================================
    // SHOW — GET /api/users/{user}
    // Retourne un utilisateur
    // ===================================================================
    public function show(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Vérifier les permissions
        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n\'avez pas accès à cet utilisateur.',
            ], 403);
        }

        $user->load(['role', 'company']);

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    // ===================================================================
    // STORE — POST /api/users
    // Crée un nouvel utilisateur
    // ===================================================================
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->load(['role', 'company']);

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur créé avec succès.',
            'data' => $user,
        ], 201);
    }

    // ===================================================================
    // UPDATE — PUT /api/users/{user}
    // Met à jour un utilisateur
    // ===================================================================
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Vérifier les permissions
        if (!$currentUser->isAdmin() && $user->id !== $currentUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous n\'avez pas la permission de modifier cet utilisateur.',
            ], 403);
        }

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas modifier un utilisateur d\'une autre entreprise.',
            ], 403);
        }

        $data = $request->validated();

        // Hash le mot de passe si fourni
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->load(['role', 'company']);

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user,
        ]);
    }

    // ===================================================================
    // DESTROY — DELETE /api/users/{user}
    // Supprime un utilisateur (admins uniquement)
    // ===================================================================
    public function destroy(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Empêcher de se supprimer soi-même
        if ($user->id === $currentUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        // Vérifier les permissions
        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas supprimer un utilisateur d\'une autre entreprise.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur supprimé avec succès.',
        ]);
    }

    // ===================================================================
    // ME — GET /api/users/me
    // Retourne l'utilisateur connecté
    // ===================================================================
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['role', 'company']);

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }
}
