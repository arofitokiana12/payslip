<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'company_name' => 'PayFlex Madagascar',
                'date_creation' => '2020-01-15',
                'nif' => '1234567890',
                'stat' => '98765432',
                'rcs' => 'RCS-Tana-2020-A-001',
                'adress' => 'Lot II K 47 Ambohijatovo, Antananarivo 101',
                'email' => 'contact@payflex-mg.com',
                'active' => true,
            ],
            [
                'company_name' => 'Tech Solutions SARL',
                'date_creation' => '2019-06-01',
                'nif' => '2234567891',
                'stat' => '88765433',
                'rcs' => 'RCS-Tana-2019-B-042',
                'adress' => 'Immeuble Horizon, Avenue de l\'Indépendance, Antananarivo',
                'email' => 'info@techsolutions.mg',
                'active' => true,
            ],
            [
                'company_name' => 'AgriExport Plus',
                'date_creation' => '2021-03-10',
                'nif' => '3234567892',
                'stat' => '78765434',
                'rcs' => 'RCS-Tamatave-2021-C-012',
                'adress' => 'Zone Franche Toamasina, 501 Toamasina',
                'email' => 'direction@agriexport.mg',
                'active' => true,
            ],
        ];

        foreach ($companies as $data) {
            Company::updateOrCreate(
                ['company_name' => $data['company_name']],
                $data
            );
        }
    }
}
