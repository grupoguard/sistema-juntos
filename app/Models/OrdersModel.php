<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersModel extends Model
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
        'evidence_date',
        'audio',
        'charge_date',
        'accession',
    ];

    /**
     * Relacionamento com o cliente (cada pedido pertence a um cliente)
     */
    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_id');
    }

    /**
     * Relacionamento com o produto (cada pedido tem um produto)
     */
    public function product()
    {
        return $this->belongsTo(ProductsModel::class, 'product_id');
    }

    /**
     * Relacionamento com o grupo (cada pedido pertence a um grupo)
     */
    public function group()
    {
        return $this->belongsTo(GroupsModel::class, 'group_id');
    }

    /**
     * Relacionamento com o vendedor (cada pedido pertence a um vendedor)
     */
    public function seller()
    {
        return $this->belongsTo(SellersModel::class, 'seller_id');
    }

    /**
     * Relacionamento muitos-para-muitos com Dependents (dependentes selecionados no pedido)
     */
    public function dependents()
    {
        return $this->belongsToMany(DependentsModel::class, 'order_dependents')
            ->withTimestamps();
    }

    public function orderPrice()
    {
        return $this->hasOne(OrderPricesModel::class);
    }

    public function orderAditionals()
    {
        return $this->hasMany(OrderAditionalsModel::class);
    }
}
