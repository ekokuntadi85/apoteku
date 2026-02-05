<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new permission
        $permissionName = 'access-finance';
        
        // Ensure Permission model exists and package is installed
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            
            // Assign to Super Admin
            $roleSuperAdmin = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
            if ($roleSuperAdmin) {
                $roleSuperAdmin->givePermissionTo($permission);
            }

            // Assign to Admin (Optional, depending on business logic)
            $roleAdmin = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            if ($roleAdmin) {
                $roleAdmin->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
