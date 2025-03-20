<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $fillable = ['group_id', 'name', 'date_birth', 'cpf', 'rg', 'phone', 'email', 'zipcode', 'address', 'number', 'complement', 'neighborhood', 'city', 'state', 'obs'];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
