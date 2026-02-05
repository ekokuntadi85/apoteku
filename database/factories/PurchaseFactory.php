<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $purchaseDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $totalPrice = $this->faker->randomFloat(2, 100000, 5000000);

        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->randomNumber(8),
            'purchase_date' => $purchaseDate->format('Y-m-d'),
            'due_date' => $this->faker->optional(0.8)->dateTimeBetween($purchaseDate, '+3 months')?->format('Y-m-d'),
            'total_price' => $totalPrice,
            'supplier_id' => Supplier::factory(),
        ];
    }
}
