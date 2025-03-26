<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetornoArmazenado extends Model
{
    protected $table = 'retornos_armazenados';
    protected $fillable = ['arquivo_id', 'nome_arquivo', 'baixado_em', 'processado', 'processado_em'];
}
