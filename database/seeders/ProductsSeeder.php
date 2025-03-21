<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            [
                'name' => 'Juntos Saude',
                'code' => 408,
                'value' => 29.90,
                'accession' => 0.00,
                'dependents_limit' => 3,
                'recurrence' => 'mensal',
                'lack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juntos Bem Estar',
                'code' => 409,
                'value' => 39.90,
                'accession' => 0.00,
                'dependents_limit' => 3,
                'recurrence' => 'mensal',
                'lack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juntos Vida',
                'code' => 410,
                'value' => 39.90,
                'accession' => 0.00,
                'dependents_limit' => 3,
                'recurrence' => 'mensal',
                'lack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juntos Odonto',
                'code' => 411,
                'value' => 49.90,
                'accession' => 0.00,
                'dependents_limit' => 3,
                'recurrence' => 'mensal',
                'lack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juntos Família',
                'code' => 412,
                'value' => 39.90,
                'accession' => 0.00,
                'dependents_limit' => 3,
                'recurrence' => 'mensal',
                'lack' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
