<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('active', true)->pluck('employee_id')->toArray();
        if (empty($employees)) {
            return;
        }

        $statuses = ['present', 'present', 'present', 'late', 'half_day', 'absent', 'on_leave'];

        // 2 mois de données : jours ouvrés
        $start = Carbon::today()->subDays(60);
        $end = Carbon::today();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }
            $dayStr = $date->format('Y-m-d');
            foreach ($employees as $employeeId) {
                if (rand(1, 20) === 1) {
                    continue; // skip some days (absent sans enregistrement)
                }
                $status = $statuses[array_rand($statuses)];
                $checkIn = $status === 'absent' || $status === 'on_leave' ? null : sprintf('%02d:%02d:00', rand(7, 10), rand(0, 59));
                $checkOut = ($checkIn && rand(1, 10) > 1) ? sprintf('%02d:%02d:00', rand(16, 19), rand(0, 59)) : null;

                Attendance::firstOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'date' => $dayStr,
                    ],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                    ]
                );
            }
        }
    }
}
