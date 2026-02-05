<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call other seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            //CategorySeeder::class,
            //UnitSeeder::class,
            //SupplierSeeder::class,
            //ProductSeeder::class,
            //ProductUnitSeeder::class,
            //PurchaseSeeder::class,
        ]);

        // Create users with different roles
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin User', 'password' => bcrypt('password')]
        );
        $superAdmin->assignRole('super-admin');
        
    }
}