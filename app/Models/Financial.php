<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Financial extends Model
{
    use HasFactory;

    protected $table = 'financial';

    protected $fillable = [
        'order_id',
        'value',
        'paid_value',
        'charge_date',
        'due_date',
        'payment_method',
        'description',
        'obs',
        'charge_paid',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code_url',
        'pix_qr_code',
        'asaas_payment_id',
    ];

    // Relacionamento com Order (pedido)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function asaas()
    {
        return $this->hasOne(FinancialAsaas::class, 'financial_id');
    }

    public function edp()
    {
        return $this->hasOne(FinancialEdp::class, 'financial_id');
    }

    public function asaasData(): HasOne
    {
        return $this->hasOne(FinancialAsaas::class, 'financial_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FinancialLog::class, 'financial_id')
            ->orderByDesc('event_date')
            ->orderByDesc('id');
    }

    public function getInvoiceUrlAttribute(): ?string
    {
        return $this->asaasData?->invoice_url;
    }

    public function getBankSlipUrlAttribute(): ?string
    {
        return $this->asaasData?->bank_slip_url;
    }

    public function getPixQrCodeUrlAttribute(): ?string
    {
        return $this->asaasData?->pix_qr_code_url;
    }

    public function getPixQrCodeAttribute(): ?string
    {
        return $this->asaasData?->pix_qr_code;
    }

    public function getAsaasPaymentIdAttribute(): ?string
    {
        return $this->asaasData?->asaas_payment_id;
    }
}