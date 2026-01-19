<?php

// Service para gerenciar usuários
namespace App\Services;

use App\Models\User;
use App\Models\Group;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementService
{
    /**
     * Cria usuário para um Group (COOP)
     */
    public function createUserForGroup(Group $group, ?string $password = null)
    {
        return DB::transaction(function () use ($group, $password) {
            // Gerar senha aleatória se não fornecida
            $generatedPassword = $password ?: Str::random(12);
            
            // Criar usuário
            $user = User::create([
                'name' => $group->name,
                'email' => $group->email,
                'password' => Hash::make($generatedPassword),
                'status' => true
            ]);

            // Atribuir role COOP
            $user->assignRole('COOP');

            // Adicionar permissões específicas do grupo
            $this->assignGroupPermissions($user, $group);

            return [
                'user' => $user,
                'password' => $generatedPassword // Retornar para mostrar ao criador
            ];
        });
    }

    /**
     * Cria usuário para um Seller
     */
    public function createUserForSeller(Seller $seller, ?string $password = null)
    {
        return DB::transaction(function () use ($seller, $password) {
            // Gerar senha aleatória se não fornecida
            $generatedPassword = $password ?: Str::random(12);
            
            // Criar usuário
            $user = User::create([
                'name' => $seller->name,
                'email' => $seller->email,
                'password' => Hash::make($generatedPassword),
                'status' => true
            ]);

            // Atribuir role SELLER
            $user->assignRole('SELLER');

            // Adicionar permissões específicas do vendedor
            $this->assignSellerPermissions($user, $seller);

            return [
                'user' => $user,
                'password' => $generatedPassword
            ];
        });
    }

    /**
     * Atribui permissões específicas ao grupo
     */
    private function assignGroupPermissions($user, $group)
    {
        // COOP já tem permissões padrão pela role
        // Aqui você pode adicionar permissões específicas se necessário
        
        // Salvar vínculo do user com o group
        $user->groups()->attach($group->id);
    }

    /**
     * Atribui permissões específicas ao vendedor
     */
    private function assignSellerPermissions($user, $seller)
    {
        // SELLER já tem permissões padrão pela role
        // Salvar vínculo do user com o seller
        $user->sellers()->attach($seller->id);
    }

    public function createUser(array $userData, array $accessData = [])
    {
        return DB::transaction(function () use ($userData, $accessData) {
            // Criar usuário
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'status' => $userData['status'] ?? true
            ]);

            // Adicionar role se fornecida
            if (isset($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            return $user;
        });
    }

    public function assignUserAccess(User $user, array $accessData)
    {
        foreach ($accessData as $access) {
            UserAccess::create([
                'user_id' => $user->id,
                'group_id' => $access['group_id'],
                'userable_type' => $access['userable_type'],
                'role_id' => $access['role_id']
            ]);
        }
    }

    public function updateUserAccess(User $user, array $newAccessData)
    {
        return DB::transaction(function () use ($user, $newAccessData) {
            // Remove acessos existentes
            $user->userAccesses()->delete();
            
            // Adiciona novos acessos
            $this->assignUserAccess($user, $newAccessData);
            
            return $user->load(['userAccesses.role']);
        });
    }

    public function getAvailableEntities()
    {
        return [
            'App\Models\Admin' => \App\Models\Admin::all(),
            'App\Models\Seller' => \App\Models\Seller::all(), 
            'App\Models\Partner' => \App\Models\Partner::all()
        ];
    }

    public function getUsersByRole($roleName)
    {
        return User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->with(['userAccesses.role'])->get();
    }
}