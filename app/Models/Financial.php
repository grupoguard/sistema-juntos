<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $table = 'financial';

    protected $fillable = [
        'order_id', 
        'asaas_payment_id',
        'asaas_customer_id',
        'value', 
        'paid_value', 
        'charge_date', 
        'due_date',
        'payment_method',
        'external_reference',
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code',
        'pix_qr_code_url',
        'description',
        'charge_paid', 
        'status'
    ];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
