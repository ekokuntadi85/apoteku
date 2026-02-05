<?php

namespace Database\Factories;

use App\Models\ProductBatch;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductBatchFactory extends Factory
{
    protected $model = ProductBatch::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'purchase_id' => Purchase::factory(), // This will be overridden by PurchaseSeeder
            'batch_number' => $this->faker->unique()->bothify('BATCH-########'),
            'purchase_price' => $this->faker->randomFloat(2, 1000, 50000),
            'stock' => $this->faker->numberBetween(1, 200),
            'expiration_date' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
        ];
    }
}