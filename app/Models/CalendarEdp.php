<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEdp extends Model
{
    use HasFactory;
    protected $table = 'calendar_edp';
    protected $fillable = ['date'];
}
