<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceReturn extends Model
{
    use HasFactory;

    protected $table = 'evidence_return';

    protected $fillable = ['order_id', 'status'];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
