<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('groups')->insert([
            'group_name' => 'Juntos Sede',
            'name' => 'Juntos Sede',
            'document' => 53802618000131,
            'phone' => 1239393939,
            'site' => 'www.juntosbeneficios.com.br',
            'zipcode' => 12242010,
            'address' => 'Av. Dep. Benedito Matarazzo',
            'number' => 7151,
            'complement' => 'Sala 1',
            'neighborhood' => 'Jardim Aquarius',
            'city' => 'São José dos Campos',
            'state' => 'SP',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
