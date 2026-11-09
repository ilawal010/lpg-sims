<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'owner' => [
                'display_name' => 'Owner / Super Admin',
                'description' => 'Full business administration and oversight.',
            ],
            'manager' => [
                'display_name' => 'Manager',
                'description' => 'Outlet operations and authorized approvals.',
            ],
            'attendant' => [
                'display_name' => 'Sales Attendant',
                'description' => 'Daily sales and authorized POS operations.',
            ],
        ];

        $permissions = [
            'dashboard.view' => 'View Dashboard',

            'sales.view' => 'View Sales',
            'sales.create' => 'Create Sales',
            'sales.refund' => 'Refund Sales',

            'inventory.view' => 'View Inventory',
            'inventory.receive' => 'Receive Inventory',
            'inventory.adjust' => 'Adjust Inventory',

            'cylinders.view' => 'View Cylinders',
            'cylinders.manage' => 'Manage Cylinders',
            'cashbook.view' => 'View Cashbook',

            'purchases.view' => 'View Purchases',
            'purchases.create' => 'Create Purchases',

            'expenses.view' => 'View Expenses',
            'expenses.create' => 'Record Expenses',
            'expenses.void' => 'Void Recorded Expenses',

            'customers.view' => 'View Customers',
            'customers.manage' => 'Manage Customers',

            'credit.approve' => 'Approve Credit Sales',

            'prices.manage' => 'Manage Selling Prices',

            'reports.view' => 'View Reports',
            'reports.financial' => 'View Financial Reports',

            'users.manage' => 'Manage Users',
            'settings.manage' => 'Manage Settings',
            'audit.view' => 'View Audit Logs',
        ];

        $managerPermissions = [
            'dashboard.view',
            'sales.view',
            'sales.create',
            'sales.refund',
            'inventory.view',
            'inventory.receive',
            'inventory.adjust',
            'cylinders.view',
            'cylinders.manage',
            'cashbook.view',
            'purchases.view',
            'purchases.create',
            'expenses.view',
            'expenses.create',
            'expenses.void',
            'customers.view',
            'customers.manage',
            'credit.approve',
            'reports.view',

        ];

        $attendantPermissions = [
            'dashboard.view',
            'sales.view',
            'sales.create',
            'inventory.view',
            'cylinders.view',
            'customers.view',
        ];

        DB::transaction(function () use (
            $roles,
            $permissions,
            $managerPermissions,
            $attendantPermissions
        ) {
            foreach ($roles as $name => $details) {
                Role::updateOrCreate(
                    ['name' => $name],
                    $details
                );
            }

            foreach ($permissions as $name => $displayName) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $displayName,
                        'module' => explode('.', $name)[0],
                    ]
                );
            }

            $permissionIds = Permission::pluck('id', 'name');

            $assignments = [
                'owner' => array_keys($permissions),
                'manager' => $managerPermissions,
                'attendant' => $attendantPermissions,
            ];

            foreach ($assignments as $roleName => $names) {
                $role = Role::where('name', $roleName)->firstOrFail();

                $ids = collect($names)
                    ->map(fn ($name) => $permissionIds[$name])
                    ->all();

                $role->permissions()->sync($ids);
            }
        });
    }
}
