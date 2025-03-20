<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $table = 'financial';

    protected $fillable = ['order_id', 'value', 'paid_value', 'charge_date', 'charge_paid', 'status'];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
