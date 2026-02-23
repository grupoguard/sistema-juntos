<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedReturn extends Model
{
    protected $fillable = [
        'record_type',
        'line_content',
        'error_message',
        'arquivo_data',
        'processed',
        'processed_at',
        'resolution_note'
    ];

    protected $casts = [
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'arquivo_data' => 'date'
    ];

    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}
