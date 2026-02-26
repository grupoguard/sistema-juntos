<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients'; // Nome da tabela no banco

    protected $fillable = [
        'asaas_customer_id',
        'group_id',
        'name',
        'mom_name',
        'date_birth',
        'cpf',
        'rg',
        'gender',
        'marital_status',
        'phone',
        'email',
        'zipcode',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'obs',
        'status',
    ];

    // Remove pontos e traços do CPF antes de salvar
    public function setCpfAttribute($value)
    {
        $this->attributes['cpf'] = preg_replace('/\D/', '', $value);
    }
 
    // Remove pontos e traços do RG antes de salvar
    public function setRgAttribute($value)
    {
        $this->attributes['rg'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Relacionamento com a tabela Group (muitos clientes pertencem a um grupo)
    */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function dependents()
    {
        return $this->hasMany(Dependent::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) return $query;

        if ($user->isCoop()) {
            $groupIds = $user->getAccessibleGroupIds();
            return empty($groupIds) ? $query->whereRaw('1=0') : $query->whereIn('group_id', $groupIds);
        }

        if ($user->isSeller()) {
            $sellerIds = $user->getAccessibleSellerIds();
            if (empty($sellerIds)) return $query->whereRaw('1=0');

            return $query->whereIn('id', function ($sub) use ($sellerIds) {
                $sub->select('client_id')
                    ->from('orders')
                    ->whereIn('seller_id', $sellerIds)
                    ->groupBy('client_id');
            });
        }

        return $query->whereRaw('1=0');
    }
}
