<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Seller extends Model
{
    use HasFactory;

    protected $table = 'sellers'; // Nome da tabela no banco

    protected $fillable = [
        'group_id',
        'migration_id',
        'name',
        'date_birth',
        'cpf',
        'rg',
        'phone',
        'email',
        'comission_type',
        'comission_value',
        'comission_recurrence',
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

     public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isCoop()) {
            $groupIds = $user->getAccessibleGroupIds();

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

            return $query->whereIn('id', $sellerIds);
        }

        return $query->whereRaw('1 = 0');
    }
}
