<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // --- Satuan Dosis & Eceran ---
            ['name' => 'Tablet', 'short_name' => 'tab'],
            ['name' => 'Kaplet', 'short_name' => 'kap'],
            ['name' => 'Kapsul', 'short_name' => 'kaps'],
            ['name' => 'Pcs', 'short_name' => 'pcs'],
            ['name' => 'Sachet', 'short_name' => 'sachet'],
            ['name' => 'Suppositoria', 'short_name' => 'supp'],
            ['name' => 'Ampul', 'short_name' => 'amp'],
            ['name' => 'Vial', 'short_name' => 'vial'],
            ['name' => 'Flacon', 'short_name' => 'fl'],

            // --- Satuan Kemasan Cair & Semi-Padat ---
            ['name' => 'Botol', 'short_name' => 'btl'],
            ['name' => 'Tube', 'short_name' => 'tube'],
            ['name' => 'Pot', 'short_name' => 'pot'],
            ['name' => 'Kaleng', 'short_name' => 'klg'],
            ['name' => 'Sirup', 'short_name' => 'sir'],
            ['name' => 'Suspensi', 'short_name' => 'susp'],
            ['name' => 'Injeksi', 'short_name' => 'inj'],
            ['name' => 'Tetes', 'short_name' => 'tetes'],
            ['name' => 'Salep', 'short_name' => 'salep'],
            ['name' => 'Krim', 'short_name' => 'krim'],
            ['name' => 'Gel', 'short_name' => 'gel'],

            // --- Satuan Kemasan Distribusi (Besar) ---
            ['name' => 'Strip', 'short_name' => 'strip'],
            ['name' => 'Blister', 'short_name' => 'blister'],
            ['name' => 'Box', 'short_name' => 'box'],
            ['name' => 'Dus', 'short_name' => 'dus'],
            ['name' => 'Karton', 'short_name' => 'karton'],
        ];

        foreach ($units as $unitData) {
            Unit::firstOrCreate(['name' => $unitData['name']], $unitData);
        }
    }
}
