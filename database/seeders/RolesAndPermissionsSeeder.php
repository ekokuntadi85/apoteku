<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-dashboard',
            'access-dashboard',
            'access-reports',
            'access-products',
            'access-sales',
            'access-purchases',
            'manage-settings',
            'manage-users',
            'delete-purchase',
            'access-finance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign existing permissions
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign existing permissions
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $roleSuperAdmin->givePermissionTo(Permission::all());

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo([
            'access-dashboard',
            'access-reports',
            'access-products',
            'access-sales',
            'access-purchases',
            'manage-settings',
            'delete-purchase',
            'access-finance',
        ]);

        $roleKasir = Role::firstOrCreate(['name' => 'kasir']);
        $roleKasir->givePermissionTo([
            'access-products',
            'access-sales',
            'access-purchases',
            'manage-settings',
        ]);
    }
}
