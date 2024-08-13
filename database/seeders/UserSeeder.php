<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Usuário comum
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Example',
                'cpf' => '12345678901',
                'password' => Hash::make('password'),
                'type' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Usuário lojista
        User::firstOrCreate(
            ['email' => 'merchant@example.com'],
            [
                'name' => 'Merchant Example',
                'cpf' => '10987654321',
                'password' => Hash::make('password'),
                'type' => 'merchant',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

