<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialContributionsSeeder extends Seeder
{
    public function run()
    {
        $contributions = [
            [
                'name' => 'CNaPS (Caisse Nationale de Prévoyance Sociale)',
                'code' => 'cnaps',
                'employee_rate' => 1.00,
                'employer_rate' => 13.00,
                'ceiling' => 8064000, // Plafond annuel
                'active' => true,
                'description' => 'Cotisation retraite et prestations familiales'
            ],
            [
                'name' => 'OSTIE (Organisme Sanitaire Tananarivien Inter-Entreprises)',
                'code' => 'ostie',
                'employee_rate' => 1.00,
                'employer_rate' => 5.00,
                'ceiling' => null,
                'active' => true,
                'description' => 'Cotisation sanitaire'
            ],
        ];

        foreach ($contributions as $contribution) {
            DB::table('social_contributions')->insert(array_merge($contribution, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
