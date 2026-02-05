<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\ProductUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, ShouldQueue, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Processing row:', $row);

        $kategoriName = Arr::get($row, 'kategori');
        $satuanName = Arr::get($row, 'satuan');
        $namaObat = Arr::get($row, 'nama_obat');
        $sku = Arr::get($row, 'sku');
        $sellingPrice = Arr::get($row, 'selling_price');
        $purchasePrice = Arr::get($row, 'purchase_price');
        $stock = Arr::get($row, 'stock') ?? 0;
        $expirationDateRaw = Arr::get($row, 'expiration_date');
        $supplierName = Arr::get($row, 'supplier');
        $batchNumber = Arr::get($row, 'nomor_batch', '-');

        Log::info('Extracted data:', [
            'kategoriName' => $kategoriName,
            'satuanName' => $satuanName,
            'namaObat' => $namaObat,
            'sku' => $sku,
            'sellingPrice' => $sellingPrice,
            'purchasePrice' => $purchasePrice,
            'stock' => $stock,
            'expirationDateRaw' => $expirationDateRaw,
            'supplierName' => $supplierName,
            'batchNumber' => $batchNumber,
        ]);

        if (empty($kategoriName) || empty($satuanName) || empty($namaObat) || empty($sku) || empty($purchasePrice)) { // sellingPrice dan stock bisa 0 atau null
            Log::warning('Skipping row due to missing essential data.', $row);
            return null;
        }

        try {
            $category = Category::firstOrCreate(['name' => $kategoriName]);
            Log::info('Category processed:', ['id' => $category->id, 'name' => $category->name]);

            $unit = Unit::firstOrCreate(['name' => $satuanName]);
            Log::info('Unit processed:', ['id' => $unit->id, 'name' => $unit->name]);

            $actualSupplierName = !empty($supplierName) ? $supplierName : 'Initial Stock';
            $supplier = Supplier::firstOrCreate(['name' => $actualSupplierName]);
            Log::info('Supplier processed:', ['id' => $supplier->id, 'name' => $supplier->name]);

            // Product tidak lagi memiliki unit_id dan selling_price
            $product = Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'name' => $namaObat,
                    'category_id' => $category->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            Log::info('Product processed:', ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku]);

            // Buat ProductUnit dasar untuk produk ini
            $productUnit = ProductUnit::firstOrCreate(
                ['product_id' => $product->id, 'is_base_unit' => true],
                [
                    'name' => $unit->name,
                    'conversion_factor' => 1,
                    'selling_price' => $sellingPrice, // selling_price sekarang di ProductUnit
                    'purchase_price' => $purchasePrice,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            Log::info('Base ProductUnit created/updated for product:', ['product_id' => $product->id, 'unit_name' => $unit->name, 'product_unit_id' => $productUnit->id]);

            $expirationDate = null;
            if (!empty($expirationDateRaw)) {
                if (is_numeric($expirationDateRaw)) {
                    try {
                        $expirationDate = Carbon::createFromTimestamp(
                            Date::excelToTimestamp($expirationDateRaw)
                        );
                    } catch (\Exception $e) {
                        Log::error('Error parsing Excel date (numeric):', ['value' => $expirationDateRaw, 'error' => $e->getMessage()]);
                        $expirationDate = Carbon::parse($expirationDateRaw); // Fallback
                    }
                } else {
                    $expirationDate = Carbon::parse($expirationDateRaw);
                }
            }
            Log::info('Expiration Date parsed:', ['raw' => $expirationDateRaw, 'parsed' => $expirationDate]);

            // ProductBatch sekarang terkait dengan ProductUnit
            ProductBatch::create([
                'product_id' => $product->id,
                'product_unit_id' => $productUnit->id, // Gunakan product_unit_id dari ProductUnit dasar
                'batch_number' => $batchNumber,
                'purchase_price' => $purchasePrice,
                'stock' => $stock,
                'expiration_date' => $expirationDate,
                'supplier_id' => $supplier->id,
            ]);
            Log::info('ProductBatch created for product:', ['product_id' => $product->id, 'batch_number' => $batchNumber, 'product_unit_id' => $productUnit->id]);

            return $product;
        } catch (\Exception $e) {
            Log::error('Error during import of row:', ['row' => $row, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
