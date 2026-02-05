# Fitur Tutup Tahun (Year-End Closing)

## 📋 **Deskripsi**

Fitur **Tutup Tahun** memungkinkan administrator untuk mengarsipkan data transaksi tahun lama dan membuat saldo awal (opening balance) untuk tahun baru. Proses ini membantu menjaga performa aplikasi tetap optimal dengan mengurangi volume data aktif.

## ⚠️ **PERINGATAN PENTING**

> **CAUTION:** Fitur ini bersifat **DESTRUKTIF** dan akan **MENGHAPUS DATA PERMANEN**!

### Data yang AKAN DIHAPUS:
- ❌ Semua transaksi penjualan (`transactions`)
- ❌ Semua pembelian (`purchases`)
- ❌ Semua jurnal akuntansi (`journal_entries`, `journal_details`)
- ❌ Semua biaya operasional (`expenses`)
- ❌ Semua log pergerakan stok (`stock_movements`)
- ❌ Semua pembayaran cicilan (`payments`)
- ❌ Product batches dengan stok = 0

### Data yang DIPERTAHANKAN:
- ✅ Master data: `products`, `customers`, `suppliers`, `categories`, `units`
- ✅ User accounts dan permissions
- ✅ Product batches dengan stok > 0 (akan dikonversi ke saldo awal)
- ✅ Settings aplikasi

### Backup Otomatis:
- 🛡️ Database akan di-backup **OTOMATIS** sebelum proses dimulai
- 📦 Backup tersimpan lokal dan di Dropbox (jika configured)

---

## 🚀 **Cara Menggunakan**

### **Melalui Web UI** (Recommended)

1. **Login sebagai Super Admin**
   - Hanya super admin yang dapat mengakses fitur ini

2. **Akses Menu Tutup Tahun**
   ```
   Navigate to: /year-end-closing
   ```

3. **Pilih Tahun yang Akan Ditutup**
   - Gunakan dropdown untuk memilih tahun
   - Hanya tahun lalu yang dapat dipilih (tahun berjalan tidak bisa)

4. **Klik "Proses Tutup Tahun"**
   - System akan menampilkan modal konfirmasi

5. **Konfirmasi dengan Mengetik**
   ```
   TUTUP TAHUN {YEAR}
   ```
   - Contoh: `TUTUP TAHUN 2024`
   - Ketik PERSIS seperti yang diminta (case-insensitive)

6. **Submit**
   - Proses akan berjalan (biasanya 1-3 menit tergantung volume data)
   - Progress akan ditampilkan
   - Jangan tutup browser selama proses berjalan

7. **Selesai**
   - Success message akan muncul dengan ringkasan:
     - Jumlah transaksi yang dihapus
     - Jumlah pembelian yang dihapus
     - Jumlah jurnal yang dihapus
     - Invoice number saldo awal yang dibuat
     - Path file backup

---

### **Melalui CLI** (Advanced)

Untuk automasi atau testing:

```bash
# Basic usage
docker compose exec app php artisan year:close 2024

# With custom user ID
docker compose exec app php artisan year:close 2024 --user-id=1

# Skip backup (NOT RECOMMENDED)
docker compose exec app php artisan year:close 2024 --no-backup
```

**Options:**
- `{year}` - Tahun yang akan ditutup (required)
- `--user-id={id}` - ID user yang menjalankan (default: 1)
- `--no-backup` - Skip database backup (TIDAK DIREKOMENDASIKAN)

**Output:**
```
===========================================
   YEAR-END CLOSING PROCESS
===========================================

Closing Year: 2024
User ID: 1

? Are you sure you want to close year 2024? This will DELETE all transaction data! (yes/no) [no]:
 > yes

Starting year-end closing process...

[1/4] Validating...
✓ Validation passed

[2/4] Creating database backup...
This may take a few minutes...
✓ Backup created: backup-2025-12-31-142530.sql.gz

[3/4] Creating opening balance...
 3/3 [============================] 100%
✓ Opening balance created

[4/4] Process completed successfully!

===========================================
   SUMMARY
===========================================
+---------------------------+--------+
| Item                      | Count  |
+---------------------------+--------+
| Transactions Deleted      | 1,234  |
| Purchases Deleted         | 567    |
| Journal Entries Deleted   | 890    |
| Expenses Deleted          | 123    |
| Payments Deleted          | 45     |
| Stock Movements Deleted   | 2,345  |
| Opening Purchase          | SA-2025|
| Backup File               | backup-...|
+---------------------------+--------+

Year-end closing for 2024 completed successfully! ✓
```

---

## 🔍 **Apa yang Terjadi di Backend?**

### **Proses Step-by-Step:**

1. **Validasi**
   - ✓ Tahun yang ditutup harus tahun lalu (tidak boleh tahun ini)
   - ✓ Tahun tersebut belum pernah ditutup sebelumnya
   - ✓ Tidak ada proses tutup tahun lain yang sedang berjalan

2. **Database Backup**
   - Menjalankan command `backup:auto`
   - Membuat backup compressed (.sql.gz)
   - Upload ke Dropbox (jika configured)
   - Menyimpan path backup untuk audit

3. **Membuat Saldo Awal**
   - Cari/buat supplier "SALDO AWAL SISTEM"
   - Buat purchase baru dengan invoice `SA-{tahun_baru}`
   - Tanggal purchase: 01-01-{tahun_baru}
   - Loop semua product batches dengan stock > 0:
     - Buat batch baru linked ke purchase saldo awal
     - Copy: product_id, stock, purchase_price, expiration_date, unit
     - Batch number: `SA-{tahun_baru}-{original_batch_number}`
   - Hitung total value dari semua batch
   - Update total_price pada purchase

4. **Hapus Batch Lama**
   - Hapus semua product batches lama (yang sudah dikonversi)
   - Hanya batch dari saldo awal yang tersisa

5. **Hapus Transaction Data**
   - Urutan penghapusan (untuk mencegah foreign key errors):
     1. `transaction_detail_batches`
     2. `transaction_details`
     3. `payments`
     4. `transactions`
     5. `product_batches` (stock = 0)
     6. `purchases`
     7. `stock_movements`
     8. `journal_details`
     9. `journal_entries`
     10. `expenses`

6. **Update Audit Record**
   - Simpan statistik penghapusan
   - Update status = 'completed'
   - Simpan timestamp closed_at

---

## 📊 **Riwayat Tutup Tahun**

Setiap proses tutup tahun akan tercatat di tabel `year_end_closings` dengan informasi:

| Field | Deskripsi |
|-------|-----------|
| `closing_year` | Tahun yang ditutup (e.g., 2024) |
| `closed_at` | Timestamp proses selesai |
| `closed_by` | User ID yang menjalankan |
| `opening_purchase_id` | ID purchase saldo awal yang dibuat |
| `total_*_deleted` | Statistik jumlah record yang dihapus |
| `backup_file_path` | Path file backup database |
| `status` | Status: processing, completed, failed |
| `error_message` | Pesan error (jika gagal) |

**View History:**
- Akses `/year-end-closing`
- Scroll ke bagian "Riwayat Tutup Tahun"
- Lihat semua proses tutup tahun yang pernah dilakukan

---

## 🔄 **Rollback (Jika Terjadi Error)**

### **Scenario 1: Proses Gagal Tengah Jalan**
- ✅ **Database transaction** akan rollback otomatis
- ✅ Tidak ada data yang terhapus
- ✅ Check error message di log

### **Scenario 2: Proses Berhasil tapi Ada Masalah**
- ⚠️ Restore dari backup database
- Cara restore:
  ```bash
  # Via UI
  Navigate to: /database-restore
  Select backup file → Restore
  
  # Via Command
  docker compose exec app php artisan db:restore {backup_filename}
  ```

---

## 🧪 **Testing di Development**

**SELALU test di development sebelum production!**

```bash
# 1. Buat backup dulu
docker compose exec app php artisan backup:auto

# 2. Check data sebelum tutup tahun
docker compose exec app php artisan tinker
> \App\Models\Transaction::count();
> \App\Models\Purchase::count();
> \App\Models\ProductBatch::where('stock', '>', 0)->count();
> exit

# 3. Execute tutup tahun
docker compose exec app php artisan year:close 2024

# 4. Verify data setelah tutup tahun
docker compose exec app php artisan tinker
> \App\Models\Transaction::count(); // Should be 0
> \App\Models\Purchase::count(); // Should be 1 (opening balance)
> $opening = \App\Models\Purchase::first();
> echo $opening->invoice_number; // SA-2025
> echo $opening->supplier->name; // SALDO AWAL SISTEM
> \App\Models\ProductBatch::where('stock', '>', 0)->count(); // Same as before
> exit

# 5. Test POS masih berfungsi
# Navigate to /pos
# Add product
# Check stock correct
# Complete transaction
```

---

## 📝 **Best Practices**

### **Sebelum Tutup Tahun:**
1. ✅ Pastikan semua transaksi tahun tersebut sudah dicatat
2. ✅ Lakukan stock opname untuk validasi stok fisik
3. ✅ Pastikan tidak ada transaksi yang sedang pending
4. ✅ Backup manual tambahan (double safety)
5. ✅ Informasikan ke semua user bahwa system akan maintenance
6. ✅ Lakukan di off-peak hours (malam hari/weekend)

### **Setelah Tutup Tahun:**
1. ✅ Verifikasi saldo awal sudah benar
2. ✅ Test beberapa transaksi untuk memastikan system normal
3. ✅ Check laporan untuk memastikan tidak ada anomali
4. ✅ Simpan file backup di tempat aman
5. ✅ Dokumentasikan process untuk referensi ke depan

---

## 🛠️ **Troubleshooting**

### **Error: "Tidak dapat menutup tahun {year}"**
- ✓ Pastikan year adalah tahun lalu
- ✓ Tidak bisa tutup tahun berjalan (2025)

### **Error: "Tahun {year} sudah pernah ditutup"**
- ✓ Check riwayat tutup tahun
- ✓ Jika memang perlu re-close, hapus record di `year_end_closings` table (via database direct)

### **Error: "Terdapat proses tutup tahun yang sedang berjalan"**
- ✓ Check apakah ada proses lain yang stuck
- ✓ Tunggu hingga selesai atau ubah status di database

### **Error saat Backup:**
- ✓ Check space disk cukup
- ✓ Check Dropbox connection (jika enabled)
- ✓ Check permissions folder storage

### **Stock tidak sesuai setelah tutup tahun:**
- ⚠️ **ROLLBACK IMMEDIATELY**
- ⚠️ Restore dari backup
- ⚠️ Report bug ke developer

---

## 🔐 **Security**

- 🔒 Hanya **Super Admin** yang dapat mengakses
- 🔒 Konfirmasi text required untuk mencegah accident
- 🔒 Audit trail lengkap (user, timestamp, statistics)
- 🔒 Backup otomatis untuk safety
- 🔒 Database transaction untuk atomicity

---

## 📚 **Technical Details**

### **Files Involved:**
```
database/migrations/
  └─ 2025_12_31_000000_create_year_end_closings_table.php

app/Models/
  └─ YearEndClosing.php

app/Services/
  └─ YearEndClosingService.php

app/Livewire/
  └─ YearEndClosingManager.php

resources/views/livewire/
  └─ year-end-closing-manager.blade.php

routes/
  └─ web.php (added route)

app/Console/Commands/
  └─ ExecuteYearEndClosing.php
```

### **Database Tables:**
- `year_end_closings` - Audit trail
- Modified: None
- Deleted data from: all transaction-related tables

---

## 📞 **Support**

Jika mengalami masalah:
1. Check log file: `storage/logs/laravel.log`
2. Check error message di UI
3. Restore dari backup jika diperlukan
4. Contact system administrator

---

**Last Updated:** 2025-12-31  
**Version:** 1.0.0
