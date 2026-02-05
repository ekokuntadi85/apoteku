<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Define purchase price first to calculate selling price from it
        $purchasePrice = fake()->randomFloat(2, 5000, 50000);
        $sellingPrice = $purchasePrice * fake()->randomFloat(2, 1.15, 1.3); // 15-30% markup

        return [
            // Ensure a product exists to link to.
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Pcs', 'Strip', 'Box', 'Botol']),
            'is_base_unit' => true,
            'conversion_factor' => 1, // Default to 1 for a base unit
            'purchase_price' => round($purchasePrice),
            'selling_price' => round($sellingPrice),
        ];
    }
}
