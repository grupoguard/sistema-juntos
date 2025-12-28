<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderAditional extends Pivot
{
    use HasFactory;

    protected $table = 'order_aditionals';

    protected $fillable = [
        'order_id',
        'aditional_id',
        'value',
    ];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relacionamento com Aditional (adicional)
    public function productAditional()
    {
        return $this->belongsTo(ProductAditional::class);
    }
}
