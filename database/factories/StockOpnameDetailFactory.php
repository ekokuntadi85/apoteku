<?php

namespace Database\Factories;

use App\Models\StockOpnameDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockOpnameDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockOpnameDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stock_opname_id' => \App\Models\StockOpname::factory(),
            'product_batch_id' => \App\Models\ProductBatch::factory(),
            'system_stock' => $this->faker->numberBetween(1, 100),
                        'physical_stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
