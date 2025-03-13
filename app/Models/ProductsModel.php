<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
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
        'lack'
    ];

    // Relacionamento com Comission (um produto pode ter várias comissões)
    public function commissions()
    {
        return $this->hasMany(ComissionModel::class, 'product_id');
    }

    // Relacionamento com Aditionals (um produto pode ter vários adicionais)
    public function additionals()
    {
        return $this->belongsToMany(AditionalsModel::class, 'product_aditionals', 'product_id', 'aditional_id');
    }
}
