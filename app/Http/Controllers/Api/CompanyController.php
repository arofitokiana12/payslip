<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('active', true)
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => $companies->items(),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
                'last_page' => $companies->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'date_creation' => 'nullable|date',
            'nif' => 'nullable|integer',
            'stat' => 'nullable|integer',
            'rcs' => 'nullable|integer',
            'logo' => 'nullable|string|max:255',
            'adress' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'active' => 'boolean'
        ]);

        $company = Company::create($validated);

        return response()->json([
            'message' => 'Entreprise créée avec succès',
            'data' => $company
        ], 201);
    }

    public function show($id)
    {
        $company = Company::findOrFail($id);

        return response()->json([
            'data' => $company
        ]);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'date_creation' => 'nullable|date',
            'nif' => 'nullable|integer',
            'stat' => 'nullable|integer',
            'rcs' => 'nullable|integer',
            'logo' => 'nullable|string|max:255',
            'adress' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'active' => 'boolean'
        ]);

        $company->update($validated);

        return response()->json([
            'message' => 'Entreprise modifiée avec succès',
            'data' => $company
        ]);
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // Vérifier si des utilisateurs ou employés sont liés
        if ($company->users()->count() > 0 || $company->employees()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette entreprise car des utilisateurs ou employés y sont rattachés.'
            ], 409);
        }

        $company->delete();

        return response()->json([
            'message' => 'Entreprise supprimée avec succès'
        ]);
    }
}
