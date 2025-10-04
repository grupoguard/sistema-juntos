<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsaasLogs extends Model
{
    use HasFactory;

    protected $table = 'asaas_logs';

    protected $fillable = [
        'asaas_id', 
        'entity_type',
        'request_data',
        'response_data', 
        'status', 
        'error_message', 
        'ip_address',
    ];
}
