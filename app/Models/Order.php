<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'group_id',
        'seller_id',
        'charge_type',
        'installation_number',
        'approval_name',
        'approval_by',
        'evidence_date',
        'charge_date',
        'accession',
        'accession_payment',
        'discount_type',
        'discount_value',   
    ];

    /**
     * Relacionamento com o cliente (cada pedido pertence a um cliente)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relacionamento com o produto (cada pedido tem um produto)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relacionamento com o grupo (cada pedido pertence a um grupo)
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Relacionamento com o vendedor (cada pedido pertence a um vendedor)
     */
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Relacionamento muitos-para-muitos com Dependents (dependentes selecionados no pedido)
     */
    public function dependents()
    {
        return $this->belongsToMany(Dependent::class, 'order_dependents')
            ->withTimestamps();
    }

    public function orderPrice()
    {
        return $this->hasOne(OrderPrice::class);
    }

    public function orderAditionals()
    {
        return $this->hasMany(OrderAditional::class);
    }

    public function orderAditionalDependents()
    {
        return $this->hasMany(OrderAditionalDependent::class, 'order_id');
    }

    public function evidences()
    {
        return $this->hasMany(EvidenceDocument::class);
    }
}
