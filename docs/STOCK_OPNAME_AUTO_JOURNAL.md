# Stock Opname Auto-Journal Feature

## Overview

Sistem sekarang **otomatis membuat jurnal keuangan** saat stock opname difinalisasi, sehingga selisih stock langsung tercatat di laporan keuangan.

## How It Works

### 1. Draft Mode (Default)

Saat buat stock opname baru:
- ✅ Status = **DRAFT**
- ✅ Stock sudah disesuaikan  
- ❌ **Belum ada jurnal** ke keuangan
- ✅ Masih bisa diedit/dihapus

### 2. Finalize

Saat klik **Finalize** (atau run command):
- ✅ Calculate total selisih nilai (qty × harga beli)
- ✅ **Auto-create jurnal** ke keuangan
- ✅ Status berubah jadi **FINALIZED**
- ❌ Tidak bisa diedit lagi

## Journal Entry Format

### Skenario 1: LOSS (Kehilangan Stock)

Jika stock fisik **lebih kecil** dari system:

```
Tanggal: [opname_date]
Ref: OP-[id]
Deskripsi: Penyesuaian Stock Opname...

Debit:  502 - Biaya Selisih Stock    Rp XXX
Kredit: 104 - Persediaan Obat         Rp XXX
```

**Efek ke Laporan:**
- Persediaan Obat **turun**
- Biaya **naik** (laba **turun**)

### Skenario 2: GAIN (Kelebihan Stock)

Jika stock fisik **lebih besar** dari system:

```
Tanggal: [opname_date]
Ref: OP-[id]
Deskripsi: Penyesuaian Stock Opname...

Debit:  104 - Persediaan Obat         Rp XXX
Kredit: 801 - Pendapatan Lain-lain    Rp XXX
```

**Efek ke Laporan:**
- Persediaan Obat **naik**
- Pendapatan Lain-lain **naik** (laba **naik**)

### Skenario 3: NO ADJUSTMENT

Jika stock fisik = stock system:
- ✅ Status jadi finalized
- ❌ **Tidak ada jurnal** (karena tidak ada selisih)

## Usage

### Via Artisan Command

```bash
# Finalize opname dengan ID 5
php artisan opname:finalize 5
```

**Output:**
```
Finalizing Stock Opname #5...
+----------------+-------------------+
| Info           | Value             |
+----------------+-------------------+
| Opname Date    | 31/12/2025        |
| Notes          | Stock akhir tahun |
| Details Count  | 20                |
| Status         | draft             |
+----------------+-------------------+

Adjustment Value: Rp -500,000
Type: LOSS (akan create journal expense)

✅ Stock Opname #5 finalized successfully!
Journal Entry ID: 3058
Reference: OP-5
```

### Via UI (Future)

*Coming soon - Tombol "Finalize" di halaman detail stock opname*

## Important Notes

> [!IMPORTANT]
> **Chart of Accounts Required:**
> - ✅ `104` - Persediaan Obat (sudah ada)
> - ⚠️ `502` - Biaya Selisih Stock (buat jika belum ada)
> - ⚠️ `801` - Pendapatan Lain-lain (auto-create jika belum ada)

> [!WARNING]  
> **Finalized opname tidak bisa diedit!**
> Pastikan data sudah benar sebelum finalize.

> [!TIP]
> **Best Practice:**
> 1. Buat stock opname dalam status DRAFT
> 2. Review semua selisih
> 3. Pastikan angka sudah benar
> 4. Baru finalize untuk create journal

## Verification

Setelah finalize, cek:

**1. Status Opname:**
```sql
SELECT id, status, journal_entry_id FROM stock_opnames WHERE id = X;
```

**2. Journal Entry:**
```sql
SELECT * FROM journal_entries WHERE reference_number = 'OP-X';
```

**3. Neraca Saldo:**
- Buka: **Keuangan → Neraca Saldo**
- Cek akun 104, 502, atau 801

## Troubleshooting

**Q: Error "Akun Biaya Selisih Stock (502) tidak ditemukan"**

A: Buat akun baru:
```sql
INSERT INTO accounts (code, name, type, normal_balance) 
VALUES ('502', 'Biaya Selisih Stock', 'expense', 'debit');
```

**Q: Bisa un-finalize?**

A: Tidak bisa. Tapi bisa:
1. Delete journal manual
2. Update status ke draft manual (via SQL)
3. Edit opname
4. Finalize ulang

**NOT RECOMMENDED** - lebih baik buat opname baru.

## Migration Info

Database changes:
- Added `status` column (enum: draft/finalized)
- Added `journal_entry_id` column (FK to journal_entries)

Migration:
```
2026_01_01_090046_add_status_and_journal_to_stock_opnames.php
```

## API / Code Usage

```php
use App\Services\StockOpnameService;

$service = new StockOpnameService();
$opname = StockOpname::find(5);

try {
    $service->finalizeOpname($opname);
    // Success!
} catch (\Exception $e) {
    // Handle error
}
```

## Support

Jika ada pertanyaan atau masalah, cek:
- Log: `storage/logs/laravel.log`
- Search for: `Finalizing stock opname`
