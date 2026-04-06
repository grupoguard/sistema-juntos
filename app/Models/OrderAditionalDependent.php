<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAditionalDependent extends Model
{
    use HasFactory;

    protected $table = 'order_aditionals_dependents';

    protected $fillable = ['order_id', 'dependent_id', 'aditional_id', 'value'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function dependent()
    {
        return $this->belongsTo(Dependent::class, 'dependent_id');
    }

    public function aditional()
    {
        return $this->belongsTo(Aditional::class, 'aditional_id');
    }
}