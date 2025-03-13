<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            'name' => 'Juntos Saude',
            'code' => 408,
            'value' => 29.90,
            'accession' => 0.00,
            'dependents_limit' => 3,
            'recurrence' => 'mensal',
            'lack' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
