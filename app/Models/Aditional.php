<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aditional extends Model
{
    use HasFactory;

    protected $table = 'aditionals';

    protected $fillable = [
        'name',
        'status' 
    ];

    protected $attributes = [
        'status' => true,
    ];

    /**
     * Relacionamento muitos-para-muitos com Orders (adicionais de um pedido)
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_aditionals')
            ->withTimestamps();
    }

    /**
     * Relacionamento muitos-para-muitos com Products (adicionais disponíveis para um produto)
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_aditionals')
            ->withTimestamps();
    }
}
