<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => 'admin@juntosbeneficios.com.br',
                'password' => Hash::make('secret'),
                'created_at' => now(),
                'updated_at' => now()
            ], 
            [
                'name' => 'dev',
                'email' => 'contato@webcube.com.br',
                'password' => Hash::make('secret'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
