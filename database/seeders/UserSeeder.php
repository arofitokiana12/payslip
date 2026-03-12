<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $hrRole = Role::where('name', 'Ressources Humaines')->first();
        $accountantRole = Role::where('name', 'Comptable')->first();
        $employeeRole = Role::where('name', 'Employé')->first();

        $companyPayFlex = Company::where('company_name', 'PayFlex Madagascar')->first();
        $companyTech = Company::where('company_name', 'Tech Solutions SARL')->first();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'super@payflex.com',
                'user_name' => 'superadmin',
                'password' => 'password',
                'role_id' => $superAdminRole?->role_id,
                'company_id' => null,
                'active' => true,
            ],
            [
                'name' => 'Admin PayFlex',
                'email' => 'admin@payflex-mg.com',
                'user_name' => 'admin_payflex',
                'password' => 'password',
                'role_id' => $adminRole?->role_id,
                'company_id' => $companyPayFlex?->company_id,
                'active' => true,
            ],
            [
                'name' => 'Marie Manager',
                'email' => 'marie.manager@payflex-mg.com',
                'user_name' => 'mmanager',
                'password' => 'password',
                'role_id' => $managerRole?->role_id,
                'company_id' => $companyPayFlex?->company_id,
                'active' => true,
            ],
            [
                'name' => 'Jean RH',
                'email' => 'jean.rh@payflex-mg.com',
                'user_name' => 'jrh',
                'password' => 'password',
                'role_id' => $hrRole?->role_id,
                'company_id' => $companyPayFlex?->company_id,
                'active' => true,
            ],
            [
                'name' => 'Sophie Comptable',
                'email' => 'sophie@payflex-mg.com',
                'user_name' => 'scompta',
                'password' => 'password',
                'role_id' => $accountantRole?->role_id,
                'company_id' => $companyPayFlex?->company_id,
                'active' => true,
            ],
            [
                'name' => 'Admin Tech Solutions',
                'email' => 'admin@techsolutions.mg',
                'user_name' => 'admin_tech',
                'password' => 'password',
                'role_id' => $adminRole?->role_id,
                'company_id' => $companyTech?->company_id,
                'active' => true,
            ],
            [
                'name' => 'Employé Test',
                'email' => 'employe@payflex-mg.com',
                'user_name' => 'employe1',
                'password' => 'password',
                'role_id' => $employeeRole?->role_id,
                'company_id' => $companyPayFlex?->company_id,
                'active' => true,
            ],
        ];

        foreach ($users as $data) {
            $password = $data['password'];
            $data['password'] = Hash::make($password);
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
