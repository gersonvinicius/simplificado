<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payer_id',
        'payee_id',
        'value',
        'status',
    ];

    /**
     * Relacionamento com a model User para o pagador
     */
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    /**
     * Relacionamento com a model User para o recebedor
     */
    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
