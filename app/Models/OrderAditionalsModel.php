<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderAditionalsModel extends Pivot
{
    use HasFactory;

    protected $table = 'order_aditionals';

    protected $fillable = [
        'order_id',
        'product_aditional_id',
        'value',
    ];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(OrdersModel::class, 'order_id');
    }

    // Relacionamento com Aditional (adicional)
    public function productAditional()
    {
        return $this->belongsTo(ProductAditionalsModel::class);
    }
}
