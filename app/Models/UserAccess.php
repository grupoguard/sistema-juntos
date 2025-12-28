<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAccess extends Model
{
    use HasFactory;

    protected $table = 'user_access';
    protected $fillable = ['user_id', 'group_id', 'userable_type', 'userable_id', 'role_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}
