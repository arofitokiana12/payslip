<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Accès total au système'],
            ['name' => 'Admin', 'description' => 'Administrateur entreprise'],
            ['name' => 'Manager', 'description' => 'Gestion d\'équipe et validation'],
            ['name' => 'Ressources Humaines', 'description' => 'Gestion employés et congés'],
            ['name' => 'Comptable', 'description' => 'Gestion de la paie et fiches'],
            ['name' => 'Employé', 'description' => 'Accès limité à son profil'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
