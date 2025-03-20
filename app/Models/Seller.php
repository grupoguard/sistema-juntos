<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $table = 'sellers'; // Nome da tabela no banco

    protected $fillable = [
        'group_id',
        'name',
        'date_birth',
        'cpf',
        'rg',
        'phone',
        'email',
        'comission_type',
        'comission_value',
        'comission_recurrence',
        'zipcode',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'obs',
        'status',
    ];

    /**
     * Relacionamento com a tabela Group (vários vendedores pertencem a um grupo)
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
