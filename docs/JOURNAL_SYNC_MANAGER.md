# Journal Sync Manager - User Guide

## 🎯 Tujuan

Halaman **Sinkronisasi Jurnal** memisahkan proses restore database dari sinkronisasi jurnal, sehingga:
- ✅ Restore database **10x lebih cepat**
- ✅ User punya **kontrol penuh** kapan sync
- ✅ **Visibility** yang jelas tentang status jurnal

## 📍 Lokasi

**Menu:** Keuangan > Sinkronisasi Jurnal

**URL:** `/finance/journal-sync`

## 📊 Dashboard Statistics

### 1. Data Sumber
- **Penjualan:** Jumlah transaksi POS
- **Pembelian:** Jumlah purchase orders
- **Pengeluaran:** Jumlah expense records

### 2. Journal Entries
- **Sales:** Journal untuk revenue (INV-*)
- **COGS:** Journal untuk harga pokok (COGS-*)
- **Purchase:** Journal untuk pembelian (PUR-*)
- **Expense:** Journal untuk pengeluaran (EXP-*)

### 3. Total Journals
Jumlah total journal entries yang sudah dibuat.

### 4. Coverage
Persentase coverage jurnal:
- **≥ 90%:** ✅ Sangat Baik (hijau)
- **70-89%:** ⚠️ Perlu Sync (kuning)
- **< 70%:** ❌ Perlu Sync Penuh (merah)

## 🔧 Aksi yang Tersedia

### 1. Sync Semua
**Fungsi:** Sinkronisasi SEMUA transaksi, pembelian, dan pengeluaran ke journal entries.

**Kapan digunakan:**
- Setelah restore database lama
- Coverage < 90%
- Ada data baru yang belum ter-sync

**Proses:**
1. Scan semua purchases → Create PUR-* journals
2. Scan semua transactions → Create INV-* dan COGS-* journals
3. Scan semua expenses → Create EXP-* journals
4. Skip yang sudah ada (idempotent)

**Durasi:** ~1-5 menit untuk 10,000 transaksi

### 2. Fix Missing COGS
**Fungsi:** Perbaiki COGS yang hilang dengan fallback ke latest batch.

**Kapan digunakan:**
- Setelah sync semua, masih ada transaksi tanpa COGS
- Data lama tanpa batch tracking

**Proses:**
1. Cari transaction details tanpa COGS journal
2. Calculate COGS dari batch data
3. Fallback ke latest batch jika tidak ada detail batch
4. Create missing COGS journals

**Durasi:** ~30 detik - 2 menit

### 3. Hapus Semua Journals
**Fungsi:** Hapus SEMUA journal entries dan details.

**⚠️ PERINGATAN:** Ini **TIDAK BISA DI-UNDO**!

**Kapan digunakan:**
- Sebelum re-sync penuh (untuk clean slate)
- Ada duplikasi journals
- Troubleshooting

**Proses:**
1. Truncate `journal_entries` table
2. Truncate `journal_details` table
3. Setelah ini, jalankan "Sync Semua"

## 📝 Workflow Recommended

### Scenario 1: Restore Database Lama
```
1. Restore database via Database Manager
   → Auto-sync DISABLED (cepat!)
   
2. Buka "Sinkronisasi Jurnal"
   → Lihat coverage (kemungkinan 0%)
   
3. Klik "Sync Semua"
   → Tunggu sampai selesai (~2-5 menit)
   
4. Jika ada warning "Skipped COGS", klik "Fix Missing COGS"
   → Tunggu sampai selesai (~1 menit)
   
5. Refresh Stats
   → Coverage harus ≥ 90%
```

### Scenario 2: Data Sudah Ada, Tapi Coverage Rendah
```
1. Buka "Sinkronisasi Jurnal"
   → Cek coverage

2. Jika < 90%, klik "Sync Semua"
   → Sistem akan skip yang sudah ada
   
3. Refresh Stats
   → Coverage harus naik
```

### Scenario 3: Re-sync Penuh (Clean Slate)
```
1. Klik "Hapus Semua Journals"
   → Confirm (HATI-HATI!)
   
2. Klik "Sync Semua"
   → Build journals dari awal
   
3. Klik "Fix Missing COGS"
   → Lengkapi COGS yang hilang
   
4. Refresh Stats
   → Coverage harus 90-100%
```

## 🐛 Troubleshooting

### Coverage Stuck di < 50%
**Penyebab:** Banyak data tanpa batch tracking

**Solusi:**
1. Jalankan "Sync Semua" dulu
2. Lalu "Fix Missing COGS"
3. Jika masih rendah, cek log untuk detail

### Sync Timeout
**Penyebab:** Terlalu banyak data (>50,000 transaksi)

**Solusi:**
1. Jalankan via terminal: `php artisan finance:sync-historical-journals`
2. Atau increase PHP `max_execution_time`

### Duplikasi Journals
**Penyebab:** Sync dijalankan berkali-kali tanpa check

**Solusi:**
1. Klik "Hapus Semua Journals"
2. Klik "Sync Semua" (sekali saja)

## 📈 Expected Results

Setelah sync penuh, Anda harus lihat:

**Buku Besar:**
- Akun 101 (Kas): Ada transaksi penjualan & pembelian
- Akun 401 (Pendapatan): Ada semua penjualan
- Akun 501 (HPP): Ada COGS untuk setiap item terjual

**Laporan Laba Rugi:**
- Pendapatan > 0
- HPP > 0
- Laba Kotor = Pendapatan - HPP
- Beban Operasional > 0
- Laba Bersih calculated

**Laporan Neraca:**
- Assets = Liabilities + Equity (BALANCE!)

## 🔐 Permissions

Halaman ini memerlukan permission:
- `view_financial_reports` (untuk akses menu)

## 💡 Tips

1. **Jalankan sync saat low-traffic** (malam/weekend) untuk database besar
2. **Backup database** sebelum "Hapus Semua Journals"
3. **Monitor log** untuk warning tentang missing data
4. **Refresh stats** setelah setiap aksi untuk lihat progress
5. **Coverage 100%** tidak selalu mungkin (data lama tanpa batch)

## 📞 Support

Jika ada masalah:
1. Capture screenshot dashboard
2. Copy log output
3. Check `storage/logs/laravel.log`
4. Report dengan info: jumlah transaksi, coverage %, error message
