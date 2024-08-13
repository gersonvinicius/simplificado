<?php

namespace App\Repositories;

use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransferRepository
{
    public function findById($id)
    {
        $result = DB::select('SELECT * FROM transfers WHERE id = ?', [$id]);

        // Verifica se encontrou o registro e transforma o array em um objeto
        if (count($result) > 0) {
            return (object) $result[0];
        }

        return null;
    }

    public function create(array $data)
    {
        $id = DB::table('transfers')->insertGetId([
            'payer_id' => $data['payer_id'],
            'payee_id' => $data['payee_id'],
            'value' => $data['value'],
            'status' => $data['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->findById($id);
    }

    public function updateStatus($id, $status)
    {
        return DB::update('UPDATE transfers SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $id]);
    }
}
