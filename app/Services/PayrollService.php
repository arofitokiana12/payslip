<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Bonus;
use App\Models\SalaryAdvance;
use App\Models\SocialContribution;
use App\Models\TaxBracket;
use App\Models\Setting;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Calculer la paie pour un employé pour un mois donné
     */
    public function calculatePayroll($employeeId, $month, $year)
    {
        $employee = Employee::findOrFail($employeeId);

        // Données de base
        $baseSalary = $employee->base_salary;
        $workingDays = $this->getWorkingDaysInMonth($month, $year);

        // Calcul des jours travaillés
        $workedDays = $this->getWorkedDays($employeeId, $month, $year);
        $absentDays = $workingDays - $workedDays;

        // Calcul des congés payés (ne sont pas déduits)
        $paidLeaveDays = $this->getPaidLeaveDays($employeeId, $month, $year);

        // Calcul heures supplémentaires
        $overtimeHours = $this->getOvertimeHours($employeeId, $month, $year);
        $overtimeAmount = $this->calculateOvertimeAmount($baseSalary, $overtimeHours);

        // Calcul bonus
        $bonusAmount = $this->getBonuses($employeeId, $month, $year);

        // Calcul avances
        $advanceAmount = $this->getAdvances($employeeId, $month, $year);

        // Salaire brut
        $grossSalary = $baseSalary;

        // Déductions pour absences (hors congés payés)
        $unpaidAbsenceDays = max(0, $absentDays - $paidLeaveDays);
        $absenceDeduction = ($baseSalary / $workingDays) * $unpaidAbsenceDays;

        // Cotisations sociales
        $contributions = $this->calculateSocialContributions($grossSalary);

        // IRSA (Impôt sur salaire)
        $irsaAmount = $this->calculateIRSA($grossSalary);

        // Total gains
        $totalEarnings = $grossSalary + $overtimeAmount + $bonusAmount;

        // Total déductions
        $totalDeductions = $absenceDeduction + $contributions['total_employee'] + $irsaAmount + $advanceAmount;

        // Salaire net
        $netSalary = $totalEarnings - $totalDeductions;

        return [
            'employee' => $employee,
            'period' => [
                'month' => $month,
                'year' => $year
            ],
            'base_salary' => $baseSalary,
            'working_days' => $workingDays,
            'worked_days' => $workedDays,
            'absent_days' => $absentDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_absence_days' => $unpaidAbsenceDays,
            'overtime_hours' => $overtimeHours,
            'earnings' => [
                'base_salary' => $baseSalary,
                'overtime' => $overtimeAmount,
                'bonuses' => $bonusAmount,
                'total' => $totalEarnings
            ],
            'deductions' => [
                'absences' => $absenceDeduction,
                'contributions' => $contributions['employee_details'],
                'total_contributions' => $contributions['total_employee'],
                'irsa' => $irsaAmount,
                'advances' => $advanceAmount,
                'total' => $totalDeductions
            ],
            'gross_salary' => $totalEarnings,
            'net_salary' => $netSalary,
            'employer_contributions' => $contributions['total_employer']
        ];
    }

    /**
     * Générer une fiche de paie
     */
    public function generatePayslip($employeeId, $month, $year)
    {
        // Vérifier si une fiche existe déjà
        $existing = Payslip::where('employee_id', $employeeId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        if ($existing) {
            throw new \Exception('Une fiche de paie existe déjà pour cette période');
        }

        // Calculer la paie
        $calculation = $this->calculatePayroll($employeeId, $month, $year);

        // Créer la fiche de paie
        $payslip = Payslip::create([
            'employee_id' => $employeeId,
            'period_month' => $month,
            'period_year' => $year,
            'gross_salary' => $calculation['gross_salary'],
            'net_salary' => $calculation['net_salary'],
            'total_deductions' => $calculation['deductions']['total'],
            'total_earnings' => $calculation['earnings']['total'],
            'status' => 'draft'
        ]);

        // Créer les lignes de paie - Gains
        PayslipItem::create([
            'payslip_id' => $payslip->payslip_id,
            'item_type' => 'earning',
            'item_name' => 'Salaire de base',
            'amount' => $calculation['earnings']['base_salary']
        ]);

        if ($calculation['earnings']['overtime'] > 0) {
            PayslipItem::create([
                'payslip_id' => $payslip->payslip_id,
                'item_type' => 'earning',
                'item_name' => 'Heures supplémentaires (' . $calculation['overtime_hours'] . 'h)',
                'amount' => $calculation['earnings']['overtime']
            ]);
        }

        if ($calculation['earnings']['bonuses'] > 0) {
            PayslipItem::create([
                'payslip_id' => $payslip->payslip_id,
                'item_type' => 'earning',
                'item_name' => 'Primes et bonus',
                'amount' => $calculation['earnings']['bonuses']
            ]);
        }

        // Créer les lignes de paie - Déductions
        if ($calculation['deductions']['absences'] > 0) {
            PayslipItem::create([
                'payslip_id' => $payslip->payslip_id,
                'item_type' => 'deduction',
                'item_name' => 'Absences non justifiées (' . $calculation['unpaid_absence_days'] . ' jours)',
                'amount' => $calculation['deductions']['absences']
            ]);
        }

        // Cotisations sociales
        foreach ($calculation['deductions']['contributions'] as $contrib) {
            PayslipItem::create([
                'payslip_id' => $payslip->payslip_id,
                'item_type' => 'deduction',
                'item_name' => $contrib['name'] . ' (' . $contrib['rate'] . '%)',
                'amount' => $contrib['amount']
            ]);
        }

        // IRSA
        PayslipItem::create([
            'payslip_id' => $payslip->payslip_id,
            'item_type' => 'tax',
            'item_name' => 'IRSA',
            'amount' => $calculation['deductions']['irsa']
        ]);

        // Avances
        if ($calculation['deductions']['advances'] > 0) {
            PayslipItem::create([
                'payslip_id' => $payslip->payslip_id,
                'item_type' => 'deduction',
                'item_name' => 'Avance sur salaire',
                'amount' => $calculation['deductions']['advances']
            ]);
        }

        return $payslip->load(['employee', 'items']);
    }

    /**
     * Nombre de jours ouvrables dans le mois
     */
    private function getWorkingDaysInMonth($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Exclure samedi (6) et dimanche (0)
            if (!in_array($currentDate->dayOfWeek, [0, 6])) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Nombre de jours travaillés
     */
    private function getWorkedDays($employeeId, $month, $year)
    {
        $count = Attendance::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereIn('status', ['Present', 'Late', 'Remote'])
            ->count();

        return $count;
    }

    /**
     * Nombre de jours de congés payés
     */
    private function getPaidLeaveDays($employeeId, $month, $year)
    {
        $leaves = Leave::where('employee_id', $employeeId)
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->where('status', 'approved')
            ->whereIn('leave_type', ['annual', 'sick'])
            ->get();

        $totalDays = 0;
        foreach ($leaves as $leave) {
            $totalDays += $leave->duration_days;
        }

        return $totalDays;
    }

    /**
     * Heures supplémentaires
     */
    private function getOvertimeHours($employeeId, $month, $year)
    {
        $total = Overtime::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('hour');

        return $total ?? 0;
    }

    /**
     * Calcul montant heures supplémentaires
     */
    private function calculateOvertimeAmount($baseSalary, $overtimeHours)
    {
        if ($overtimeHours == 0) return 0;

        // Récupérer le taux depuis les settings
        $rate = Setting::get('overtime_rate_weekday', 130);

        // 173.33 heures = moyenne heures par mois (40h/semaine * 52 semaines / 12 mois)
        $hourlyRate = $baseSalary / 173.33;
        $overtimeRate = $hourlyRate * ($rate / 100);

        return round($overtimeRate * $overtimeHours, 2);
    }

    /**
     * Total des bonus du mois
     */
    private function getBonuses($employeeId, $month, $year)
    {
        $total = Bonus::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        return $total ?? 0;
    }

    /**
     * Total des avances à rembourser
     */
    private function getAdvances($employeeId, $month, $year)
    {
        $total = SalaryAdvance::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('repayment_status', 'pending')
            ->sum('amount');

        return $total ?? 0;
    }

    /**
     * Calculer les cotisations sociales
     */
    private function calculateSocialContributions($salary)
    {
        $contributions = SocialContribution::where('active', true)->get();

        $employeeDetails = [];
        $totalEmployee = 0;
        $totalEmployer = 0;

        foreach ($contributions as $contrib) {
            // Appliquer le plafond si défini
            $baseAmount = $contrib->ceiling && $salary > $contrib->ceiling
                ? $contrib->ceiling
                : $salary;

            $employeeAmount = round($baseAmount * ($contrib->employee_rate / 100), 2);
            $employerAmount = round($baseAmount * ($contrib->employer_rate / 100), 2);

            $employeeDetails[] = [
                'name' => $contrib->name,
                'code' => $contrib->code,
                'rate' => $contrib->employee_rate,
                'amount' => $employeeAmount
            ];

            $totalEmployee += $employeeAmount;
            $totalEmployer += $employerAmount;
        }

        return [
            'employee_details' => $employeeDetails,
            'total_employee' => $totalEmployee,
            'total_employer' => $totalEmployer
        ];
    }

    /**
     * Calcul IRSA (Madagascar)
     */
    private function calculateIRSA($salary)
    {
        // Récupérer le taux d'abattement
        $abatementRate = Setting::get('irsa_abatement_rate', 20);

        // Salaire imposable après abattement
        $taxableIncome = $salary * (1 - ($abatementRate / 100));

        // Récupérer les tranches d'imposition
        $brackets = TaxBracket::where('active', true)->orderBy('order')->get();

        $irsa = 0;

        foreach ($brackets as $bracket) {
            if ($taxableIncome <= $bracket->min_salary) {
                break;
            }

            if ($bracket->max_salary === null || $taxableIncome <= $bracket->max_salary) {
                // Tranche actuelle
                $amountInBracket = $taxableIncome - $bracket->min_salary;
                $irsa = $bracket->fixed_amount + ($amountInBracket * ($bracket->tax_rate / 100));
                break;
            } else if ($taxableIncome > $bracket->max_salary) {
                // Passer à la tranche suivante
                continue;
            }
        }

        return round($irsa, 2);
    }
}
