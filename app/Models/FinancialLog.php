<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialLog extends Model
{
    use HasFactory;

    protected $table = 'financial_logs';

    protected $fillable = [
        'financial_id',
        'provider',
        'source_type',
        'source_id',
        'event_name',
        'old_status',
        'new_status',
        'message',
        'payload',
        'event_date',
    ];

    protected $casts = [
        'payload' => 'array',
        'event_date' => 'datetime',
    ];

    public function financial()
    {
        return $this->belongsTo(Financial::class, 'financial_id');
    }
}