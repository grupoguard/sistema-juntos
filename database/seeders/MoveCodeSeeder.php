<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoveCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('move_codes')->insert([
            ['code' => '1', 'description' => 'Exclusão automática por alteração cadastral'],
            ['code' => '2', 'description' => 'Inclusão automática'],
            ['code' => '3', 'description' => 'Inconsistência'],
            ['code' => '4', 'description' => 'Processando (Parcelamento)'],
            ['code' => '5', 'description' => 'Exclusão automática por revisão de conta'],
            ['code' => '6', 'description' => 'Incluído pela EDP'],
        ]);
    }
}
