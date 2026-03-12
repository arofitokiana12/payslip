<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            SettingsSeeder::class,
            SocialContributionsSeeder::class,
            TaxBracketsSeeder::class,
            HolidaysSeeder::class,
            RoleSeeder::class,
            CompanySeeder::class,
            PositionSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            LeaveSeeder::class,
            AttendanceSeeder::class,
            PayslipSeeder::class,
        ]);
    }
}
