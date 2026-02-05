<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Purchase::factory()->count(10)->create()->each(function ($purchase) {
            // Attach 1 to 5 product batches to each purchase
            $products = Product::inRandomOrder()->limit(rand(1, 5))->get();
            foreach ($products as $product) {
                // Ensure the product has a base unit
                $baseUnit = $product->baseUnit;
                if (!$baseUnit) {
                    // Fallback if for some reason base unit is not found (should not happen with ProductUnitSeeder)
                    $generatedPurchasePrice = fake()->randomFloat(2, 5000, 50000);
                    $baseUnit = \App\Models\ProductUnit::factory()->create([
                        'product_id' => $product->id,
                        'name' => 'Pcs',
                        'is_base_unit' => true,
                        'conversion_factor' => 1,
                        'selling_price' => $generatedPurchasePrice * 1.20,
                        'purchase_price' => $generatedPurchasePrice,
                    ]);
                }

                ProductBatch::factory()->create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_unit_id' => $baseUnit->id, // Assign the base unit
                    'purchase_price' => fake()->randomFloat(2, 5000, 50000), // Random purchase price
                    'stock' => rand(10, 100),
                    'expiration_date' => now()->addDays(rand(30, 365)),
                ]);
            }

            // Recalculate and update the total_price for the purchase
            $totalPrice = $purchase->productBatches()->sum(DB::raw('purchase_price * stock'));
            $purchase->update(['total_price' => $totalPrice]);
        });
    }
}