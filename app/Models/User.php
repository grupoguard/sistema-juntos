<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'password' => 'hashed',
        'status' => 'boolean',
        'email_verified_at' => 'datetime'
    ];

    /**
     * Relacionamento com Groups (COOP)
     * Um usuário COOP pode ter acesso a um ou mais groups
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'user_groups')
                    ->withTimestamps();
    }

    /**
     * Relacionamento com Sellers
     * Um usuário SELLER é vinculado a um seller
     */
    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'user_sellers')
                    ->withTimestamps();
    }

    /**
     * Pega todos os group_ids que o usuário tem acesso
     */
    public function getAccessibleGroupIds()
    {
        if ($this->hasRole('ADMIN')) {
            // Admin vê todos os groups
            return Group::pluck('id')->toArray();
        }

        if ($this->hasRole('COOP')) {
            // COOP vê apenas seus groups
            return $this->groups->pluck('id')->toArray();
        }

        if ($this->hasRole('SELLER')) {
            // SELLER vê apenas o group do seu seller
            return $this->sellers->pluck('group_id')->unique()->toArray();
        }

        return [];
    }

    /**
     * Pega todos os seller_ids que o usuário tem acesso
     */
    public function getAccessibleSellerIds()
    {
        if ($this->hasRole('ADMIN')) {
            // Admin vê todos os sellers
            return Seller::pluck('id')->toArray();
        }

        if ($this->hasRole('COOP')) {
            // COOP vê sellers do seu group
            $groupIds = $this->groups->pluck('id');
            return Seller::whereIn('group_id', $groupIds)->pluck('id')->toArray();
        }

        if ($this->hasRole('SELLER')) {
            // SELLER vê apenas ele mesmo
            return $this->sellers->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Verifica se tem acesso a um group específico
     */
    public function hasAccessToGroup($groupId): bool
    {
        return in_array($groupId, $this->getAccessibleGroupIds());
    }

    /**
     * Verifica se tem acesso a um seller específico
     */
    public function hasAccessToSeller($sellerId): bool
    {
        return in_array($sellerId, $this->getAccessibleSellerIds());
    }
}
