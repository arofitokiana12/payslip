<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Paramètres généraux
            [
                'key' => 'company_name',
                'value' => 'Ma Société SARL',
                'type' => 'string',
                'category' => 'general',
                'label' => 'Nom de l\'entreprise',
                'description' => 'Nom de votre entreprise'
            ],
            [
                'key' => 'currency',
                'value' => 'MGA',
                'type' => 'string',
                'category' => 'general',
                'label' => 'Devise',
                'description' => 'Devise utilisée (MGA, EUR, USD)'
            ],
            [
                'key' => 'working_hours_per_week',
                'value' => '40',
                'type' => 'number',
                'category' => 'payroll',
                'label' => 'Heures de travail par semaine',
                'description' => 'Nombre d\'heures de travail hebdomadaires'
            ],
            [
                'key' => 'working_days_per_week',
                'value' => '5',
                'type' => 'number',
                'category' => 'payroll',
                'label' => 'Jours travaillés par semaine',
                'description' => 'Nombre de jours travaillés par semaine'
            ],

            // Heures supplémentaires
            [
                'key' => 'overtime_rate_weekday',
                'value' => '130',
                'type' => 'number',
                'category' => 'payroll',
                'label' => 'Taux heures sup. (semaine)',
                'description' => 'Taux majoré pour heures supplémentaires en semaine (en %)'
            ],
            [
                'key' => 'overtime_rate_weekend',
                'value' => '150',
                'type' => 'number',
                'category' => 'payroll',
                'label' => 'Taux heures sup. (weekend)',
                'description' => 'Taux majoré pour heures supplémentaires le weekend (en %)'
            ],
            [
                'key' => 'overtime_rate_holiday',
                'value' => '200',
                'type' => 'number',
                'category' => 'payroll',
                'label' => 'Taux heures sup. (jours fériés)',
                'description' => 'Taux majoré pour heures supplémentaires les jours fériés (en %)'
            ],

            // IRSA
            [
                'key' => 'irsa_abatement_rate',
                'value' => '20',
                'type' => 'number',
                'category' => 'tax',
                'label' => 'Abattement IRSA',
                'description' => 'Taux d\'abattement pour le calcul de l\'IRSA (en %)'
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
