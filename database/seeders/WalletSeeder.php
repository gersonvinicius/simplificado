<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WalletSeeder extends Seeder
{
    public function run()
    {
        // Carteira do usuário comum
        DB::table('wallets')->insert([
            'user_id' => 1, // ID do primeiro usuário (ajustar se necessário)
            'balance' => 1000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Carteira do usuário lojista
        DB::table('wallets')->insert([
            'user_id' => 2, // ID do segundo usuário (ajustar se necessário)
            'balance' => 5000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

