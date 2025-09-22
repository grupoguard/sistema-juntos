<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comission extends Model
{
    use HasFactory;

    protected $table = 'comission';

    protected $fillable = ['product_id', 'start', 'end', 'value'];

    // Relacionamento com Product (uma comissão pertence a um produto)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
