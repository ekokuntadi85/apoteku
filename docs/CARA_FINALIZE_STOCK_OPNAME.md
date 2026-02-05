# Cara Menggunakan Fitur Finalize Stock Opname

## Akses Fitur

1. **Login ke aplikasi**
2. **Menu: Inventory → Stock Opname**
3. **Lihat daftar riwayat opname**

## Indikator Status

Di halaman list, Anda akan lihat badge status:
- 🟡 **DRAFT** = Belum difinalisasi, jurnal belum dibuat
- 🟢 **FINALIZED** = Sudah difinalisasi, jurnal sudah dibuat

## Cara Finalize

### Langkah 1: Klik "Lihat Detail"

Dari list stock opname, klik tombol **"Lihat Detail"** pada opname yang mau difinalisasi.

### Langkah 2: Review Data

Di halaman detail, Anda akan lihat:
- ✅ Summary cards (total items, stock system, stock fisik)
- ✅ Ringkasan penyesuaian (selisih nilai & type)
- ✅ Detail tabel per produk

### Langkah 3: Klik Tombol "Finalize Stock Opname"

Tombol biru besar di bagian bawah halaman (hanya muncul jika status DRAFT)

### Langkah 4: Konfirmasi

Modal konfirmasi akan muncul menampilkan:
- ⚠️ Warning tidak bisa diubah lagi
- 💰 Nilai selisih
- 📊 Type (LOSS/GAIN)
- 📝 Dampak ke jurnal keuangan

### Langkah 5: Klik "Ya, Finalize"

Sistem akan:
1. ✅ Create jurnal otomatis
2. ✅ Update status jadi FINALIZED
3. ✅ Redirect dan tampilkan success message

### Setelah Finalize

Halaman detail akan menampilkan:
- ✅ Badge status FINALIZED (hijau)
- ✅ Card "Jurnal Keuangan" dengan link ke detail jurnal
-❌ Tombol Finalize hilang (sudah selesai)
- ❌ Tidak bisa diedit/dihapus lagi

## Troubleshooting

**Q: Tombol Finalize tidak muncul?**
- Cek status: Jika sudah FINALIZED, tombol tidak muncul
- Refresh halaman

**Q: Error saat finalize?**
- Lihat error message
- Kemungkinan akun 502 (Biaya Selisih Stock) belum dibuat
- Buat akun dulu via Keuangan → Chart of Accounts

**Q: Mau undo finalize?**
- Tidak bisa via UI
- Harus manual via database atau buat opname baru

## Tips

✅ **Best Practice:**
1. Buat opname → status DRAFT
2. Review semua angka dengan teliti
3. Pastikan perhitungan benar
4. Baru klik Finalize

✅ **Cek hasil:**
Setelah finalize, buka **Keuangan → Neraca Saldo** untuk lihat dampaknya

## Screenshot/Demo

URL halaman:
- List: `/stock-opname`
- Detail: `/stock-opname/{id}`

**Status Badge:**
- DRAFT = Kuning/Orange
- FINALIZED = Hijau

**Tombol:**
- "Lihat Detail" = Biru (di list)
- "Finalize Stock Opname" = Biru besar (di detail, hanya untuk DRAFT)
