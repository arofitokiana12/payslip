<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = [
            'Jean', 'Marie', 'Pierre', 'Sophie', 'Andry', 'Ravo', 'Lanto', 'Hery',
            'Nirina', 'Fenitra', 'Tsiry', 'Miora', 'Hasina', 'Faniry', 'Aina',
            'David', 'Sarah', 'Thomas', 'Emma', 'Nicolas', 'Laura', 'Lucas', 'Léa',
            'Rakoto', 'Rasoa', 'Randria', 'Ralay', 'Razafy', 'Randriamanantsoa',
        ];
        $lastNames = [
            'Rakoto', 'Rasoa', 'Randrianarisoa', 'Razafindrakoto', 'Ramanantsoa',
            'Randriamanantsoa', 'Rajaonarison', 'Rakotondrabe', 'Randriamampionona',
            'Dupont', 'Martin', 'Bernard', 'Petit', 'Durand', 'Leroy', 'Moreau',
            'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'Roux', 'Fournier',
        ];

        $companies = Company::all();
        if ($companies->isEmpty()) {
            return;
        }

        $matricule = 1000;
        foreach ($companies as $company) {
            $positions = Position::where('company_id', $company->company_id)->pluck('position_id')->toArray();
            if (empty($positions)) {
                continue;
            }

            $count = $company->company_name === 'PayFlex Madagascar' ? 18 : ($company->company_name === 'Tech Solutions SARL' ? 12 : 10);
            for ($i = 0; $i < $count; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $matricule++;
                $hireDate = now()->subDays(rand(30, 1200));
                $contractTypes = ['CDI', 'CDI', 'CDI', 'CDD', 'stage', 'freelance'];
                $contractType = $contractTypes[array_rand($contractTypes)];
                $baseSalary = (float) rand(450000, 3500000);

                Employee::updateOrCreate(
                    ['matricule' => (string) $matricule],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'position_id' => $positions[array_rand($positions)],
                        'hire_date' => $hireDate,
                        'contract_type' => $contractType,
                        'contract_end_date' => $contractType === 'CDD' ? $hireDate->copy()->addMonths(rand(6, 24)) : null,
                        'status' => rand(1, 10) > 1 ? 'active' : 'inactive',
                        'active' => rand(1, 10) > 1,
                        'base_salary' => $baseSalary,
                        'company_id' => $company->company_id,
                    ]
                );
            }
        }
    }
}
