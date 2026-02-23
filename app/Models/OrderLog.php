<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'table_ajust',
        'obj_antes_alteracao',
        'obj_depois_alteracao',
        'order_status',
    ];

    protected $casts = [
        'obj_antes_alteracao' => 'array',
        'obj_depois_alteracao' => 'array',
    ];
}
