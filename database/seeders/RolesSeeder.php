<?php

namespace Database\Seeders;

use App\Models\Role;
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
            Role::create(['name' => $role]);
        }
    }
}
