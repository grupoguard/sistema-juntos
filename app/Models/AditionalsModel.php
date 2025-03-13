<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AditionalsModel extends Model
{
    use HasFactory;

    protected $table = 'aditionals';

    protected $fillable = ['name', 'value'];

    /**
     * Relacionamento muitos-para-muitos com Orders (adicionais de um pedido)
     */
    public function orders()
    {
        return $this->belongsToMany(OrdersModel::class, 'order_aditionals')
            ->withTimestamps();
    }

    /**
     * Relacionamento muitos-para-muitos com Products (adicionais disponíveis para um produto)
     */
    public function products()
    {
        return $this->belongsToMany(ProductsModel::class, 'product_aditionals')
            ->withTimestamps();
    }
}
