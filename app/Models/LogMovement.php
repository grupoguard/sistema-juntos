<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMovement extends Model
{
    use HasFactory;
    protected $table = 'log_movement';
    protected $fillable = [
        'register_code', 
        'installation_number', 
        'extra_value', 
        'product_cod', 
        'installment', 
        'reading_script', 
        'date_invoice', 
        'city_code', 
        'date_movement', 
        'value', 
        'code_return', 
        'future', 
        'code_move', 
        'arquivo_data'
    ];

    public function returnCode()
    {
        return $this->belongsTo(ReturnCode::class, 'code_return', 'code');
    }

    public function financialEdpFirst()
    {
        return $this->hasMany(FinancialEdp::class, 'first_log_movement_id');
    }

    public function financialEdpLast()
    {
        return $this->hasMany(FinancialEdp::class, 'last_log_movement_id');
    }

    public function financialEdpConfirmed()
    {
        return $this->hasMany(FinancialEdp::class, 'confirmed_log_movement_id');
    }

    public function financialEdpReceived()
    {
        return $this->hasMany(FinancialEdp::class, 'received_log_movement_id');
    }
}
