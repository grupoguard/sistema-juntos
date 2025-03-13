<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RolesModel;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['ADMIN', 'COOP', 'SELLER', 'FINANCIAL'];

        foreach ($roles as $role) {
            RolesModel::create(['name' => $role]);
        }
    }
}
