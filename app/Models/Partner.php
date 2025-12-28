<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;
    protected $table = 'partners';
    protected $fillable = ['company_name', 'fantasy_name', 'cnpj', 'phone', 'email', 'whatsapp', 'site', 'zipcode', 'address', 'number', 'complement', 'neighborhood', 'city', 'state'];

    public function partnerPlans()
    {
        return $this->hasMany(PartnerPlan::class);
    }

}