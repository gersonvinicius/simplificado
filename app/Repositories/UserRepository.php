<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function findAll()
    {
        return DB::select('SELECT * FROM users');
    }

    public function findById($id)
    {
        return DB::select('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function save(User $user)
    {
        $user->save();
    }

    public function create(array $data)
    {
        $id = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'cpf' => $data['cpf'],
            'email' => $data['email'],
            'password' => $data['password'],
            'type' => $data['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->findById($id);
    }
}
