<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'code',
        'name',
        'value',
        'accession',
        'dependents_limit',
        'recurrence',
        'lack',
        'status'
    ];

    // Relacionamento com Comission (um produto pode ter várias comissões)
    public function commissions()
    {
        return $this->hasMany(Comission::class, 'product_id');
    }

    // Relacionamento com Aditionals (um produto pode ter vários adicionais)
    public function additionals()
    {
        return $this->belongsToMany(Aditional::class, 'product_aditionals', 'product_id', 'aditional_id')
        ->withPivot('value') // Pega o campo 'value' da tabela pivô
        ->withTimestamps();
    }
    

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');
    }
}
