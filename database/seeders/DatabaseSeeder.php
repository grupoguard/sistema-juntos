<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            GroupsSeeder::class,
            UserSeeder::class,
            ProductsSeeder::class,
            AnomalyCodeSeeder::class,
            MoveCodeSeeder::class,
            ReturnCodeSeeder::class,
            ProductionPermissionsSeeder::class
        ]);
    }
}
