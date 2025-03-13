<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenceDocumentsModel extends Model
{
    use HasFactory;
    protected $table = 'evidence_documents';

    protected $fillable = ['order_id', 'document'];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(OrdersModel::class, 'order_id');
    }
}
