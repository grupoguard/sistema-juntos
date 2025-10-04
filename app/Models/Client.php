<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients'; // Nome da tabela no banco

    protected $fillable = [
        'asaas_customer_id',
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

    // Remove pontos e traços do CPF antes de salvar
    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D/', '', $value);
    }
 
    // Remove pontos e traços do RG antes de salvar
    public function setRgAttribute($value)
    {
        $this->attributes['rg'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Relacionamento com a tabela Group (muitos clientes pertencem a um grupo)
    */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
