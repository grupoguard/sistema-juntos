<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsaasWebhookEvents extends Model
{
    use HasFactory;

    protected $table = 'asaas_webhook_logs';

    protected $fillable = [
        'event_type', 
        'asaas_payment_id',
        'payload',
        'processed', 
        'processed_at', 
        'processing_error',
    ];
}
