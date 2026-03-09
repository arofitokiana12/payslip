<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TaxBracket;
use App\Models\SocialContribution;
use App\Models\Holiday;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Récupérer tous les paramètres groupés par catégorie
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('category');

        return response()->json([
            'data' => $settings
        ]);
    }

    /**
     * Récupérer les paramètres d'une catégorie spécifique
     */
    public function getByCategory($category)
    {
        $settings = Setting::where('category', $category)->get();

        return response()->json([
            'data' => $settings
        ]);
    }

    /**
     * Récupérer un paramètre spécifique par sa clé
     */
    public function getByKey($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Paramètre non trouvé'
            ], 404);
        }

        return response()->json([
            'data' => $setting
        ]);
    }

    /**
     * Mettre à jour un paramètre
     */
    public function update(Request $request, $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'message' => 'Paramètre non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'value' => 'required'
        ]);

        $setting->update(['value' => $validated['value']]);

        return response()->json([
            'message' => 'Paramètre mis à jour avec succès',
            'data' => $setting
        ]);
    }

    /**
     * Mettre à jour plusieurs paramètres en une fois
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required'
        ]);

        $updated = [];

        foreach ($validated['settings'] as $settingData) {
            $setting = Setting::where('key', $settingData['key'])->first();

            if ($setting) {
                $setting->update(['value' => $settingData['value']]);
                $updated[] = $setting;
            }
        }

        return response()->json([
            'message' => count($updated) . ' paramètre(s) mis à jour',
            'data' => $updated
        ]);
    }

    /**
     * Réinitialiser tous les paramètres aux valeurs par défaut
     */
    public function reset()
    {
        // Cette fonctionnalité nécessite un seeder ou des valeurs par défaut stockées
        return response()->json([
            'message' => 'Fonctionnalité de réinitialisation à implémenter'
        ]);
    }

    // ==================== TAX BRACKETS ====================

    /**
     * Liste des tranches d'imposition
     */
    public function getTaxBrackets()
    {
        $brackets = TaxBracket::where('active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => $brackets
        ]);
    }

    /**
     * Créer une tranche d'imposition
     */
    public function createTaxBracket(Request $request)
    {
        $validated = $request->validate([
            'min_salary' => 'required|numeric|min:0',
            'max_salary' => 'nullable|numeric|gt:min_salary',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_amount' => 'required|numeric|min:0',
            'order' => 'required|integer',
            'active' => 'sometimes|boolean'
        ]);

        $bracket = TaxBracket::create($validated);

        return response()->json([
            'message' => 'Tranche d\'imposition créée avec succès',
            'data' => $bracket
        ], 201);
    }

    /**
     * Mettre à jour une tranche d'imposition
     */
    public function updateTaxBracket(Request $request, $id)
    {
        $bracket = TaxBracket::findOrFail($id);

        $validated = $request->validate([
            'min_salary' => 'sometimes|numeric|min:0',
            'max_salary' => 'nullable|numeric',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'fixed_amount' => 'sometimes|numeric|min:0',
            'order' => 'sometimes|integer',
            'active' => 'sometimes|boolean'
        ]);

        $bracket->update($validated);

        return response()->json([
            'message' => 'Tranche d\'imposition modifiée avec succès',
            'data' => $bracket
        ]);
    }

    /**
     * Supprimer une tranche d'imposition
     */
    public function deleteTaxBracket($id)
    {
        $bracket = TaxBracket::findOrFail($id);
        $bracket->delete();

        return response()->json([
            'message' => 'Tranche d\'imposition supprimée avec succès'
        ]);
    }

    // ==================== SOCIAL CONTRIBUTIONS ====================

    /**
     * Liste des cotisations sociales
     */
    public function getSocialContributions()
    {
        $contributions = SocialContribution::where('active', true)->get();

        return response()->json([
            'data' => $contributions
        ]);
    }

    /**
     * Créer une cotisation sociale
     */
    public function createSocialContribution(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:social_contributions,code',
            'employee_rate' => 'required|numeric|min:0|max:100',
            'employer_rate' => 'required|numeric|min:0|max:100',
            'ceiling' => 'nullable|numeric|min:0',
            'active' => 'sometimes|boolean',
            'description' => 'nullable|string'
        ]);

        $contribution = SocialContribution::create($validated);

        return response()->json([
            'message' => 'Cotisation sociale créée avec succès',
            'data' => $contribution
        ], 201);
    }

    /**
     * Mettre à jour une cotisation sociale
     */
    public function updateSocialContribution(Request $request, $id)
    {
        $contribution = SocialContribution::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'employee_rate' => 'sometimes|numeric|min:0|max:100',
            'employer_rate' => 'sometimes|numeric|min:0|max:100',
            'ceiling' => 'nullable|numeric|min:0',
            'active' => 'sometimes|boolean',
            'description' => 'nullable|string'
        ]);

        $contribution->update($validated);

        return response()->json([
            'message' => 'Cotisation sociale modifiée avec succès',
            'data' => $contribution
        ]);
    }

    /**
     * Supprimer une cotisation sociale
     */
    public function deleteSocialContribution($id)
    {
        $contribution = SocialContribution::findOrFail($id);
        $contribution->delete();

        return response()->json([
            'message' => 'Cotisation sociale supprimée avec succès'
        ]);
    }

    // ==================== HOLIDAYS ====================

    /**
     * Liste des jours fériés
     */
    public function getHolidays(Request $request)
    {
        $query = Holiday::query();

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        $holidays = $query->orderBy('date')->get();

        return response()->json([
            'data' => $holidays
        ]);
    }

    /**
     * Créer un jour férié
     */
    public function createHoliday(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'year' => 'required|integer|min:2000|max:2100',
            'recurring' => 'sometimes|boolean'
        ]);

        $holiday = Holiday::create($validated);

        return response()->json([
            'message' => 'Jour férié créé avec succès',
            'data' => $holiday
        ], 201);
    }

    /**
     * Mettre à jour un jour férié
     */
    public function updateHoliday(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'year' => 'sometimes|integer|min:2000|max:2100',
            'recurring' => 'sometimes|boolean'
        ]);

        $holiday->update($validated);

        return response()->json([
            'message' => 'Jour férié modifié avec succès',
            'data' => $holiday
        ]);
    }

    /**
     * Supprimer un jour férié
     */
    public function deleteHoliday($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'message' => 'Jour férié supprimé avec succès'
        ]);
    }

    /**
     * Générer les jours fériés récurrents pour une année
     */
    public function generateRecurringHolidays(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        $year = $validated['year'];
        $recurringHolidays = Holiday::where('recurring', true)->get();
        $generated = [];

        foreach ($recurringHolidays as $holiday) {
            // Vérifier si le jour férié existe déjà pour cette année
            $exists = Holiday::where('name', $holiday->name)
                ->where('year', $year)
                ->exists();

            if (!$exists) {
                // Créer le jour férié pour la nouvelle année
                $newDate = \Carbon\Carbon::parse($holiday->date)->setYear($year);

                $newHoliday = Holiday::create([
                    'name' => $holiday->name,
                    'date' => $newDate->toDateString(),
                    'year' => $year,
                    'recurring' => true
                ]);

                $generated[] = $newHoliday;
            }
        }

        return response()->json([
            'message' => count($generated) . ' jour(s) férié(s) généré(s) pour ' . $year,
            'data' => $generated
        ]);
    }

    // ==================== SYSTEM INFO ====================

    /**
     * Informations système
     */
    public function getSystemInfo()
    {
        return response()->json([
            'data' => [
                'app_name' => config('app.name'),
                'app_version' => '1.0.0',
                'php_version' => phpversion(),
                'laravel_version' => app()->version(),
                'database' => config('database.default'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale')
            ]
        ]);
    }
}
