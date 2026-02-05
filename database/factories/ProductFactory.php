<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $productNames = [
            'Paracetamol',
            'Ibuprofen',
            'Amoxicillin',
            'Omeprazole',
            'Loratadine',
            'Cetirizine',
            'Metformin',
            'Amlodipine',
            'Simvastatin',
            'Vitamin C',
            'Antasida',
            'Promethazine',
            'Dexamethasone',
            'Captopril',
            'Furosemide',
            'Ranitidine',
            'Domperidone',
            'Asam Mefenamat',
            'Dextromethorphan',
            'Chlorpheniramine Maleate',
        ];

        // Ensure categories and units exist before creating products
        $categoryIds = Category::pluck('id')->toArray();
        $unitIds = Unit::pluck('id')->toArray();

        // Fallback if no categories/units exist (should not happen if seeders run in order)
        if (empty($categoryIds)) {
            $categoryIds = [Category::factory()->create()->id];
        }
        if (empty($unitIds)) {
            $unitIds = [Unit::factory()->create()->id];
        }

        return [
            'name' => $this->faker->randomElement($productNames) . ' ' . $this->faker->randomElement(['500mg', '100ml', '250mg', 'Forte', 'Sirup']),
            'sku' => $this->faker->unique()->ean8(),
            'category_id' => $this->faker->randomElement($categoryIds),
        ];
    }
}