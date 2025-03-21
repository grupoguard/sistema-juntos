<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminModel;
use App\Models\Role;
use App\Models\RolesModel;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\UserAccessModel;
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
        $adminUser = User::create([
                'name' => 'admin',
                'email' => 'admin@juntosbeneficios.com.br',
                'password' => Hash::make('secret'),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(), 
        ]);

        $adminData = Admin::create([
            'group_id' => null,
            'name' => 'Administrador',
            'date_birth' => '1990-01-01',
            'cpf' => '11111111111',
            'rg' => '123456789',
            'phone' => '11999999999',
            'email' => 'admin@cartaojuntos.com.br',
            'zipcode' => '12345678',
            'address' => 'Rua dos Administradores',
            'number' => '100',
            'complement' => 'Sala 1',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'obs' => 'Usuário administrador'
        ]);

        UserAccess::create([
            'user_id' => $adminUser->id,
            'group_id' => null, // Admin global
            'userable_id' => $adminData->id,
            'userable_type' => Admin::class,
            'role_id' => Role::where('name', 'ADMIN')->first()->id
        ]);
    }
}
