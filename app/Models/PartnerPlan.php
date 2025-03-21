<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPlan extends Model
{
    use HasFactory;
    protected $table = 'partner_plans';

    protected $fillable = [
        'partner_id',
        'category_id',
        'particular_price',
        'juntos_price',
        'description'
    ];

    // Relacionamento com Partner (parceiro)
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    // Relacionamento com PartnerCategory (categoria do parceiro)
    public function category()
    {
        return $this->belongsTo(PartnersCategorie::class, 'category_id');
    }
}