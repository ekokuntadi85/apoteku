# Tutorial Penyesuaian Jurnal Setelah Tutup Tahun

## Masalah Yang Terjadi

Setelah tutup tahun dan sync jurnal, neraca menunjukkan:

```
ASET (AKTIVA)
├── 101 - Kas: Rp -32.193.625,90  ❌ (NEGATIF)
└── 104 - Persediaan Obat: Rp 32.193.625,90 ✅
TOTAL ASET: Rp 0,00 ❌
```

**Root Cause:**
Opening balance purchase (SA-2026) membuat jurnal otomatis via `PurchaseObserver`:
- Debit: Persediaan Obat Rp 32.19 juta ✅
- Kredit: Utang Usaha Rp 32.19 juta ❌ (ini yang salah!)

Kemudian status "paid" membuat jurnal lagi:
- Debit: Utang Usaha Rp 32.19 juta
- Kredit: Kas Rp 32.19 juta ❌ (ini yang bikin kas negatif!)

## Solusi Yang Sudah Diterapkan

✅ **Fix Otomatis untuk Tutup Tahun Berikutnya:**
- Observer sudah diperbaiki untuk skip opening balance (invoice SA-XXXX)
- Tutup tahun berikutnya tidak akan ada masalah ini lagi

## Solusi untuk Data Sekarang

### Opsi 1: Manual Jurnal Penyesuaian (RECOMMENDED - Quick Fix)

Buat jurnal penyesuaian untuk:
1. Hapus utang usaha yang salah
2. Catat persediaan sebagai modal awal

**Langkah-langkah:**

1. **Buka Menu Keuangan → Jurnal Umum**

2. **Klik "Tambah Jurnal"**

3. **Isi Form:**
   ```
   Tanggal: 01/01/2026
   Nomor Referensi: ADJ-2026-001
   Deskripsi: Penyesuaian Saldo Awal Tahun 2026
   ```

4. **Tambah Entries:**

   **Debit:**
   - Akun: 201 - Utang Usaha
   - Jumlah: Rp 32.193.625,90
   
   **Kredit:**
   - Akun: 301 - Modal Pemilik  
   - Jumlah: Rp 32.193.625,90

5. **Simpan**

**Hasil Setelah Penyesuaian:**
```
ASET (AKTIVA)
├── 101 - Kas: Rp 0,00 ✅
└── 104 - Persediaan Obat: Rp 32.193.625,90 ✅
TOTAL ASET: Rp 32.193.625,90 ✅

KEWAJIBAN
└── 201 - Utang Usaha: Rp 0,00 ✅

EKUITAS
└── 301 - Modal Pemilik: Rp 32.193.625,90 ✅
```

### Opsi 2: Hapus & Recreate Opening Balance (Advanced)

⚠️ **HANYA jika opsi 1 tidak berhasil**

1. **Backup Database:**
   ```bash
   docker compose exec app php artisan backup:auto
   ```

2. **Hapus Jurnal SA-2026:**
   ```bash
   docker compose exec app php artisan tinker --execute="
   \App\Models\JournalEntry::where('reference_number', 'LIKE', '%SA-2026%')->delete();
   echo 'Deleted opening balance journals';
   "
   ```

3. **Buat Manual Journal Entry:**
   - Tanggal: 01/01/2026
   - Ref: SA-2026-BALANCE
   - Debit: 104 - Persediaan Obat Rp 32.193.625,90
   - Kredit: 301 - Modal Pemilik Rp 32.193.625,90

## Verifikasi

Setelah penyesuaian, cek:

1. **Neraca Saldo:**
   - Total Aset = Persediaan Obat
   - Kas = 0
   - Utang Usaha = 0
   - Modal = Nilai Persediaan

2. **Laba Rugi:**
   - Seharusnya kosong (karena semua transaksi sudah dihapus)

## Penjelasan Akuntansi

**Jurnal yang Benar untuk Opening Balance:**
```
Debit:  Persediaan Obat (Aset)
Kredit: Modal Pemilik (Ekuitas)
```

**Bukan:**
```
Debit:  Persediaan Obat
Kredit: Utang Usaha ❌ (ini salah karena bukan benar-benar utang)
```

Opening balance adalah **ekuitas awal**, bukan utang!

## Catatan Penting

- ✅ Fix sudah diterapkan di `PurchaseObserver.php`
- ✅ Tutup tahun berikutnya tidak akan ada masalah ini
- ✅ Untuk tahun ini, gunakan jurnal penyesuaian manual
- ✅ Setelah penyesuaian, total aset akan match dengan nilai persediaan

## Troubleshooting

**Q: Setelah adjustment, neraca masih tidak balance?**
A: Sync ulang jurnal:
```bash
docker compose exec app php artisan journal:sync-historical
```

**Q: Saya tidak yakin dengan nominal yang diinput?**
A: Cek nilai persediaan:
```bash
docker compose exec app php artisan tinker --execute="
\$opening = \App\Models\Purchase::where('invoice_number', 'LIKE', 'SA-2026%')->first();
echo 'Opening Balance: ' . number_format(\$opening->total_price) . PHP_EOL;
"
```

Nominal yang sama gunakan untuk jurnal adjustment.
