<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReturnCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('return_codes')->insert([
            ['code' => '01', 'description' => 'Faturamento do serviço'],
            ['code' => '03', 'description' => 'Não faturado'],
            ['code' => '04', 'description' => 'Devolução por revisão'],
            ['code' => '05', 'description' => 'Cobrança por revisão'],
            ['code' => '06', 'description' => 'Baixa do serviço'],
            ['code' => '07', 'description' => 'Volta a débito'],
        ]);
    }
}
