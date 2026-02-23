<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Garante que a role existe no guard web
        Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@juntosbeneficios.com.br'],
            [
                'name' => 'admin',
                'password' => Hash::make('secret'),
                'status' => true,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'admin@cartaojuntos.com.br'],
            [
                'group_id' => null,
                'name' => 'Administrador',
                'date_birth' => '1990-01-01',
                'cpf' => '11111111111',
                'rg' => '123456789',
                'phone' => '11999999999',
                'zipcode' => '12345678',
                'address' => 'Rua dos Administradores',
                'number' => '100',
                'complement' => 'Sala 1',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'obs' => 'Usuário administrador'
            ]
        );

        // Atribui a role ao usuário criado
        $adminUser->syncRoles(['ADMIN']);
    }
}
