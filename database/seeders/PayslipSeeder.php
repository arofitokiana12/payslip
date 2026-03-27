<?php

namespace Database\Seeders;

use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class PayslipSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('active', true)->get();
        if ($employees->isEmpty()) {
            return;
        }

        $statuses = ['draft', 'draft', 'validated', 'validated', 'paid'];
        $periods = [
            [now()->month, now()->year],
            [now()->subMonth()->month, now()->subMonth()->year],
            [now()->subMonths(2)->month, now()->subMonths(2)->year],
        ];

        foreach ($employees as $employee) {
            foreach ($periods as [$month, $year]) {
                $gross = (float) $employee->base_salary;
                $deductions = round($gross * (rand(120, 220) / 1000), 2);
                $net = round($gross - $deductions, 2);

                $payslip = Payslip::firstOrCreate(
                    [
                        'employee_id' => $employee->employee_id,
                        'period_month' => $month,
                        'period_year' => $year,
                    ],
                    [
                        'gross_salary' => $gross,
                        'total_deductions' => $deductions,
                        'total_earnings' => $gross,
                        'net_salary' => $net,
                        'status' => $statuses[array_rand($statuses)],
                    ]
                );

                if ($payslip->wasRecentlyCreated && $payslip->items()->count() === 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->payslip_id,
                        'item_type' => 'earning',
                        'item_name' => 'Salaire de base',
                        'amount' => $gross,
                    ]);
                    PayslipItem::create([
                        'payslip_id' => $payslip->payslip_id,
                        'item_type' => 'deduction',
                        'item_name' => 'Cotisation sociale',
                        'amount' => round($deductions * 0.6, 2),
                    ]);
                    PayslipItem::create([
                        'payslip_id' => $payslip->payslip_id,
                        'item_type' => 'tax',
                        'item_name' => 'IRSA',
                        'amount' => round($deductions * 0.4, 2),
                    ]);
                }
            }
        }
    }
}
