<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaysSeeder extends Seeder
{
    public function run()
    {
        // Jours fériés Madagascar 2025
        $holidays = [
            ['name' => 'Jour de l\'an', 'date' => '2025-01-01', 'year' => 2025, 'recurring' => true],
            ['name' => 'Journée des femmes', 'date' => '2025-03-08', 'year' => 2025, 'recurring' => true],
            ['name' => 'Journée des martyrs', 'date' => '2025-03-29', 'year' => 2025, 'recurring' => true],
            ['name' => 'Pâques', 'date' => '2025-04-20', 'year' => 2025, 'recurring' => false],
            ['name' => 'Lundi de Pâques', 'date' => '2025-04-21', 'year' => 2025, 'recurring' => false],
            ['name' => 'Fête du travail', 'date' => '2025-05-01', 'year' => 2025, 'recurring' => true],
            ['name' => 'Ascension', 'date' => '2025-05-29', 'year' => 2025, 'recurring' => false],
            ['name' => 'Lundi de Pentecôte', 'date' => '2025-06-09', 'year' => 2025, 'recurring' => false],
            ['name' => 'Fête de l\'indépendance', 'date' => '2025-06-26', 'year' => 2025, 'recurring' => true],
            ['name' => 'Assomption', 'date' => '2025-08-15', 'year' => 2025, 'recurring' => true],
            ['name' => 'Toussaint', 'date' => '2025-11-01', 'year' => 2025, 'recurring' => true],
            ['name' => 'Noël', 'date' => '2025-12-25', 'year' => 2025, 'recurring' => true],
        ];

        foreach ($holidays as $holiday) {
            DB::table('holidays')->insert(array_merge($holiday, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
