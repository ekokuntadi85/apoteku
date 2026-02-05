<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure categories and units are seeded first
        $this->call(CategorySeeder::class);
        $this->call(UnitSeeder::class);

        Product::factory()->count(2)->create()->each(function ($product) {
            // 1. Create the base unit for every product
            $purchasePrice = fake()->randomFloat(2, 5000, 50000);
            $sellingPrice = $purchasePrice * fake()->randomFloat(2, 1.15, 1.30); // 15-30% markup

            $baseUnit = ProductUnit::factory()->create([
                'product_id' => $product->id,
                'name' => 'Pcs',
                'is_base_unit' => true,
                'conversion_factor' => 1,
                'purchase_price' => round($purchasePrice),
                'selling_price' => round($sellingPrice),
            ]);

            // 2. Randomly add a second, larger unit for some products
            if (rand(0, 1)) {
                $conversion = fake()->randomElement([10, 12, 20, 24]);
                $biggerUnitName = fake()->randomElement(['Strip', 'Box', 'Lusin']);

                ProductUnit::factory()->create([
                    'product_id' => $product->id,
                    'name' => $biggerUnitName,
                    'is_base_unit' => false,
                    'conversion_factor' => $conversion,
                    // Prices are multiples of the base unit price
                    'purchase_price' => round($baseUnit->purchase_price * $conversion),
                    'selling_price' => round($baseUnit->selling_price * $conversion * 0.95), // slight discount for bulk
                ]);
            }
        });
    }
}