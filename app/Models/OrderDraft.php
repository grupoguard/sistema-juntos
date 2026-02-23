<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDraft extends Model
{
    protected $fillable = [
        'user_id',
        'group_id',
        'seller_id',
        'client_id',
        'status',
        'current_step',
        'payload',
        'document_file',
        'document_file_type',
        'address_proof_file',
        'last_interaction_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_interaction_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}