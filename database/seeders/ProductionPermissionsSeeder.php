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

            // Adicionais
            'aditionals.view',
            'aditionals.create',
            'aditionals.edit',
            'aditionals.delete',

             // Product Aditionals
            'product_aditionals.view',
            'product_aditionals.create',
            'product_aditionals.edit',
            'product_aditionals.delete',
            
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

            // Calendar EDP
            'calendar.view',
            'calendar.create',
            'calendar.edit',
            'calendar.delete',

            // Anomaly Codes
            'anomaly.view',
            'anomaly.create',
            'anomaly.edit',
            'anomaly.delete',

            // Comission
            'comission.view',
            'comission.create',
            'comission.edit',
            'comission.delete',

            // Dependents
            'dependents.view',
            'dependents.create',
            'dependents.edit',
            'dependents.delete',

            // Employees
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Evidences
            'evidences.view',

            // Return Codes
            'return.view',
            'return.create',
            'return.edit',
            'return.delete',

            // Partner
            'partner.view',
            'partner.create',
            'partner.edit',
            'partner.delete',

            // Partner Categories
            'partner_categories.view',
            'partner_categories.create',
            'partner_categories.edit',
            'partner_categories.delete',

            // Partner Plans
            'partner_plans.view',
            'partner_plans.create',
            'partner_plans.edit',
            'partner_plans.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web',]);
        }

        // ============ CRIAR ROLES ============
         
        $admin = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $coop = Role::firstOrCreate(['name' => 'COOP', 'guard_name' => 'web']);
        $seller = Role::firstOrCreate(['name' => 'SELLER', 'guard_name' => 'web']);
        $financial = Role::firstOrCreate(['name' => 'FINANCIAL', 'guard_name' => 'web']);

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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
