<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependent extends Model
{
    use HasFactory;

    protected $table = 'dependents'; // Nome da tabela no banco

    protected $fillable = [
        'client_id',
        'name',
        'mom_name',
        'date_birth',
        'cpf',
        'rg',
        'marital_status',
        'relationship',
    ];

    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D/', '', $value);
    }

    public function setRgAttribute($value)
    {
        $this->attributes['rg'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Relacionamento com a tabela Client (um dependente pertence a um cliente)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
