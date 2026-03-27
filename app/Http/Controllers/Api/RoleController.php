<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // ===================================================================
    // INDEX — GET /api/roles
    // Retourne la liste de tous les rôles
    // ===================================================================
    public function index(Request $request): JsonResponse
    {
        $roles = Role::withCount('users')->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
            ],
        ]);
    }

    // ===================================================================
    // SHOW — GET /api/roles/{role}
    // Retourne un rôle avec ses utilisateurs
    // ===================================================================
    public function show(Role $role): JsonResponse
    {
        $role->loadCount('users');

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    // ===================================================================
    // STORE — POST /api/roles
    // Crée un nouveau rôle (super_admin uniquement)
    // ===================================================================
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Rôle créé avec succès.',
            'data' => $role,
        ], 201);
    }

    // ===================================================================
    // UPDATE — PUT /api/roles/{role}
    // Met à jour un rôle (super_admin uniquement)
    // ===================================================================
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Rôle mis à jour avec succès.',
            'data' => $role,
        ]);
    }

    // ===================================================================
    // DESTROY — DELETE /api/roles/{role}
    // Supprime un rôle (super_admin uniquement)
    // ===================================================================
    public function destroy(Role $role): JsonResponse
    {
        // Vérifier qu'il n'y a pas d'utilisateurs avec ce rôle
        if ($role->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer ce rôle car il est attribué à des utilisateurs.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rôle supprimé avec succès.',
        ]);
    }

    // ===================================================================
    // SYSTEM ROLES — GET /api/roles/system
    // Retourne les rôles système prédéfinis
    // ===================================================================
    public function systemRoles(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Role::getSystemRoles(),
        ]);
    }
}
