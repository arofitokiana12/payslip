<?php

namespace Database\Seeders;

use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('active', true)->pluck('employee_id')->toArray();
        if (empty($employees)) {
            return;
        }

        $types = ['annual', 'sick', 'maternity', 'unpaid', 'other'];
        $statuses = ['pending', 'pending', 'approved', 'approved', 'rejected', 'cancelled'];
        $reasons = ['Congés annuels', 'Rendez-vous médical', 'Maternité', 'Raisons personnelles', 'Formation', null];

        for ($i = 0; $i < 55; $i++) {
            $employeeId = $employees[array_rand($employees)];
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            $start = Carbon::today()->subDays(rand(0, 180))->addDays(rand(0, 60));
            $duration = rand(1, 14);
            $end = $start->copy()->addDays($duration - 1);

            Leave::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                ],
                [
                    'leave_type' => $type,
                    'status' => $status,
                    'reason' => $reasons[array_rand($reasons)],
                ]
            );
        }
    }
}
