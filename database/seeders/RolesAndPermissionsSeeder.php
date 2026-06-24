<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Companies
            'view_companies', 'manage_companies',
            // Branches
            'view_branches', 'manage_branches',
            // Products
            'view_products', 'manage_products',
            // Categories
            'view_categories', 'manage_categories',
            // Tables
            'view_tables', 'manage_tables',
            // Orders
            'view_orders', 'create_orders', 'cancel_orders', 'manage_orders',
            // Payments
            'process_payments',
            // Customers
            'view_customers', 'manage_customers',
            // Inventory
            'view_inventory', 'manage_inventory',
            // Discounts / Vouchers
            'manage_discounts',
            // Staff
            'view_staff', 'manage_staff',
            // Reports
            'view_reports', 'view_advanced_reports',
            // Subscriptions
            'manage_subscriptions',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Super Admin: all permissions (via gate, not explicit assignment)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);

        // Owner: everything in their company
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions($permissions);

        // Branch Manager: branch-level management
        $manager = Role::firstOrCreate(['name' => 'branch_manager']);
        $manager->syncPermissions([
            'view_branches',
            'view_products', 'manage_products',
            'view_categories',
            'view_tables', 'manage_tables',
            'view_orders', 'create_orders', 'cancel_orders', 'manage_orders',
            'process_payments',
            'view_customers', 'manage_customers',
            'view_inventory', 'manage_inventory',
            'manage_discounts',
            'view_staff',
            'view_reports',
        ]);

        // Cashier: POS-only
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->syncPermissions([
            'view_products',
            'view_tables',
            'view_orders', 'create_orders', 'cancel_orders',
            'process_payments',
            'view_customers',
            'view_inventory',
        ]);
    }
}
