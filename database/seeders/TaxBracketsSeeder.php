<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxBracketsSeeder extends Seeder
{
    public function run()
    {
        // Barème IRSA Madagascar (2025)
        $brackets = [
            [
                'min_salary' => 0,
                'max_salary' => 350000,
                'tax_rate' => 0,
                'fixed_amount' => 0,
                'order' => 1,
                'active' => true
            ],
            [
                'min_salary' => 350001,
                'max_salary' => 400000,
                'tax_rate' => 5,
                'fixed_amount' => 0,
                'order' => 2,
                'active' => true
            ],
            [
                'min_salary' => 400001,
                'max_salary' => 500000,
                'tax_rate' => 10,
                'fixed_amount' => 2500,
                'order' => 3,
                'active' => true
            ],
            [
                'min_salary' => 500001,
                'max_salary' => 600000,
                'tax_rate' => 15,
                'fixed_amount' => 12500,
                'order' => 4,
                'active' => true
            ],
            [
                'min_salary' => 600001,
                'max_salary' => null, // Pas de plafond
                'tax_rate' => 20,
                'fixed_amount' => 27500,
                'order' => 5,
                'active' => true
            ],
        ];

        foreach ($brackets as $bracket) {
            DB::table('tax_brackets')->insert(array_merge($bracket, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
