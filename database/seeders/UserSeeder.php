<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@payflex.com',
            'password' => Hash::make('password'),
            'role_id' => null,
            'active' => 1
        ]);


    }
}
