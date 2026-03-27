<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\Employee;
use App\Models\Bonus;
use App\Models\SalaryAdvance;
use App\Models\Overtime;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Company;


class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Liste des fiches de paie
     */
    public function index(Request $request)
    {
        $query = Payslip::with(['employee']);

        // Filtres
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('month')) {
            $query->where('period_month', $request->month);
        }

        if ($request->has('year')) {
            $query->where('period_year', $request->year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payslips = $query->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => $payslips->items(),
            'meta' => [
                'current_page' => $payslips->currentPage(),
                'per_page' => $payslips->perPage(),
                'total' => $payslips->total(),
                'last_page' => $payslips->lastPage(),
            ],
        ]);
    }

    /**
     * Détails d'une fiche de paie
     */
    public function show($id)
    {
        $payslip = Payslip::with(['employee', 'items'])->findOrFail($id);

        return response()->json([
            'data' => $payslip
        ]);
    }

    /**
     * Calculer la paie (sans créer de fiche)
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        try {
            $calculation = $this->payrollService->calculatePayroll(
                $validated['employee_id'],
                $validated['month'],
                $validated['year']
            );

            return response()->json([
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du calcul: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer une fiche de paie
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        try {
            $payslip = $this->payrollService->generatePayslip(
                $validated['employee_id'],
                $validated['month'],
                $validated['year']
            );

            return response()->json([
                'message' => 'Fiche de paie générée avec succès',
                'data' => $payslip
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Générer les fiches de paie pour tous les employés
     */
    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'employee_ids' => 'sometimes|array',
            'employee_ids.*' => 'exists:employees,employee_id'
        ]);

        $month = $validated['month'];
        $year = $validated['year'];

        // Si employee_ids fourni, utiliser cette liste, sinon tous les employés actifs
        if (isset($validated['employee_ids'])) {
            $employees = Employee::whereIn('employee_id', $validated['employee_ids'])
                ->where('active', true)
                ->get();
        } else {
            $employees = Employee::where('active', true)->get();
        }

        $generated = [];
        $errors = [];

        foreach ($employees as $employee) {
            try {
                $payslip = $this->payrollService->generatePayslip(
                    $employee->employee_id,
                    $month,
                    $year
                );
                $generated[] = $payslip;
            } catch (\Exception $e) {
                $errors[] = [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => count($generated) . ' fiche(s) de paie générée(s)',
            'generated' => count($generated),
            'errors' => count($errors),
            'data' => $generated,
            'error_details' => $errors
        ]);
    }

    /**
     * Finaliser une fiche de paie
     */
    public function finalize($id)
    {
        $payslip = Payslip::findOrFail($id);

        if ($payslip->status !== 'draft') {
            return response()->json([
                'message' => 'Seules les fiches brouillon peuvent être finalisées'
            ], 400);
        }

        $payslip->update(['status' => 'finalized']);

        return response()->json([
            'message' => 'Fiche de paie finalisée',
            'data' => $payslip
        ]);
    }

    /**
     * Marquer comme payée
     */
    public function markAsPaid($id)
    {
        $payslip = Payslip::findOrFail($id);

        if ($payslip->status !== 'finalized') {
            return response()->json([
                'message' => 'Seules les fiches finalisées peuvent être marquées comme payées'
            ], 400);
        }

        $payslip->update(['status' => 'paid']);

        return response()->json([
            'message' => 'Fiche de paie marquée comme payée',
            'data' => $payslip
        ]);
    }

    /**
     * Supprimer une fiche de paie (brouillon uniquement)
     */
    public function destroy($id)
    {
        $payslip = Payslip::findOrFail($id);

        if ($payslip->status !== 'draft') {
            return response()->json([
                'message' => 'Seules les fiches brouillon peuvent être supprimées'
            ], 400);
        }

        $payslip->delete();

        return response()->json([
            'message' => 'Fiche de paie supprimée'
        ]);
    }

    // ==================== BONUS ====================

    /**
     * Liste des bonus
     */
    public function getBonuses(Request $request)
    {
        $query = Bonus::with(['employee']);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('month') && $request->has('year')) {
            $query->whereYear('date', $request->year)
                  ->whereMonth('date', $request->month);
        }

        $bonuses = $query->orderBy('date', 'desc')->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => $bonuses->items(),
            'meta' => [
                'current_page' => $bonuses->currentPage(),
                'per_page' => $bonuses->perPage(),
                'total' => $bonuses->total(),
                'last_page' => $bonuses->lastPage(),
            ],
        ]);
    }

    /**
     * Créer un bonus
     */
    public function createBonus(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'bonus_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $bonus = Bonus::create($validated);

        return response()->json([
            'message' => 'Bonus créé avec succès',
            'data' => $bonus->load('employee')
        ], 201);
    }

    /**
     * Modifier un bonus
     */
    public function updateBonus(Request $request, $id)
    {
        $bonus = Bonus::findOrFail($id);

        $validated = $request->validate([
            'bonus_type' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'description' => 'nullable|string'
        ]);

        $bonus->update($validated);

        return response()->json([
            'message' => 'Bonus modifié avec succès',
            'data' => $bonus->load('employee')
        ]);
    }

    /**
     * Supprimer un bonus
     */
    public function deleteBonus($id)
    {
        $bonus = Bonus::findOrFail($id);
        $bonus->delete();

        return response()->json([
            'message' => 'Bonus supprimé avec succès'
        ]);
    }

    // ==================== SALARY ADVANCES ====================

    /**
     * Liste des avances
     */
    public function getAdvances(Request $request)
    {
        $query = SalaryAdvance::with(['employee']);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('status')) {
            $query->where('repayment_status', $request->status);
        }

        $advances = $query->orderBy('date', 'desc')->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => $advances->items(),
            'meta' => [
                'current_page' => $advances->currentPage(),
                'per_page' => $advances->perPage(),
                'total' => $advances->total(),
                'last_page' => $advances->lastPage(),
            ],
        ]);
    }

    /**
     * Créer une avance
     */
    public function createAdvance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'repayment_status' => 'sometimes|in:pending,partial,completed'
        ]);

        $validated['repayment_status'] = $validated['repayment_status'] ?? 'pending';

        $advance = SalaryAdvance::create($validated);

        return response()->json([
            'message' => 'Avance créée avec succès',
            'data' => $advance->load('employee')
        ], 201);
    }

    /**
     * Modifier une avance
     */
    public function updateAdvance(Request $request, $id)
    {
        $advance = SalaryAdvance::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'date' => 'sometimes|date',
            'repayment_status' => 'sometimes|in:pending,partial,completed'
        ]);

        $advance->update($validated);

        return response()->json([
            'message' => 'Avance modifiée avec succès',
            'data' => $advance->load('employee')
        ]);
    }

    /**
     * Supprimer une avance
     */
    public function deleteAdvance($id)
    {
        $advance = SalaryAdvance::findOrFail($id);
        $advance->delete();

        return response()->json([
            'message' => 'Avance supprimée avec succès'
        ]);
    }

    // ==================== OVERTIME ====================

    /**
     * Liste des heures supplémentaires
     */
    public function getOvertimes(Request $request)
    {
        $query = Overtime::with(['employee']);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('month') && $request->has('year')) {
            $query->whereYear('date', $request->year)
                  ->whereMonth('date', $request->month);
        }

        $overtimes = $query->orderBy('date', 'desc')->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'data' => $overtimes->items(),
            'meta' => [
                'current_page' => $overtimes->currentPage(),
                'per_page' => $overtimes->perPage(),
                'total' => $overtimes->total(),
                'last_page' => $overtimes->lastPage(),
            ],
        ]);
    }

    /**
     * Créer des heures supplémentaires
     */
    public function createOvertime(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'date' => 'required|date',
            'hour' => 'required|integer|min:1|max:24'
        ]);

        $overtime = Overtime::create($validated);

        return response()->json([
            'message' => 'Heures supplémentaires enregistrées',
            'data' => $overtime->load('employee')
        ], 201);
    }

    /**
     * Modifier des heures supplémentaires
     */
    public function updateOvertime(Request $request, $id)
    {
        $overtime = Overtime::findOrFail($id);

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'hour' => 'sometimes|integer|min:1|max:24'
        ]);

        $overtime->update($validated);

        return response()->json([
            'message' => 'Heures supplémentaires modifiées',
            'data' => $overtime->load('employee')
        ]);
    }

    /**
     * Supprimer des heures supplémentaires
     */
    public function deleteOvertime($id)
    {
        $overtime = Overtime::findOrFail($id);
        $overtime->delete();

        return response()->json([
            'message' => 'Heures supplémentaires supprimées'
        ]);
    }

    // ==================== REPORTS ====================

    /**
     * Rapport de paie mensuel
     */
    public function monthlyReport(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100'
        ]);

        $payslips = Payslip::with(['employee'])
            ->where('period_month', $validated['month'])
            ->where('period_year', $validated['year'])
            ->get();

        $totalGross = $payslips->sum('gross_salary');
        $totalNet = $payslips->sum('net_salary');
        $totalDeductions = $payslips->sum('total_deductions');

        return response()->json([
            'period' => [
                'month' => $validated['month'],
                'year' => $validated['year']
            ],
            'summary' => [
                'total_employees' => $payslips->count(),
                'total_gross_salary' => $totalGross,
                'total_net_salary' => $totalNet,
                'total_deductions' => $totalDeductions
            ],
            'data' => $payslips
        ]);
    }

    /* Télécharger une fiche de paie en PDF
    */
   public function downloadPDF($id)
   {
       $payslip = Payslip::with(['employee.position', 'items'])->findOrFail($id);

       // Récupérer l'entreprise
       $company = Company::where('company_id', $payslip->employee->company_id)->first();

       // Nom du mois
       $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
       $monthName = $months[$payslip->period_month];

       // Statut en français
       $statusLabels = [
           'draft' => 'Brouillon',
           'finalized' => 'Finalisé',
           'paid' => 'Payé'
       ];
       $statusLabel = $statusLabels[$payslip->status] ?? $payslip->status;

       // Convertir le montant net en lettres
       $netSalaryInWords = $this->numberToWords($payslip->net_salary);

       // Générer le PDF
       $pdf = PDF::loadView('payslips.pdf', [
           'payslip' => $payslip,
           'company' => $company,
           'monthName' => $monthName,
           'statusLabel' => $statusLabel,
           'netSalaryInWords' => $netSalaryInWords
       ]);

       // Options PDF
       $pdf->setPaper('A4', 'portrait');

       // Nom du fichier
       $filename = 'Fiche_Paie_'
           . $payslip->employee->first_name . '_'
           . $payslip->employee->last_name . '_'
           . $monthName . '_'
           . $payslip->period_year . '.pdf';

       return $pdf->download($filename);
   }

   /**
    * Afficher la fiche en PDF dans le navigateur
    */
   public function viewPDF($id)
   {
       $payslip = Payslip::with(['employee.position', 'items'])->findOrFail($id);

       $company = Company::where('company_id', $payslip->employee->company_id)->first();

       $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
       $monthName = $months[$payslip->period_month];

       $statusLabels = [
           'draft' => 'Brouillon',
           'finalized' => 'Finalisé',
           'paid' => 'Payé'
       ];
       $statusLabel = $statusLabels[$payslip->status] ?? $payslip->status;

       $netSalaryInWords = $this->numberToWords($payslip->net_salary);

       $pdf = PDF::loadView('payslips.pdf', [
           'payslip' => $payslip,
           'company' => $company,
           'monthName' => $monthName,
           'statusLabel' => $statusLabel,
           'netSalaryInWords' => $netSalaryInWords
       ]);

       $pdf->setPaper('A4', 'portrait');

       return $pdf->stream();
   }

   /**
    * Télécharger toutes les fiches d'un mois en ZIP
    */
   public function downloadMonthlyZip(Request $request)
   {
       $validated = $request->validate([
           'month' => 'required|integer|min:1|max:12',
           'year' => 'required|integer|min:2000|max:2100'
       ]);

       $payslips = Payslip::with(['employee.position', 'items'])
           ->where('period_month', $validated['month'])
           ->where('period_year', $validated['year'])
           ->get();

       if ($payslips->isEmpty()) {
           return response()->json([
               'message' => 'Aucune fiche de paie trouvée pour cette période'
           ], 404);
       }

       $zip = new \ZipArchive();
       $zipFileName = 'Fiches_Paie_' . $validated['month'] . '_' . $validated['year'] . '.zip';
       $zipPath = storage_path('app/public/' . $zipFileName);

       if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
           $company = Company::first();
           $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
           $monthName = $months[$validated['month']];

           foreach ($payslips as $payslip) {
               $statusLabels = [
                   'draft' => 'Brouillon',
                   'finalized' => 'Finalisé',
                   'paid' => 'Payé'
               ];
               $statusLabel = $statusLabels[$payslip->status] ?? $payslip->status;

               $netSalaryInWords = $this->numberToWords($payslip->net_salary);

               $pdf = PDF::loadView('payslips.pdf', [
                   'payslip' => $payslip,
                   'company' => $company,
                   'monthName' => $monthName,
                   'statusLabel' => $statusLabel,
                   'netSalaryInWords' => $netSalaryInWords
               ]);

               $pdf->setPaper('A4', 'portrait');

               $filename = 'Fiche_'
                   . $payslip->employee->first_name . '_'
                   . $payslip->employee->last_name . '.pdf';

               $zip->addFromString($filename, $pdf->output());
           }

           $zip->close();
       }

       return response()->download($zipPath)->deleteFileAfterSend(true);
   }

   /**
    * Convertir un nombre en lettres (français)
    */
   private function numberToWords($number)
   {
       $number = floor($number);

       if ($number == 0) return 'zéro';

       $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
       $teens = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
       $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

       $result = '';

       // Millions
       if ($number >= 1000000) {
           $millions = floor($number / 1000000);
           $result .= $this->convertGroup($millions, $units, $teens, $tens) . ' million';
           if ($millions > 1) $result .= 's';
           $result .= ' ';
           $number %= 1000000;
       }

       // Milliers
       if ($number >= 1000) {
           $thousands = floor($number / 1000);
           if ($thousands > 1) {
               $result .= $this->convertGroup($thousands, $units, $teens, $tens) . ' ';
           }
           $result .= 'mille ';
           $number %= 1000;
       }

       // Centaines
       if ($number > 0) {
           $result .= $this->convertGroup($number, $units, $teens, $tens);
       }

       return trim($result) . ' ariary';
   }

   private function convertGroup($number, $units, $teens, $tens)
   {
       $result = '';

       // Centaines
       if ($number >= 100) {
           $hundreds = floor($number / 100);
           if ($hundreds > 1) {
               $result .= $units[$hundreds] . ' ';
           }
           $result .= 'cent';
           if ($hundreds > 1 && $number % 100 == 0) {
               $result .= 's';
           }
           $result .= ' ';
           $number %= 100;
       }

       // Dizaines et unités
       if ($number >= 20) {
           $result .= $tens[floor($number / 10)];
           if ($number % 10 > 0) {
               $result .= '-' . $units[$number % 10];
           }
       } elseif ($number >= 10) {
           $result .= $teens[$number - 10];
       } elseif ($number > 0) {
           $result .= $units[$number];
       }

       return trim($result);
   }
}
