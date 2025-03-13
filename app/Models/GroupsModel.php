<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupsModel extends Model
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
}
