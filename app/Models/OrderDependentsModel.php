<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderDependentsModel extends Pivot
{
    use HasFactory;

    protected $table = 'order_dependents';

    protected $fillable = ['order_id', 'dependent_id'];

     // Relacionamento com Order (pedido)
     public function order()
     {
         return $this->belongsTo(OrdersModel::class, 'order_id');
     }
 
     // Relacionamento com Dependent (dependente)
     public function dependent()
     {
         return $this->belongsTo(DependentsModel::class, 'dependent_id');
     }
}
