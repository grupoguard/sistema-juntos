<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesModel extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = ['name'];

    public function userAccess()
    {
        return $this->hasMany(UserAccessModel::class, 'role_id');
    }
}
