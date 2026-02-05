<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $units = ['Tablet', 'Kapsul', 'Botol', 'Strip', 'Tube'];
        $shortNames = ['tab', 'kps', 'btl', 'str', 'tbe'];
        $randomIndex = $this->faker->unique()->numberBetween(0, count($units) - 1);

        return [
            'name' => $units[$randomIndex],
            'short_name' => $shortNames[$randomIndex],
        ];
    }
}