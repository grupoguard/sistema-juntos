<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProductionPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============ CRIAR PERMISSIONS ============
        
        $permissions = [
            // Dashboard
            'view-dashboard',
            
            // Groups/Cooperativas
            'groups.view',
            'groups.create',
            'groups.edit',
            'groups.delete',
            
            // Sellers/Vendedores
            'sellers.view',
            'sellers.create',
            'sellers.edit',
            'sellers.delete',
            
            // Clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            
            // Orders/Pedidos
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.cancel',
            
            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Financial
            'financial.view',
            'financial.create',
            'financial.edit',
            'financial.delete',
            'financial.reports',
            'financial.export',
            
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Reports
            'reports.sales',
            'reports.commissions',
            'reports.general',
            'reports.export',
            
            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ============ CRIAR ROLES ============
        
        $admin = Role::firstOrCreate(['name' => 'ADMIN']);
        $coop = Role::firstOrCreate(['name' => 'COOP']);
        $seller = Role::firstOrCreate(['name' => 'SELLER']);
        $financial = Role::firstOrCreate(['name' => 'FINANCIAL']);

        // ============ ATRIBUIR PERMISSIONS ============
        
        // ADMIN - Tudo
        $admin->syncPermissions(Permission::all());

        // COOP - Gerenciar seus vendedores, clientes e pedidos
        $coop->syncPermissions([
            'view-dashboard',
            'sellers.view',
            'sellers.create',
            'sellers.edit',
            'clients.view',
            'clients.create',
            'clients.edit',
            'orders.view',
            'orders.create',
            'orders.edit',
            'products.view',
            'financial.view',
            'reports.sales',
            'reports.commissions',
        ]);

        // SELLER - Apenas suas vendas
        $seller->syncPermissions([
            'view-dashboard',
            'clients.view',
            'clients.create',
            'clients.edit',
            'orders.view',
            'orders.create',
            'products.view',
            'reports.sales',
            'reports.commissions',
        ]);

        // FINANCIAL - Apenas financeiro
        $financial->syncPermissions([
            'view-dashboard',
            'financial.view',
            'financial.create',
            'financial.edit',
            'financial.reports',
            'financial.export',
            'orders.view',
            'clients.view',
            'reports.general',
        ]);

        $this->command->info('✅ Permissões criadas com sucesso!');
    }
}
