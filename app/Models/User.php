<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'email_verified_at' => 'datetime',
    ];

    public function access()
    {
        return $this->hasOne(UserAccess::class, 'user_id');
    }
    
    // Relacionamento para obter a role correta
    public function role()
    {
        return $this->hasOneThrough(Role::class, UserAccess::class, 'user_id', 'id', 'id', 'role_id');
    }

    // Método para retornar o nome da role
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : 'Sem Permissão';
    }
}
