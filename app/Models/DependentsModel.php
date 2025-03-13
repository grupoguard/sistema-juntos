<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DependentsModel extends Model
{
    use HasFactory;

    protected $table = 'dependents'; // Nome da tabela no banco

    protected $fillable = [
        'client_id',
        'name',
        'date_birth',
        'cpf',
        'rg',
        'relationship',
    ];

    /**
     * Relacionamento com a tabela Client (um dependente pertence a um cliente)
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }
}
