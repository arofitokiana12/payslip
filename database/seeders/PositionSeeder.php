<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Company;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positionsByCompany = [
            'PayFlex Madagascar' => [
                'Directeur Général', 'Responsable RH', 'Comptable Principal',
                'Développeur Senior', 'Développeur', 'Support Client', 'Stagiaire',
                'Chargé de Paie', 'Assistant Administratif', 'Chef de Projet',
            ],
            'Tech Solutions SARL' => [
                'Directeur Technique', 'Ingénieur DevOps', 'Designer UI/UX',
                'Commercial', 'Administrateur Système', 'Data Analyst', 'Stagiaire',
            ],
            'AgriExport Plus' => [
                'Directeur Export', 'Responsable Logistique', 'Contrôleur Qualité',
                'Comptable', 'Assistant Commercial', 'Chauffeur', 'Ouvrier',
            ],
        ];

        foreach ($positionsByCompany as $companyName => $positionNames) {
            $company = Company::where('company_name', $companyName)->first();
            if (!$company) {
                continue;
            }
            foreach ($positionNames as $name) {
                Position::updateOrCreate(
                    [
                        'company_id' => $company->company_id,
                        'position_name' => $name,
                    ],
                    ['position_name' => $name]
                );
            }
        }
    }
}
