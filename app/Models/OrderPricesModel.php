<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPricesModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'base_price',
        'dependent_price',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(OrdersModel::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductsModel::class);
    }
}
