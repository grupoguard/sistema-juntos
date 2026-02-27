<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

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
        'canceled_at',
        'document_file',
        'document_file_type',
        'address_proof_file',
        'review_status',
        'admin_viewed_at',
        'admin_viewed_by',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'signed_contract_url',
        'signed_physical_contract_file',
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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCoop()) {
            $groupIds = $user->getAccessibleGroupIds();

            // segurança: se não tiver groups vinculados, não retorna nada
            if (empty($groupIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('group_id', $groupIds);
        }

        if ($user->isSeller()) {
            $sellerIds = $user->getAccessibleSellerIds();

            if (empty($sellerIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('seller_id', $sellerIds);
        }

        // fallback seguro
        return $query->whereRaw('1 = 0');
    }

    public function adminViewer()
    {
        return $this->belongsTo(User::class, 'admin_viewed_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'PENDENTE');
    }

    public function markAsViewedBy(User $user): void
    {
        if (is_null($this->admin_viewed_at)) {
            $this->update([
                'admin_viewed_at' => now(),
                'admin_viewed_by' => $user->id,
            ]);
        }
    }

    public function financials()
    {
        return $this->hasMany(Financial::class, 'order_id');
    }
}
