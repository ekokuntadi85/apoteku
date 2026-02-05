<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $totalPrice = $this->faker->randomFloat(2, 10000, 1000000);

        return [
            'type' => $this->faker->randomElement(['pos', 'invoice']),
            'payment_status' => $this->faker->randomElement(['paid', 'unpaid']),
            'total_price' => $totalPrice,
            'amount_paid' => $totalPrice,
            'change' => 0,
            'invoice_number' => 'INV-' . $this->faker->unique()->randomNumber(8),
            'due_date' => $this->faker->optional(0.8)->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
            'user_id' => User::factory(),
            'customer_id' => Customer::factory(),
        ];
    }
}
