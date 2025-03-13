<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMovementModel extends Model
{
    use HasFactory;
    protected $table = 'log_movement';
    protected $fillable = ['register_code', 'installation_number', 'extra_value', 'product_cod', 'installment', 'reading_script', 'date_invoice', 'city_code', 'date_movement', 'value', 'code_return', 'future', 'code_move'];
}
