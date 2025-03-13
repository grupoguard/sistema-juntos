<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnersCategoriesModel extends Model
{
    use HasFactory;
    protected $table = 'partner_categories';
    protected $fillable = ['name'];
}
