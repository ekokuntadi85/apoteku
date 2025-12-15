# Panduan Lengkap Fitur Keuangan & Akuntansi Apoteku

Dokumen ini berisi manual penggunaan modul Keuangan pada aplikasi Apoteku. Sistem ini dirancang untuk mengotomatisasi pencatatan akuntansi (jurnal) dari aktivitas operasional sehari-hari, sehingga laporan keuangan dapat tersaji secara *real-time* dan akurat.

---

## 1. Konsep Dasar Sistem

Sistem keuangan Apoteku bekerja dengan prinsip **Otomatisasi** dan **Kekekalan Data** (Immutability).

1.  **Terintegrasi Otomatis**: Anda tidak perlu menginput jurnal debit/kredit setiap kali ada penjualan obat atau pembelian stok. Sistem akan melakukannya untuk Anda di latar belakang.
2.  **Data Permanen**: Jurnal yang sudah tercatat **tidak dapat diedit atau dihapus**. Ini sesuai standar akuntansi untuk menjaga integritas dan kejujuran data.
3.  **Real-time Reports**: Laporan Neraca dan Laba Rugi selalu *up-to-date* detik itu juga setelah transaksi terjadi.

---

## 2. Memulai: Mencatat Modal Awal

Langkah pertama saat menggunakan sistem keuangan adalah mencatat **Modal Awal**.

**Kapan dilakukan?**
*   Saat pertama kali menggunakan aplikasi.
*   Saat ada suntikan dana investasi baru dari pemilik.

**Caranya:**
1.  Masuk ke menu **Keuangan > Input Jurnal Umum**.
2.  Isi **Tanggal Transaksi** (hari ini).
3.  Isi **Deskripsi**: "Setoran Modal Awal".
4.  Pada tabel Jurnal, buat 2 baris (Debit & Kredit):
    *   **Baris 1 (Debit)**: Pilih akun **101 - Kas** (atau 102 - Bank). Masukkan jumlah uang di kolom **Debit**.
    *   **Baris 2 (Kredit)**: Pilih akun **301 - Modal Awal**. Masukkan jumlah yang sama di kolom **Kredit**.
5.  Pastikan indikator menunjukkan status **Seimbang (Balance)** berwarna hijau.
6.  Klik tombol **Simpan Jurnal**.

---

## 3. Operasional Harian

### A. Penjualan (Pemasukan)
Anda **TIDAK PERLU** menginput manual ke menu keuangan.
*   Cukup gunakan fitur **POS (Kasir)** seperti biasa.
*   Sistem akan otomatis mencatat:
    *   Debit: Kas (Bertambah)
    *   Kredit: Pendapatan Penjualan (Bertambah)
    *   Debit: HPP (Beban Pokok)
    *   Kredit: Persediaan (Stok Berkurang)

### B. Pembelian (Stok Obat)
Anda **TIDAK PERLU** menginput manual ke menu keuangan.
*   Lakukan input di menu **Transaksi > Daftar Pembelian**.
*   Sistem akan otomatis mencatat:
    *   Debit: Persediaan Obat (Aset Bertambah)
    *   Kredit: Hutang Usaha (Kewajiban Bertambah)

### C. Pengeluaran Operasional (Gaji, Listrik, Sewa)
Gunakan fitur ini untuk biaya-biaya di luar pembelian stok obat.

**Caranya:**
1.  Masuk ke menu **Keuangan > Pengeluaran Operasional**.
2.  Klik **Tambah Pengeluaran**.
3.  Pilih **Kategori** (Gaji, Listrik, dll).
4.  Isi jumlah dan deskripsi.
5.  Simpan.
6.  Sistem otomatis mencatat Debit Beban & Kredit Kas.

---

## 4. Membaca Laporan Keuangan

### A. Laporan Neraca (Balance Sheet)
*Menu: Keuangan > Laporan Neraca*
Laporan ini menunjukkan "Kesehatan" apotek Anda di satu titik waktu.
*   **Aset (Kiri)**: Apa yang Anda miliki (Uang tunai, Stok obat, Piutang).
*   **Kewajiban & Modal (Kanan)**: Dari mana aset itu didapat (Hutang supplier atau Modal sendiri).
*   **Wajib Balance**: Total Kiri harus sama dengan Total Kanan.

### B. Laporan Laba Rugi (Income Statement)
*Menu: Keuangan > Laporan Laba Rugi*
Laporan ini menunjukkan "Performa" apotek dalam periode tertentu (Misal: Bulan ini).
*   **Pendapatan**: Total penjualan.
*   **HPP (COGS)**: Modal dasar obat yang terjual.
*   **Gross Profit**: Pendapatan - HPP.
*   **Beban Operasional**: Gaji, Listrik, dll.
*   **Net Profit (Laba Bersih)**: Uang yang benar-benar Anda kantongi.

### C. Buku Besar (General Ledger)
*Menu: Keuangan > Buku Besar*
Gunakan ini untuk **Audit** atau **Investigasi**.
*   Ingin tahu kenapa Saldo Kas tinggal sedikit? Buka Buku Besar akun **Kas**.
*   Anda akan melihat rincian setiap pergerakan uang masuk dan keluar secara kronologis.

---

## 5. Penanganan Kesalahan (Jurnal Koreksi)

Karena jurnal tidak bisa diedit/dihapus, bagaimana jika Anda salah input?
**Contoh Kasus**: Anda salah input pengeluaran listrik Rp 100.000 menjadi Rp 1.000.000.

**Solusi:**
1.  Jangan panik.
2.  Buat **Jurnal Pembalik** di menu **Input Jurnal Umum**:
    *   Balik posisinya: Kas di Debit Rp 1.000.000, Beban Listrik di Kredit Rp 1.000.000.
    *   Berikan Keterangan: "Koreksi kesalahan input transaksi #REF..."
    *   Ini akan menetralkan kesalahan sebelumnya (Saldo menjadi 0).
3.  Input ulang transaksi yang benar (Rp 100.000).

---

## 6. Referensi Kode Akun (Chart of Accounts)

Berikut adalah daftar akun standar yang digunakan sistem:

| Kode | Nama Akun | Tipe | Fungsi |
| :--- | :--- | :--- | :--- |
| **101** | Kas | Aset | Uang tunai di kasir/laci |
| **102** | Bank | Aset | Uang di rekening bank |
| **103** | Piutang Usaha | Aset | Uang kita yang dibawa customer |
| **104** | Persediaan Obat | Aset | Nilai stok barang di gudang |
| **201** | Hutang Usaha | Kewajiban | Tagihan supplier yang belum dibayar |
| **301** | Modal Awal | Ekuitas | Dana investasi pemilik |
| **401** | Pendapatan Penjualan | Pendapatan | Omzet penjualan |
| **501** | Beban Pokok (HPP) | Beban | Modal dasar obat |
| **502** | Beban Gaji | Beban | Gaji karyawan |
| **503** | Beban Listrik & Air | Beban | Utilitas bulanan |
