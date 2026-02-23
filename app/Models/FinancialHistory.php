<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialHistory extends Model
{
    protected $table = 'financial_history';
    
    protected $fillable = [
        'financial_id',
        'old_status',
        'new_status',
        'reason',
        'changed_by',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function financial()
    {
        return $this->belongsTo(Financial::class);
    }
}
