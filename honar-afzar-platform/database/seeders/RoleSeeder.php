<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each product
        $products = ['andookhtiar', 'fishk', 'diyara', 'fan-hesab', 'nameh-yar'];
        
        $actions = ['view', 'create', 'update', 'delete', 'export', 'import'];
        
        foreach ($products as $product) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$product}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Core permissions
        $corePermissions = [
            'organizations.manage',
            'organizations.view',
            'users.manage',
            'users.view',
            'roles.manage',
            'settings.manage',
            'reports.view',
            'reports.export',
            'notifications.manage',
            'audit.view',
        ];

        foreach ($corePermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Create roles
        $roles = [
            'super-admin' => Permission::all()->pluck('name')->toArray(),
            'org-admin' => [
                'organizations.view',
                'users.manage',
                'users.view',
                'roles.manage',
                'settings.manage',
                'reports.view',
                'reports.export',
                'notifications.manage',
                'audit.view',
                'andookhtiar.view',
                'andookhtiar.create',
                'andookhtiar.update',
                'andookhtiar.delete',
                'andookhtiar.export',
                'fishk.view',
                'fishk.create',
                'fishk.update',
                'fishk.delete',
                'fishk.export',
                'diyara.view',
                'diyara.create',
                'diyara.update',
                'diyara.delete',
                'diyara.export',
                'fan-hesab.view',
                'fan-hesab.create',
                'fan-hesab.update',
                'fan-hesab.delete',
                'fan-hesab.export',
                'nameh-yar.view',
                'nameh-yar.create',
                'nameh-yar.update',
                'nameh-yar.delete',
                'nameh-yar.export',
            ],
            'manager' => [
                'users.view',
                'reports.view',
                'reports.export',
                'andookhtiar.view',
                'andookhtiar.create',
                'andookhtiar.update',
                'fishk.view',
                'fishk.create',
                'fishk.update',
                'diyara.view',
                'diyara.create',
                'diyara.update',
                'fan-hesab.view',
                'fan-hesab.create',
                'fan-hesab.update',
                'nameh-yar.view',
                'nameh-yar.create',
                'nameh-yar.update',
            ],
            'user' => [
                'andookhtiar.view',
                'fishk.view',
                'diyara.view',
                'fan-hesab.view',
                'nameh-yar.view',
                'nameh-yar.create',
            ],
            'viewer' => [
                'andookhtiar.view',
                'fishk.view',
                'diyara.view',
                'fan-hesab.view',
                'nameh-yar.view',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            
            $role->syncPermissions($permissions);
        }
    }
}
