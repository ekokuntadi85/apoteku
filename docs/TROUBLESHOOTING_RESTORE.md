# Troubleshooting: Restore Database & Sync Journals

## Masalah: Setelah Restore, Penjualan/Pembelian Tidak Muncul di Buku Besar

### Penyebab:
Backup database lama tidak memiliki journal entries (fitur baru). Sistem harus melakukan sinkronisasi otomatis.

### Solusi Otomatis (Recommended):
1. **Restore via Web Interface** (sudah otomatis trigger sync)
2. Sistem akan mendeteksi data tanpa journal
3. Auto-run command `finance:sync-historical-journals`
4. Cek log restore untuk memastikan sync berhasil

### Solusi Manual (Jika Otomatis Gagal):

#### 1. Jalankan Sync Command Manual:
```bash
docker compose exec app php artisan finance:sync-historical-journals
```

#### 2. Cek Output:
Command akan menampilkan:
- Progress bar untuk Purchases, Transactions, Expenses
- Warning jika ada data tanpa COGS
- Jumlah journal entries yang dibuat

#### 3. Verifikasi Hasil:
```bash
# Cek jumlah journal entries
docker compose exec app php artisan tinker
>>> \App\Models\JournalEntry::count()
>>> \App\Models\Transaction::count()
>>> \App\Models\Purchase::count()
```

**Expected:** Journal entries ≈ (Transactions + Purchases + Expenses) × 2-3

### Debugging Checklist:

#### ✅ Cek Tabel Exists:
```sql
SHOW TABLES LIKE 'journal%';
SHOW TABLES LIKE 'accounts';
```

#### ✅ Cek Accounts Seeded:
```bash
docker compose exec app php artisan tinker
>>> \App\Models\Account::count()
```
**Expected:** Minimal 15 accounts (101-507)

Jika 0, run:
```bash
docker compose exec app php artisan db:seed --class=AccountSeeder
```

#### ✅ Cek Observer Registered:
```bash
docker compose exec app php artisan tinker
>>> \App\Models\Transaction::getObservableEvents()
```
**Expected:** Array berisi 'created', 'updated', dll.

#### ✅ Cek COGS Data:
Jika penjualan tidak punya COGS (data lama tanpa batch tracking):
```bash
# Sync akan gunakan fallback: product->purchase_price
# Pastikan products punya purchase_price
docker compose exec app php artisan tinker
>>> \App\Models\Product::whereNull('purchase_price')->count()
```

Jika banyak yang NULL, update manual:
```sql
UPDATE products 
SET purchase_price = COALESCE(
    (SELECT AVG(purchase_price) FROM product_batches WHERE product_id = products.id),
    0
)
WHERE purchase_price IS NULL OR purchase_price = 0;
```

### Common Issues:

#### 1. "Penjualan tidak masuk, tapi pembelian masuk"
**Penyebab:** COGS = 0 karena tidak ada batch data
**Solusi:** 
- Cek warning di output sync command
- Update product purchase_price (lihat di atas)
- Re-run sync command

#### 2. "Pembayaran piutang/hutang tidak tercatat"
**Penyebab:** Bug di versi lama (sudah diperbaiki)
**Solusi:**
- Pull code terbaru
- Re-run sync command
- Sistem akan detect credit sales dan create payment journals

#### 3. "Sync command timeout"
**Penyebab:** Terlalu banyak data (>10,000 transaksi)
**Solusi:**
- Jalankan via terminal (bukan web)
- Increase PHP max_execution_time
- Atau sync per batch:
```bash
# Sync hanya purchases
docker compose exec app php artisan tinker
>>> (new \App\Console\Commands\SyncHistoricalJournals)->syncPurchases()

# Sync hanya transactions
>>> (new \App\Console\Commands\SyncHistoricalJournals)->syncTransactions()
```

### Verifikasi Akhir:

Setelah sync, cek di aplikasi:
1. **Buku Besar** → Pilih akun 101 (Kas) → Harus ada transaksi
2. **Buku Besar** → Pilih akun 401 (Pendapatan) → Harus ada penjualan
3. **Buku Besar** → Pilih akun 201 (Hutang Usaha) → Harus ada pembelian
4. **Laporan Laba Rugi** → Harus ada revenue & COGS
5. **Laporan Neraca** → Harus balance (Assets = Liabilities + Equity)

### Contact Support:
Jika masih ada masalah setelah langkah di atas, capture:
1. Output dari `finance:sync-historical-journals`
2. Screenshot Buku Besar
3. Jumlah data: `SELECT COUNT(*) FROM transactions; SELECT COUNT(*) FROM journal_entries;`
