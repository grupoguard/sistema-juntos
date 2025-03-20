<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients'; // Nome da tabela no banco

    protected $fillable = [
        'group_id',
        'name',
        'mom_name',
        'date_birth',
        'cpf',
        'rg',
        'gender',
        'marital_status',
        'phone',
        'email',
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
     * Relacionamento com a tabela Group (muitos clientes pertencem a um grupo)
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
