<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialAsaas extends Model
{
    use HasFactory;

    protected $table = 'financial_asaas';

    protected $fillable = [
        'financial_id',
        'asaas_payment_id',
        'asaas_customer_id',
        'external_reference',
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code',
        'pix_qr_code_url',
    ];

    public function financial()
    {
        return $this->belongsTo(Financial::class, 'financial_id');
    }
}