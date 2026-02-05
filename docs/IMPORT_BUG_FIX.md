# Fix untuk Bug Import Data Obat dan Stok Initial

## Bug #1: Supplier ID di ProductBatch

### File: `/app/Imports/ProductsImport.php`

**Baris 115-123 (SEBELUM):**
```php
ProductBatch::create([
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
    'supplier_id' => $supplier->id, // ❌ BUG: Kolom ini tidak ada!
]);
```

### Opsi Perbaikan A: Tanpa Purchase (Sederhana)

**Baris 115-122 (SESUDAH):**
```php
ProductBatch::create([
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
    // supplier_id dihapus karena kolom tidak ada
]);
```

**Kelebihan:**
- Sederhana dan cepat
- Tidak perlu perubahan besar

**Kekurangan:**
- Tidak ada tracking supplier untuk stok initial
- Tidak konsisten dengan pembelian normal

---

### Opsi Perbaikan B: Dengan Purchase (Direkomendasikan)

**Tambahkan setelah baris 68 (setelah supplier dibuat):**
```php
// Buat Purchase untuk stok initial
$purchase = Purchase::create([
    'invoice_number' => 'INIT-' . $sku . '-' . now()->timestamp,
    'purchase_date' => now(),
    'total_price' => $purchasePrice * $stock,
    'supplier_id' => $supplier->id,
    'payment_status' => 'paid',
    'due_date' => now(),
]);
```

**Ubah baris 115-123:**
```php
ProductBatch::create([
    'purchase_id' => $purchase->id, // ✅ Gunakan purchase_id
    'product_id' => $product->id,
    'product_unit_id' => $productUnit->id,
    'batch_number' => $batchNumber,
    'purchase_price' => $purchasePrice,
    'stock' => $stock,
    'expiration_date' => $expirationDate,
]);
```

**Kelebihan:**
- Konsisten dengan pembelian normal
- Tracking supplier lengkap
- Bisa integrasi dengan jurnal keuangan
- Data lebih terstruktur

**Kekurangan:**
- Lebih banyak insert ke database
- Sedikit lebih lambat

---

## Bug #2: Invalid Relation di Model Supplier

### File: `/app/Models/Supplier.php`

**Baris 18-21 (SEBELUM):**
```php
public function productBatches()
{
    return $this->hasMany(ProductBatch::class);
    // ❌ BUG: Tidak ada FK supplier_id di product_batches
}
```

### Opsi Perbaikan A: Hapus Relasi

**Baris 18-21 (SESUDAH):**
```php
// Relasi dihapus karena tidak ada FK supplier_id di product_batches
// Gunakan relasi melalui purchases jika diperlukan
```

---

### Opsi Perbaikan B: Gunakan hasManyThrough (Direkomendasikan)

**Baris 18-28 (SESUDAH):**
```php
/**
 * Get all product batches from this supplier through purchases
 */
public function productBatches()
{
    return $this->hasManyThrough(
        ProductBatch::class,  // Model tujuan
        Purchase::class,      // Model perantara
        'supplier_id',        // FK di purchases table
        'purchase_id',        // FK di product_batches table
        'id',                 // PK di suppliers table
        'id'                  // PK di purchases table
    );
}
```

**Kelebihan:**
- Relasi tetap bisa digunakan
- Konsisten dengan struktur database
- Bisa query: `$supplier->productBatches`

---

## Bug #3: Model ProductBatch Tidak Mendefinisikan Supplier Relation

### File: `/app/Models/ProductBatch.php`

**Tambahkan method baru setelah baris 30:**
```php
/**
 * Get the supplier through purchase
 */
public function supplier()
{
    return $this->hasOneThrough(
        Supplier::class,      // Model tujuan
        Purchase::class,      // Model perantara
        'id',                 // PK di purchases table
        'id',                 // PK di suppliers table
        'purchase_id',        // FK di product_batches table
        'supplier_id'         // FK di purchases table
    );
}
```

**Kegunaan:**
Bisa query: `$batch->supplier` untuk mendapatkan supplier dari batch tertentu.

---

## Integrasi dengan Jurnal Keuangan (Opsional)

Jika menggunakan Opsi B (dengan Purchase), tambahkan ini setelah membuat Purchase:

### File: `/app/Imports/ProductsImport.php`

**Tambahkan use statement di atas:**
```php
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Account;
```

**Tambahkan setelah Purchase::create():**
```php
// Buat Journal Entry untuk stok initial
try {
    $inventoryAccount = Account::where('code', '1140')->first(); // Inventory
    $equityAccount = Account::where('code', '3100')->first();    // Owner's Equity
    
    if ($inventoryAccount && $equityAccount) {
        $journalEntry = JournalEntry::create([
            'transaction_date' => now(),
            'reference_number' => 'INIT-' . $purchase->invoice_number,
            'description' => 'Stok Initial - ' . $namaObat,
            'total_amount' => $purchase->total_price,
        ]);

        // Debit: Inventory (Persediaan Barang Dagang)
        JournalDetail::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $inventoryAccount->id,
            'debit' => $purchase->total_price,
            'credit' => 0,
        ]);

        // Credit: Owner's Equity (Modal Pemilik)
        JournalDetail::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $equityAccount->id,
            'debit' => 0,
            'credit' => $purchase->total_price,
        ]);
        
        Log::info('Journal entry created for initial stock', [
            'journal_entry_id' => $journalEntry->id,
            'product' => $namaObat,
            'amount' => $purchase->total_price
        ]);
    }
} catch (\Exception $e) {
    // Log error tapi jangan stop import
    Log::error('Failed to create journal entry for initial stock', [
        'product' => $namaObat,
        'error' => $e->getMessage()
    ]);
}
```

---

## Testing

### Test Case 1: Import Produk Baru
```php
// Data Excel:
// kategori | satuan | nama_obat | sku | purchase_price | selling_price | stock | supplier
// Antibiotik | Strip | Amoxicillin 500mg | OBT-001 | 5000 | 7000 | 100 | PT Kimia Farma

// Expected Result:
// - Product created with SKU OBT-001
// - ProductUnit created (base unit: Strip)
// - Purchase created with supplier PT Kimia Farma
// - ProductBatch created with stock 100
// - StockMovement created with type PB, quantity 100
// - JournalEntry created (if enabled)
```

### Test Case 2: Import Tanpa Supplier
```php
// Data Excel:
// kategori | satuan | nama_obat | sku | purchase_price | selling_price | stock | supplier
// Antibiotik | Strip | Amoxicillin 500mg | OBT-002 | 5000 | 7000 | 100 | (kosong)

// Expected Result:
// - Supplier "Initial Stock" created/used
// - Purchase created with supplier "Initial Stock"
// - Rest same as Test Case 1
```

### Test Case 3: Import SKU Existing
```php
// Data Excel (SKU OBT-001 already exists):
// kategori | satuan | nama_obat | sku | purchase_price | selling_price | stock | supplier
// Antibiotik | Strip | Amoxicillin 500mg NEW | OBT-001 | 6000 | 8000 | 50 | PT Kimia Farma

// Expected Result:
// - Product updated (name changed to "Amoxicillin 500mg NEW")
// - NEW ProductBatch created with stock 50
// - Total stock = old stock + 50
```

---

## Checklist Implementasi

### Step 1: Fix Bug #1 (CRITICAL)
- [ ] Backup file `/app/Imports/ProductsImport.php`
- [ ] Pilih Opsi A atau B
- [ ] Implementasi fix
- [ ] Test dengan sample data

### Step 2: Fix Bug #2 (IMPORTANT)
- [ ] Backup file `/app/Models/Supplier.php`
- [ ] Implementasi hasManyThrough
- [ ] Test query: `$supplier->productBatches`

### Step 3: Add Supplier Relation di ProductBatch (OPTIONAL)
- [ ] Backup file `/app/Models/ProductBatch.php`
- [ ] Tambahkan method supplier()
- [ ] Test query: `$batch->supplier`

### Step 4: Integrasi Jurnal Keuangan (OPTIONAL)
- [ ] Tambahkan use statements
- [ ] Implementasi journal entry creation
- [ ] Test dengan sample data
- [ ] Verifikasi di laporan keuangan

### Step 5: Testing Lengkap
- [ ] Test Case 1: Import produk baru
- [ ] Test Case 2: Import tanpa supplier
- [ ] Test Case 3: Import SKU existing
- [ ] Test Case 4: Import file besar (>100 rows)
- [ ] Test Case 5: Import dengan error (invalid data)

### Step 6: Dokumentasi
- [ ] Update user manual
- [ ] Buat template Excel
- [ ] Tambahkan contoh data
- [ ] Update API documentation (jika ada)

---

## Rollback Plan

Jika ada masalah setelah implementasi:

1. **Restore backup files:**
   ```bash
   cp /path/to/backup/ProductsImport.php app/Imports/
   cp /path/to/backup/Supplier.php app/Models/
   cp /path/to/backup/ProductBatch.php app/Models/
   ```

2. **Clear cache:**
   ```bash
   docker compose exec app php artisan cache:clear
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan route:clear
   ```

3. **Rollback database (jika perlu):**
   ```bash
   docker compose exec app php artisan migrate:rollback
   docker compose exec app php artisan migrate
   ```

---

## Estimasi Waktu

- **Opsi A (Sederhana):** 30 menit - 1 jam
- **Opsi B (Lengkap):** 2-3 jam
- **Dengan Jurnal Keuangan:** +1-2 jam
- **Testing Lengkap:** 2-3 jam
- **Total:** 3-9 jam tergantung opsi yang dipilih

---

## Rekomendasi

**Gunakan Opsi B (dengan Purchase)** karena:
1. Konsisten dengan sistem pembelian normal
2. Tracking supplier lengkap
3. Bisa integrasi dengan jurnal keuangan
4. Data lebih terstruktur dan mudah di-audit
5. Tidak ada perbedaan antara stok initial dan pembelian normal

**Tambahkan Jurnal Keuangan** jika:
1. Sistem keuangan sudah digunakan
2. Perlu laporan keuangan akurat
3. Perlu audit trail lengkap

---

**Dibuat:** 2025-12-16  
**Versi:** 1.0  
**Status:** Ready for Implementation
