<?php

namespace Database\Factories;

use App\Models\StockOpname;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockOpnameFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockOpname::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'notes' => $this->faker->sentence(),
            'opname_date' => $this->faker->date(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
