<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductUnit;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products that don't have any product units yet
        $products = Product::doesntHave('productUnits')->get();

        foreach ($products as $product) {
            // Create a base unit for each product
            ProductUnit::create([
                'product_id' => $product->id,
                'name' => 'Pcs', // Default base unit name, adjust as needed
                'is_base_unit' => true,
                'conversion_factor' => 1,
                'selling_price' => $product->selling_price ?? 0, // Use existing selling_price or 0
                'purchase_price' => 0, // Default purchase price, will be updated later
            ]);
        }
    }
}