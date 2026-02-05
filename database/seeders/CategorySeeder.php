<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obat Modern
        Category::firstOrCreate(['name' => 'Obat Bebas'], ['description' => 'Obat yang dapat dibeli tanpa resep dokter, ditandai logo lingkaran hijau.']);
        Category::firstOrCreate(['name' => 'Obat Bebas Terbatas'], ['description' => 'Obat keras yang dapat dibeli tanpa resep dokter dalam jumlah tertentu, disertai tanda peringatan. Ditandai logo lingkaran biru.']);
        Category::firstOrCreate(['name' => 'Obat Keras'], ['description' => 'Obat yang hanya boleh diperoleh dengan resep dokter. Ditandai logo lingkaran merah dengan huruf \'K\'.']);
        Category::firstOrCreate(['name' => 'Narkotika'], ['description' => 'Obat yang menyebabkan penurunan atau perubahan kesadaran dan menimbulkan ketergantungan. Wajib resep dokter dan diawasi ketat. Ditandai logo palang medali merah.']);
        Category::firstOrCreate(['name' => 'Psikotropika'], ['description' => 'Obat yang memengaruhi aktivitas mental dan perilaku, penggunaannya wajib dengan resep dokter dan diawasi.']);
        Category::firstOrCreate(['name' => 'Obat Wajib Apotek (OWA)'], ['description' => 'Obat keras yang dapat diserahkan oleh apoteker kepada pasien di apotek tanpa resep dokter dengan batasan tertentu.']);
        // Obat Tradisional / Herbal
        Category::firstOrCreate(['name' => 'Jamu'], ['description' => 'Obat bahan alam yang khasiat dan keamanannya dibuktikan berdasarkan pengalaman empiris (turun-temurun). Ditandai logo pohon.']);
        Category::firstOrCreate(['name' => 'Obat Herbal Terstandar (OHT)'], ['description' => 'Obat bahan alam yang khasiatnya telah dibuktikan secara ilmiah melalui uji pra-klinis (pada hewan). Ditandai logo tiga bintang.']);
        Category::firstOrCreate(['name' => 'Fitofarmaka'], ['description' => 'Obat bahan alam yang khasiatnya telah dibuktikan melalui uji pra-klinis dan uji klinis (pada manusia). Derajat pembuktian tertinggi. Ditandai logo kristal salju.']);
        // Add more categories if needed, up to 2 as requested
        // Category::factory()->count(max(0, 2 - Category::count()))->create();
    }
}
