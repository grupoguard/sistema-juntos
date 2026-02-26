<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
 use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups'; // Nome da tabela no banco

    protected $fillable = [
        'group_name',
        'name',
        'document',
        'phone',
        'email',
        'whatsapp',
        'site',
        'zipcode',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'status',
        'obs',
    ];

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

            return $query->whereIn('id', $groupIds);
        }

        return $query->whereRaw('1 = 0');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(\App\Models\Order::class, 'group_id');
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(\App\Models\Seller::class, 'group_id');
    }
}
