<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnomalyCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('anomaly_codes')->insert([
            ['code' => '00', 'description' => 'Sem anomalias'],
            ['code' => '01', 'description' => 'Número de instalação inválido'],
            ['code' => '02', 'description' => 'Data inicial inválida'],
            ['code' => '03', 'description' => 'Data final inválida'],
            ['code' => '04', 'description' => 'Endereço inválido'],
            ['code' => '05', 'description' => 'Código de movimento inválido'],
            ['code' => '06', 'description' => 'Inconsistências nas datas'],
            ['code' => '07', 'description' => 'Classe inválida'],
            ['code' => '08', 'description' => 'Código de situação de cobrança inválido'],
            ['code' => '09', 'description' => 'Número de parcelas inválido'],
            ['code' => '10', 'description' => 'Produto/código valor extra inválido'],
            ['code' => '11', 'description' => 'Sinistro sem seguro'],
            ['code' => '12', 'description' => 'Valor da parcela não numérica'],
            ['code' => '13', 'description' => 'Já têm cadastro ativo'],
            ['code' => '14', 'description' => 'Já excluído ou não Ativo'],
            ['code' => '15', 'description' => 'Suspensa'],
            ['code' => '16', 'description' => 'Desligada'],
            ['code' => '17', 'description' => 'Duplicidade Operando/Transferência de Titularidade'],
            ['code' => '18', 'description' => 'Atualização de Operando'],
        ]);
        
    }
}
