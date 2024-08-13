<?php

namespace App\Repositories;

use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletRepository
{
    public function findByUserId($userId)
    {
        return DB::select('SELECT * FROM wallets WHERE user_id = ?', [$userId]);
    }

    public function save(Wallet $wallet)
    {
        $wallet->save();
    }

    public function updateBalance($userId, $amount)
    {
        return DB::update('UPDATE wallets SET balance = balance + ? WHERE user_id = ?', [$amount, $userId]);
    }
}
