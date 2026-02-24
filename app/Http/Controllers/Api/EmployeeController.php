<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // ===================================================================
    // INDEX — GET /api/employees
    // Retourne la liste de tous les employés avec pagination
    // Supporte : ?company_id=X  |  ?search=nom  |  ?status=active

    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['company', 'position']);

        // Filtrer par entreprise
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filtrer par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Recherche par prénom ou nom
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name',  'LIKE', "%{$search}%")
                  ->orWhere('matricule',  'LIKE', "%{$search}%");
            });
        }

        // Pagination (15 par page par défaut)
        $employees = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status'  => 'success',
            'data'    => $employees->items(),
            'meta'    => [
                'current_page' => $employees->currentPage(),
                'per_page'     => $employees->perPage(),
                'total'        => $employees->total(),
                'last_page'    => $employees->lastPage(),
            ],
        ]);
    }

    // ===================================================================
    // SHOW — GET /api/employees/{employee}
    // Retourne un seul employé avec toutes ses relations

    public function show(Employee $employee): JsonResponse
    {
        $employee->load(['company', 'position']);

        return response()->json([
            'status' => 'success',
            'data'   => $employee,
        ]);
    }

    // ===================================================================
    // STORE — POST /api/employees
    // Crée un nouvel employé

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        // dd("test");
        $employee = Employee::create($request->validated());

        $employee->load(['company', 'position']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Employé créé avec succès.',
            'data'    => $employee,
        ], 201);
    }

    // ===================================================================
    // UPDATE — PUT /api/employees/{employee}
    // Met à jour un employé existant

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        $employee->load(['company', 'position']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Employé mis à jour avec succès.',
            'data'    => $employee,
        ]);
    }

    // ===================================================================
    // DESTROY — DELETE /api/employees/{employee}
    // Supprime un employé

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Employé supprimé avec succès.',
        ]);
    }
}
