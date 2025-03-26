<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogRegister extends Model
{
    use HasFactory;
    protected $table = 'log_register';
    protected $fillable = ['register_code', 'installation_number', 'extra_value', 'product_cod', 'number_installment', 'value_installment', 'future1', 'city_code', 'start_date', 'end_date', 'address', 'name', 'future2', 'code_anomaly', 'code_move'];

    public function anomalyCode()
    {
        return $this->belongsTo(AnomalyCode::class, 'code_anomaly', 'code');
    }

    public function moveCode()
    {
        return $this->belongsTo(MoveCode::class, 'code_move', 'code');
    }
}
