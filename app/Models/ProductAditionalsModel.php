<?php

namespace App\Models;

use Google\Service\Cloudchannel\Resource\ProductsSkus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductAditionalsModel extends Pivot
{
    use HasFactory;

    protected $table = 'product_aditionals';

    protected $fillable = ['product_id', 'aditional_id'];

    // Relacionamento com Product
    public function product()
    {
        return $this->belongsTo(ProductsModel::class, 'product_id');
    }

    // Relacionamento com Aditional (adicionais do produto)
    public function aditional()
    {
        return $this->belongsTo(AditionalsModel::class, 'aditional_id');
    }
}
