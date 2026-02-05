<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $pbfNames = [
            'PT. Kimia Farma Trading & Distribution',
            'PT. Indofarma Global Medika',
            'PT. Rajawali Nusindo',
            'PT. Anugrah Argon Medika',
            'PT. Mensa Bina Sukses',
            'PT. Enseval Putera Megatrading',
            'PT. Dos Ni Roha',
            'PT. Pharos Indonesia',
            'PT. Dexa Medica',
            'PT. Sanbe Farma',
            'PT. Kalbe Farma',
            'PT. Tempo Scan Pacific',
            'PT. Combiphar',
            'PT. Soho Industri Pharmasi',
            'PT. Novell Pharmaceutical Laboratories',
            'PT. Bernofarm',
            'PT. Dankos Laboratories',
            'PT. Ferron Par Pharmaceutical',
            'PT. Interbat',
            'PT. Konimex',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($pbfNames),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
        ];
    }
}
