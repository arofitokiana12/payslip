<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Company;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Statistiques générales du dashboard
     */
    public function index(Request $request)
    {
        $currentMonth = $request->get('month', now()->month);
        $currentYear = $request->get('year', now()->year);

        // Statistiques employés
        $employeeStats = $this->getEmployeeStats();

        // Statistiques de paie
        $payrollStats = $this->getPayrollStats($currentMonth, $currentYear);

        // Statistiques de présence
        $attendanceStats = $this->getAttendanceStats($currentMonth, $currentYear);

        // Statistiques de congés
        $leaveStats = $this->getLeaveStats($currentMonth, $currentYear);

        // Graphiques
        $charts = [
            'payroll_trend' => $this->getPayrollTrend($currentYear),
            'attendance_overview' => $this->getAttendanceOverview($currentMonth, $currentYear),
            'leave_distribution' => $this->getLeaveDistribution($currentYear),
            'employee_by_position' => $this->getEmployeesByPosition()
        ];

        // Activités récentes
        $recentActivities = $this->getRecentActivities();

        return response()->json([
            'period' => [
                'month' => $currentMonth,
                'year' => $currentYear
            ],
            'stats' => [
                'employees' => $employeeStats,
                'payroll' => $payrollStats,
                'attendance' => $attendanceStats,
                'leaves' => $leaveStats
            ],
            'charts' => $charts,
            'recent_activities' => $recentActivities
        ]);
    }

    /**
     * Statistiques employés
     */
    private function getEmployeeStats()
    {
        $total = Employee::count();
        $active = Employee::where('active', true)->count();
        $inactive = $total - $active;

        $byContractType = Employee::select('contract_type', DB::raw('count(*) as count'))
            ->where('active', true)
            ->groupBy('contract_type')
            ->get()
            ->pluck('count', 'contract_type');

        // Nouveaux ce mois
        $newThisMonth = Employee::whereYear('hire_date', now()->year)
            ->whereMonth('hire_date', now()->month)
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'new_this_month' => $newThisMonth,
            'by_contract_type' => $byContractType
        ];
    }

    /**
     * Statistiques de paie
     */
    private function getPayrollStats($month, $year)
    {
        $currentMonthPayslips = Payslip::where('period_month', $month)
            ->where('period_year', $year)
            ->get();

        $totalGross = $currentMonthPayslips->sum('gross_salary');
        $totalNet = $currentMonthPayslips->sum('net_salary');
        $totalDeductions = $currentMonthPayslips->sum('total_deductions');

        $byStatus = $currentMonthPayslips->groupBy('status')->map->count();

        // Comparaison mois précédent
        $previousMonth = $month == 1 ? 12 : $month - 1;
        $previousYear = $month == 1 ? $year - 1 : $year;

        $previousMonthTotal = Payslip::where('period_month', $previousMonth)
            ->where('period_year', $previousYear)
            ->sum('net_salary');

        $variation = 0;
        if ($previousMonthTotal > 0) {
            $variation = (($totalNet - $previousMonthTotal) / $previousMonthTotal) * 100;
        }

        return [
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_deductions' => $totalDeductions,
            'payslip_count' => $currentMonthPayslips->count(),
            'by_status' => [
                'draft' => $byStatus->get('draft', 0),
                'finalized' => $byStatus->get('finalized', 0),
                'paid' => $byStatus->get('paid', 0)
            ],
            'variation_percent' => round($variation, 2)
        ];
    }

    /**
     * Statistiques de présence
     */
    private function getAttendanceStats($month, $year)
    {
        $attendances = Attendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $byStatus = $attendances->groupBy('status')->map->count();

        $totalDays = $attendances->count();
        $presentDays = $attendances->whereIn('status', ['Present', 'Remote'])->count();
        $lateDays = $attendances->where('status', 'Late')->count();
        $absentDays = $attendances->where('status', 'Absent')->count();

        $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

        return [
            'total_records' => $totalDays,
            'present' => $byStatus->get('Present', 0),
            'late' => $byStatus->get('Late', 0),
            'absent' => $byStatus->get('Absent', 0),
            'remote' => $byStatus->get('Remote', 0),
            'half_day' => $byStatus->get('Half-day', 0),
            'attendance_rate' => round($attendanceRate, 2)
        ];
    }

    /**
     * Statistiques de congés
     */
    private function getLeaveStats($month, $year)
    {
        $leaves = Leave::whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->get();

        $byStatus = $leaves->groupBy('status')->map->count();
        $byType = $leaves->groupBy('leave_type')->map->count();

        $totalDays = $leaves->sum('duration_days');

        return [
            'total_requests' => $leaves->count(),
            'total_days' => $totalDays,
            'pending' => $byStatus->get('pending', 0),
            'approved' => $byStatus->get('approved', 0),
            'rejected' => $byStatus->get('rejected', 0),
            'by_type' => [
                'annual' => $byType->get('annual', 0),
                'sick' => $byType->get('sick', 0),
                'maternity' => $byType->get('maternity', 0),
                'unpaid' => $byType->get('unpaid', 0),
                'other' => $byType->get('other', 0)
            ]
        ];
    }

    /**
     * Tendance de paie sur l'année
     */
    private function getPayrollTrend($year)
    {
        $data = Payslip::select(
                DB::raw('period_month as month'),
                DB::raw('SUM(gross_salary) as gross'),
                DB::raw('SUM(net_salary) as net'),
                DB::raw('SUM(total_deductions) as deductions')
            )
            ->where('period_year', $year)
            ->groupBy('period_month')
            ->orderBy('period_month')
            ->get();

        return [
            'labels' => $data->pluck('month')->map(function($m) {
                return Carbon::create()->month($m)->format('M');
            })->toArray(),
            'datasets' => [
                [
                    'label' => 'Salaire Brut',
                    'data' => $data->pluck('gross')->toArray(),
                    'color' => '#667eea'
                ],
                [
                    'label' => 'Salaire Net',
                    'data' => $data->pluck('net')->toArray(),
                    'color' => '#10b981'
                ],
                [
                    'label' => 'Déductions',
                    'data' => $data->pluck('deductions')->toArray(),
                    'color' => '#ef4444'
                ]
            ]
        ];
    }

    /**
     * Vue d'ensemble des présences
     */
    private function getAttendanceOverview($month, $year)
    {
        $data = Attendance::select('status', DB::raw('count(*) as count'))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('status')
            ->get();

        return [
            'labels' => $data->pluck('status')->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'colors' => [
                'Present' => '#10b981',
                'Late' => '#f59e0b',
                'Absent' => '#ef4444',
                'Remote' => '#3b82f6',
                'Half-day' => '#8b5cf6'
            ]
        ];
    }

    /**
     * Distribution des congés
     */
    private function getLeaveDistribution($year)
    {
        $data = Leave::select('leave_type', DB::raw('count(*) as count'))
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->groupBy('leave_type')
            ->get();

        return [
            'labels' => $data->pluck('leave_type')->map(function($type) {
                $labels = [
                    'annual' => 'Annuel',
                    'sick' => 'Maladie',
                    'maternity' => 'Maternité',
                    'unpaid' => 'Sans solde',
                    'other' => 'Autre'
                ];
                return $labels[$type] ?? $type;
            })->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'colors' => ['#667eea', '#f59e0b', '#ec4899', '#6b7280', '#8b5cf6']
        ];
    }

    /**
     * Employés par poste
     */
    private function getEmployeesByPosition()
    {
        $data = Employee::select('positions.position_name', DB::raw('count(*) as count'))
            ->join('positions', 'employees.position_id', '=', 'positions.position_id')
            ->where('employees.active', true)
            ->groupBy('positions.position_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('position_name')->toArray(),
            'data' => $data->pluck('count')->toArray()
        ];
    }

    /**
     * Activités récentes
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Dernières fiches de paie générées
        $recentPayslips = Payslip::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentPayslips as $payslip) {
            $activities[] = [
                'type' => 'payslip',
                'icon' => 'fa-money-bill-wave',
                'color' => 'success',
                'title' => 'Fiche de paie générée',
                'description' => $payslip->employee->first_name . ' ' . $payslip->employee->last_name,
                'date' => $payslip->created_at,
                'relative_time' => $payslip->created_at->diffForHumans()
            ];
        }

        // Dernières demandes de congés
        $recentLeaves = Leave::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentLeaves as $leave) {
            $activities[] = [
                'type' => 'leave',
                'icon' => 'fa-calendar-times',
                'color' => 'warning',
                'title' => 'Demande de congé en attente',
                'description' => $leave->employee->first_name . ' ' . $leave->employee->last_name,
                'date' => $leave->created_at

            ];
        }

        // Nouveaux employés
        $newEmployees = Employee::orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($newEmployees as $employee) {
            $activities[] = [
                'type' => 'employee',
                'icon' => 'fa-user-plus',
                'color' => 'info',
                'title' => 'Nouvel employé',
                'description' => $employee->first_name . ' ' . $employee->last_name,
                'date' => $employee->created_at,
                'relative_time' => $employee->created_at->diffForHumans()
            ];
        }

        // Trier par date
        // usort($activities, function($a, $b) {
        //     return $b['date']->timestamp - $a['date']->timestamp;
        // });

        return array_slice($activities, 0, 10);
    }

    /**
     * Employés avec anniversaires ce mois
     */
    public function getBirthdays()
    {
        $currentMonth = now()->month;

        // Assuming you have a birth_date field in employees table
        // If not, this will need to be adjusted
        $employees = Employee::whereMonth('hire_date', $currentMonth)
            ->where('active', true)
            ->orderBy('hire_date')
            ->get();

        return response()->json([
            'data' => $employees->map(function($employee) {
                return [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->first_name . ' ' . $employee->last_name,
                    'date' => $employee->hire_date,
                    'years' => now()->diffInYears($employee->hire_date)
                ];
            })
        ]);
    }

    /**
     * Top employés (par présence)
     */
    public function getTopEmployees(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $topEmployees = Employee::select('employees.*', DB::raw('COUNT(attendance.attendance_id) as presence_count'))
            ->join('attendance', 'employees.employee_id', '=', 'attendance.employee_id')
            ->whereYear('attendance.date', $year)
            ->whereMonth('attendance.date', $month)
            ->whereIn('attendance.status', ['Present', 'Remote'])
            ->groupBy('employees.employee_id')
            ->orderBy('presence_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $topEmployees
        ]);
    }

    /**
     * Alertes et notifications
     */
    public function getAlerts()
    {
        $alerts = [];

        // Congés en attente
        $pendingLeaves = Leave::where('status', 'pending')->count();
        if ($pendingLeaves > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-clock',
                'title' => 'Demandes de congés en attente',
                'message' => "$pendingLeaves demande(s) nécessitent votre approbation",
                'action' => '/leaves?status=pending'
            ];
        }

        // Fiches de paie brouillon
        $draftPayslips = Payslip::where('status', 'draft')
            ->where('period_month', now()->month)
            ->where('period_year', now()->year)
            ->count();

        if ($draftPayslips > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fa-file-alt',
                'title' => 'Fiches de paie en brouillon',
                'message' => "$draftPayslips fiche(s) à finaliser pour ce mois",
                'action' => '/payroll'
            ];
        }

        // Employés sans présence cette semaine
        $startOfWeek = now()->startOfWeek();
        $employeesWithoutAttendance = Employee::where('active', true)
            ->whereDoesntHave('attendances', function($query) use ($startOfWeek) {
                $query->where('date', '>=', $startOfWeek);
            })
            ->count();

        if ($employeesWithoutAttendance > 5) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'title' => 'Présences manquantes',
                'message' => "$employeesWithoutAttendance employé(s) sans pointage cette semaine",
                'action' => '/attendance'
            ];
        }

        return response()->json([
            'data' => $alerts
        ]);
    }
}
