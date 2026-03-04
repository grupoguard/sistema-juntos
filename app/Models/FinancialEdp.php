<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialEdp extends Model
{
    use HasFactory;

    protected $table = 'financial_edp';

    protected $fillable = [
        'financial_id',
        'first_log_movement_id',
        'last_log_movement_id',
        'confirmed_log_movement_id',
        'received_log_movement_id',
        'last_return_code',
        'last_status',
        'last_event_at',
    ];

    protected $casts = [
        'last_event_at' => 'datetime',
    ];

    public function financial()
    {
        return $this->belongsTo(Financial::class, 'financial_id');
    }

    public function firstLogMovement()
    {
        return $this->belongsTo(LogMovement::class, 'first_log_movement_id');
    }

    public function lastLogMovement()
    {
        return $this->belongsTo(LogMovement::class, 'last_log_movement_id');
    }

    public function confirmedLogMovement()
    {
        return $this->belongsTo(LogMovement::class, 'confirmed_log_movement_id');
    }

    public function receivedLogMovement()
    {
        return $this->belongsTo(LogMovement::class, 'received_log_movement_id');
    }
}