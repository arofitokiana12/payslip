<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    // ===================================================================
    // INDEX — GET /api/positions
    // Supporte filtrer par entreprise : ?company_id=X

    public function index(Request $request): JsonResponse
    {
        $query = Position::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $positions = $query->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data'   => $positions->items(),
            'meta'   => [
                'current_page' => $positions->currentPage(),
                'per_page' => $positions->perPage(),
                'total' => $positions->total(),
                'last_page' => $positions->lastPage(),
            ],
        ]);
    } 

    // ===================================================================
    // SHOW — GET /api/positions/{position}

    public function show(Position $position): JsonResponse
    {
        $position->load('company');

        return response()->json([
            'status' => 'success',
            'data'   => $position,
        ]);
    }

    // ===================================================================
    // STORE — POST /api/positions

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'position_name' => ['required', 'string', 'max:255'],
            'company_id'    => ['required', 'integer', 'exists:companies,company_id'],
        ]);

        $position = Position::create($validated);
        $position->load('company');

        return response()->json([
            'status'  => 'success',
            'message' => 'Poste créé avec succès.',
            'data'    => $position,
        ], 201);
    }

    // ===================================================================
    // UPDATE — PUT /api/positions/{position}

    public function update(Request $request, Position $position): JsonResponse
    {
        $validated = $request->validate([
            'position_name' => ['nullable', 'string', 'max:255'],
            'company_id'    => ['nullable', 'integer', 'exists:companies,company_id'],
        ]);

        $position->update($validated);
        $position->load('company');

        return response()->json([
            'status'  => 'success',
            'message' => 'Poste mis à jour avec succès.',
            'data'    => $position,
        ]);
    }

    // ===================================================================
    // DESTROY — DELETE /api/positions/{position}

    public function destroy(Position $position): JsonResponse
    {
        $position->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Poste supprimé avec succès.',
        ]);
    }
}
