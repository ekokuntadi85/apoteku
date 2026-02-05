# Dokumentasi Fitur Import Data Obat dan Stok Initial

## Ringkasan Eksekutif

Sistem apoteku memiliki fitur untuk mengimpor data produk obat beserta stok awal (initial stock) melalui file Excel (.xlsx/.xls). Fitur ini dirancang untuk mempermudah input data massal saat pertama kali setup sistem atau saat ada penambahan produk dalam jumlah banyak.

---

## 1. Komponen Utama

### 1.1 File Import Class
**Lokasi:** `/app/Imports/ProductsImport.php`

Class ini mengimplementasikan:
- `ToModel` - Mengkonversi setiap baris Excel menjadi model
- `WithHeadingRow` - Menggunakan baris pertama sebagai header
- `ShouldQueue` - Menjalankan import di background queue
- `WithChunkReading` - Membaca data dalam chunk (200 baris per chunk)

### 1.2 Livewire Components
Ada 2 komponen Livewire untuk import:

1. **ProductImportManager** (`/app/Livewire/ProductImportManager.php`)
   - Import synchronous (langsung)
   - Cocok untuk file kecil
   
2. **SlowProductImportManager** (`/app/Livewire/SlowProductImportManager.php`)
   - Import dengan queue (background)
   - Cocok untuk file besar
   - Menggunakan Laravel Queue

### 1.3 Views
- `/resources/views/livewire/product-import-manager.blade.php`
- `/resources/views/livewire/slow-product-import-manager.blade.php`

---

## 2. Struktur Data Excel

### 2.1 Kolom yang Diperlukan (Required)
| Kolom | Deskripsi | Contoh | Wajib? |
|-------|-----------|--------|--------|
| `kategori` | Nama kategori produk | Antibiotik | Ya |
| `satuan` | Satuan dasar produk | Strip | Ya |
| `nama_obat` | Nama produk obat | Amoxicillin 500mg | Ya |
| `sku` | Kode unik produk | OBT-001 | Ya |
| `purchase_price` | Harga beli per satuan dasar | 5000 | Ya |
| `selling_price` | Harga jual per satuan dasar | 7000 | Tidak (bisa 0) |
| `stock` | Jumlah stok awal | 100 | Tidak (default 0) |
| `expiration_date` | Tanggal kadaluarsa | 2025-12-31 | Tidak |
| `supplier` | Nama supplier | PT Kimia Farma | Tidak |
| `nomor_batch` | Nomor batch | BATCH-001 | Tidak (default '-') |

### 2.2 Format Tanggal
- Format Excel date (numeric)
- Format string: `YYYY-MM-DD`
- Jika kosong: tidak ada expiration date

---

## 3. Proses Import - Alur Detail

### 3.1 Validasi Data
```php
// Baris akan di-skip jika data essential kosong:
if (empty($kategoriName) || empty($satuanName) || 
    empty($namaObat) || empty($sku) || empty($purchasePrice)) {
    return null; // Skip row
}
```

### 3.2 Tahapan Import per Baris

#### Step 1: Buat/Ambil Category
```php
$category = Category::firstOrCreate(['name' => $kategoriName]);
```
- Jika kategori sudah ada, gunakan yang ada
- Jika belum ada, buat baru

#### Step 2: Buat/Ambil Unit (Satuan)
```php
$unit = Unit::firstOrCreate(['name' => $satuanName]);
```
- Jika satuan sudah ada, gunakan yang ada
- Jika belum ada, buat baru

#### Step 3: Buat/Ambil Supplier
```php
$actualSupplierName = !empty($supplierName) ? $supplierName : 'Initial Stock';
$supplier = Supplier::firstOrCreate(['name' => $actualSupplierName]);
```
- Jika supplier kosong, gunakan nama **"Initial Stock"**
- Supplier "Initial Stock" akan otomatis dibuat jika belum ada

#### Step 4: Buat/Update Product
```php
$product = Product::updateOrCreate(
    ['sku' => $sku],
    [
        'name' => $namaObat,
        'category_id' => $category->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);
```
- Menggunakan `updateOrCreate` dengan key `sku`
- Jika SKU sudah ada, data produk akan di-update
- Jika SKU baru, produk baru akan dibuat

**PENTING:** Product tidak lagi menyimpan `unit_id` dan `selling_price` langsung. Data ini dipindahkan ke `ProductUnit`.

#### Step 5: Buat ProductUnit (Base Unit)
```php
$productUnit = ProductUnit::firstOrCreate(
    ['product_id' => $product->id, 'is_base_unit' => true],
    [
        'name' => $unit->name,
        'conversion_factor' => 1,
        'selling_price' => $sellingPrice,
        'purchase_price' => $purchasePrice,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);
```
- Membuat satuan dasar (base unit) untuk produk
- `conversion_factor` = 1 (karena ini satuan dasar)
- Menyimpan `selling_price` dan `purchase_price` di level ProductUnit

#### Step 6: Parse Expiration Date
```php
if (!empty($expirationDateRaw)) {
    if (is_numeric($expirationDateRaw)) {
        // Excel date format
        $expirationDate = Carbon::createFromTimestamp(
            Date::excelToTimestamp($expirationDateRaw)
        );
    } else {
        // String format
        $expirationDate = Carbon::parse($expirationDateRaw);
    }
}
```

#### Step 7: Buat ProductBatch (Stok Initial)
```php
ProductBatch::create([
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
    'supplier_id' => $supplier->id, // TIDAK ADA DI SCHEMA!
]);
```

**⚠️ MASALAH TERDETEKSI:** Kode mencoba menyimpan `supplier_id` ke `product_batches`, tapi kolom ini **TIDAK ADA** di database!

---

## 4. Relasi Tabel

### 4.1 Struktur Tabel `product_batches`

Berdasarkan schema database aktual:

| Kolom | Tipe | Nullable | Relasi |
|-------|------|----------|--------|
| `id` | bigint(20) unsigned | NO | Primary Key |
| `batch_number` | varchar(255) | NO | - |
| `purchase_price` | decimal(10,2) | NO | - |
| `stock` | int(11) | NO | - |
| `expiration_date` | date | YES | - |
| `product_id` | bigint(20) unsigned | NO | FK → products.id |
| `created_at` | timestamp | YES | - |
| `updated_at` | timestamp | YES | - |
| `purchase_id` | bigint(20) unsigned | YES | FK → purchases.id |
| `product_unit_id` | bigint(20) unsigned | YES | FK → product_units.id |

**CATATAN:** Kolom `supplier_id` **TIDAK ADA** di tabel ini!

### 4.2 Relasi dengan Tabel Lain

#### A. Product (products)
```
product_batches.product_id → products.id
```
- One-to-Many: Satu produk bisa punya banyak batch
- Cascade delete: Jika produk dihapus, semua batch-nya ikut terhapus

#### B. ProductUnit (product_units)
```
product_batches.product_unit_id → product_units.id
```
- Many-to-One: Banyak batch bisa menggunakan satuan yang sama
- Set null on delete: Jika ProductUnit dihapus, `product_unit_id` di batch jadi NULL

#### C. Purchase (purchases)
```
product_batches.purchase_id → purchases.id
```
- Many-to-One: Banyak batch bisa berasal dari satu pembelian
- Cascade delete: Jika purchase dihapus, batch-nya ikut terhapus
- **Nullable:** Untuk stok initial, `purchase_id` bisa NULL

#### D. Supplier (suppliers)
**TIDAK ADA RELASI LANGSUNG!**

Relasi supplier ke batch adalah **INDIRECT** melalui `purchases`:
```
product_batches.purchase_id → purchases.id
purchases.supplier_id → suppliers.id
```

Untuk stok initial (tanpa purchase), supplier **TIDAK TERSIMPAN** di batch!

### 4.3 Relasi dengan StockMovement

```
stock_movements.product_batch_id → product_batches.id
```

Setiap kali `ProductBatch` dibuat, `ProductBatchObserver` otomatis membuat record di `stock_movements`:

```php
// ProductBatchObserver.php - created event
StockMovement::create([
    'product_batch_id' => $productBatch->id,
    'type' => 'PB', // Purchase Batch
    'quantity' => $productBatch->stock,
    'remarks' => 'Pembelian awal',
]);
```

**Tipe StockMovement:**
- `PB` - Purchase Batch (pembelian/stok awal)
- `PJ` - Penjualan
- `OP` - Stock Opname
- `ADJ` - Adjustment
- `DEL` - Deletion
- `RES` - Restoration

---

## 5. Bug dan Masalah yang Terdeteksi

### 🐛 Bug #1: Supplier ID di ProductBatch

**Lokasi:** `/app/Imports/ProductsImport.php` line 122

**Masalah:**
```php
ProductBatch::create([
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
    'supplier_id' => $supplier->id, // ❌ KOLOM INI TIDAK ADA!
]);
```

**Dampak:**
- Import akan **GAGAL** dengan error SQL
- Error: "Unknown column 'supplier_id' in 'field list'"

**Solusi:**
Hapus baris `'supplier_id' => $supplier->id,` dari kode.

Untuk stok initial tanpa purchase, supplier tidak perlu disimpan di batch. Jika diperlukan tracking supplier untuk stok initial, ada 2 opsi:

**Opsi A:** Buat Purchase record untuk stok initial
```php
$purchase = Purchase::create([
    'invoice_number' => 'INIT-' . $product->sku . '-' . time(),
    'purchase_date' => now(),
    'total_price' => $purchasePrice * $stock,
    'supplier_id' => $supplier->id,
    'payment_status' => 'paid',
]);

ProductBatch::create([
    'purchase_id' => $purchase->id,
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
]);
```

**Opsi B:** Biarkan tanpa supplier (purchase_id = NULL)
```php
ProductBatch::create([
    'purchase_id' => null, // Stok initial tanpa purchase
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
]);
```

### 🐛 Bug #2: Model ProductBatch Tidak Mendefinisikan Supplier Relation

**Lokasi:** `/app/Models/ProductBatch.php`

**Masalah:**
Model `Supplier` memiliki relasi `productBatches()`:
```php
// Supplier.php
public function productBatches()
{
    return $this->hasMany(ProductBatch::class);
}
```

Tapi ini **TIDAK VALID** karena `product_batches` tidak punya kolom `supplier_id`.

**Solusi:**
Hapus atau ubah relasi di `Supplier.php`:
```php
// Relasi indirect melalui purchases
public function productBatches()
{
    return $this->hasManyThrough(
        ProductBatch::class,
        Purchase::class,
        'supplier_id', // FK di purchases
        'purchase_id', // FK di product_batches
        'id',          // PK di suppliers
        'id'           // PK di purchases
    );
}
```

---

## 6. Integrasi dengan Sistem Lain

### 6.1 Stock Movement Tracking

Setiap ProductBatch yang dibuat (termasuk dari import) akan otomatis:

1. **Trigger ProductBatchObserver::created()**
2. **Membuat StockMovement** dengan:
   - `type` = 'PB' (Purchase Batch)
   - `quantity` = stock dari batch
   - `remarks` = 'Pembelian awal'

Ini memastikan **audit trail** lengkap untuk setiap pergerakan stok.

### 6.2 Kartu Stok (Stock Card)

Stock card menggunakan data dari `stock_movements`:

```php
// DocumentController.php
$initialBalance = StockMovement::whereHas('productBatch', function($query) use ($productId) {
    $query->where('product_id', $productId);
})
->where('type', 'PB')
->sum('quantity');
```

Stok initial dari import akan muncul di kartu stok sebagai "Pembelian awal".

### 6.3 Financial Journal (Jurnal Keuangan)

**PENTING:** Saat ini, import stok initial **TIDAK** membuat jurnal keuangan otomatis.

Jika menggunakan Opsi A (membuat Purchase), maka perlu ditambahkan:

```php
// Setelah membuat Purchase
$journalEntry = JournalEntry::create([
    'transaction_date' => now(),
    'reference_number' => 'INIT-' . $purchase->invoice_number,
    'description' => 'Stok Initial - ' . $product->name,
    'total_amount' => $purchase->total_price,
]);

// Debit: Inventory
JournalDetail::create([
    'journal_entry_id' => $journalEntry->id,
    'account_id' => Account::where('code', '1140')->first()->id, // Inventory
    'debit' => $purchase->total_price,
    'credit' => 0,
]);

// Credit: Equity (Modal)
JournalDetail::create([
    'journal_entry_id' => $journalEntry->id,
    'account_id' => Account::where('code', '3100')->first()->id, // Owner's Equity
    'debit' => 0,
    'credit' => $purchase->total_price,
]);
```

---

## 7. Cara Penggunaan

### 7.1 Persiapan File Excel

1. Buat file Excel dengan kolom sesuai struktur di bagian 2.1
2. Isi data produk
3. Simpan sebagai `.xlsx` atau `.xls`

### 7.2 Import via UI

1. Akses halaman import (biasanya di menu Produk → Import)
2. Pilih file Excel
3. Klik tombol Import
4. Tunggu proses selesai
5. Cek hasil import

### 7.3 Import via Command Line (untuk file besar)

```bash
docker compose exec app php artisan queue:work
```

Kemudian upload file via UI dengan SlowProductImportManager.

---

## 8. Rekomendasi Perbaikan

### 8.1 Prioritas Tinggi (Critical)

1. **Fix Bug #1** - Hapus `supplier_id` dari ProductBatch::create()
2. **Fix Bug #2** - Perbaiki relasi Supplier → ProductBatch
3. **Tambahkan Error Handling** - Tangani error SQL dengan lebih baik

### 8.2 Prioritas Sedang (Important)

4. **Buat Purchase untuk Stok Initial** - Agar supplier tracking konsisten
5. **Tambahkan Journal Entry** - Untuk integrasi dengan sistem keuangan
6. **Validasi Duplikasi** - Cek duplikasi SKU sebelum import
7. **Progress Indicator** - Tampilkan progress import untuk user

### 8.3 Prioritas Rendah (Nice to Have)

8. **Template Excel** - Sediakan template download
9. **Preview Import** - Tampilkan preview sebelum import final
10. **Rollback Feature** - Bisa rollback import jika ada kesalahan

---

## 9. Testing

### 9.1 Test Cases

1. **Import produk baru** - SKU belum ada
2. **Import produk existing** - SKU sudah ada (update)
3. **Import tanpa supplier** - Harus buat "Initial Stock"
4. **Import dengan expiration date kosong**
5. **Import dengan stock = 0**
6. **Import file besar** (>1000 baris)

### 9.2 Validasi Hasil

Setelah import, cek:
- [ ] Produk tersimpan di tabel `products`
- [ ] ProductUnit base unit terbuat
- [ ] ProductBatch terbuat dengan stok yang benar
- [ ] StockMovement terbuat dengan type 'PB'
- [ ] Total stock produk sesuai dengan yang diimport
- [ ] Kategori dan Unit terbuat jika belum ada
- [ ] Supplier "Initial Stock" terbuat jika supplier kosong

---

## 10. Diagram Relasi

```
┌─────────────┐
│  suppliers  │
└──────┬──────┘
       │
       │ (via purchases)
       ▼
┌─────────────┐       ┌──────────────┐
│  purchases  │◄──────│product_batches│
└─────────────┘       └──────┬───────┘
                             │
                    ┌────────┼────────┐
                    ▼        ▼        ▼
            ┌───────────┐ ┌──────────────┐ ┌────────────────┐
            │ products  │ │product_units │ │stock_movements │
            └───────────┘ └──────────────┘ └────────────────┘
                  │
                  ▼
            ┌───────────┐
            │categories │
            └───────────┘
```

---

## 11. Kesimpulan

Fitur import data obat dan stok initial adalah fitur penting untuk setup awal sistem. Namun, ada beberapa bug kritis yang perlu diperbaiki:

1. **Kolom `supplier_id` tidak ada di `product_batches`** - Harus dihapus dari kode import
2. **Relasi Supplier → ProductBatch tidak valid** - Harus diperbaiki atau dihapus
3. **Tidak ada integrasi dengan jurnal keuangan** - Perlu ditambahkan jika diperlukan

Setelah bug-bug ini diperbaiki, fitur import akan berfungsi dengan baik dan dapat digunakan untuk input data massal produk dan stok awal.

---

**Dibuat:** 2025-12-16  
**Versi:** 1.0  
**Status:** Draft - Menunggu Perbaikan Bug
