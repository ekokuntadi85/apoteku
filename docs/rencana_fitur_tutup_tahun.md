# Rencana Fitur: Tutup Tahun (End-of-Year Closing)

Dokumen ini berisi analisis dan rencana implementasi untuk fitur "Tutup Tahun" yang diusulkan.

## 1. Konsep dan Tujuan Utama

Ide ini diajukan untuk menjaga agar database tetap ramping dan performa aplikasi tetap cepat dalam jangka panjang.

- **Tujuan:** Meringkas data transaksi tahunan untuk mempercepat performa aplikasi.
- **Proses:** Di akhir tahun, sebuah proses dijalankan untuk mengubah saldo stok akhir menjadi saldo awal untuk tahun berikutnya, dan data transaksi lama (penjualan dan pembelian) akan dihapus atau diarsipkan.

## 2. Analisis Ide

### Kelebihan
- **Performa Tinggi:** Mengurangi jumlah data di tabel aktif akan mempercepat query dan laporan secara signifikan.
- **Database Ramping:** Menjaga ukuran database utama tetap terkendali.

### Risiko dan Pertimbangan
- **Kehilangan Data Historis:** Menghapus data secara permanen menghilangkan kemampuan untuk menganalisis atau melihat ulang transaksi lama.
- **Kepatuhan & Audit:** Data transaksi seringkali perlu disimpan selama beberapa tahun untuk keperluan pajak atau audit.
- **Transaksi Terbuka:** Proses tidak boleh menghapus faktur pembelian atau penjualan yang belum lunas.
- **Kompleksitas Saldo Awal:** Saldo awal stok harus mempertahankan detail per-batch (harga beli, tanggal kadaluarsa) agar logika FEFO (First Expired First Out) tetap berjalan.

## 3. Rekomendasi: Arsialisasi (Archiving)

Sebagai alternatif yang lebih aman dari penghapusan total, direkomendasikan untuk melakukan **arsialisasi**.

- **Konsep:** Alih-alih `DELETE` data, kita `MOVE` data ke tabel arsip (misal: `transactions_archive`).
- **Keuntungan:** Performa aplikasi tetap cepat, namun data historis tetap aman dan dapat diakses jika diperlukan melalui menu terpisah.

## 4. Rencana Implementasi (Menggunakan Pendekatan Arsialisasi)

### Tahap 1: Persiapan
1.  **Buat Tabel Arsip:** Buat struktur tabel baru yang identik dengan tabel transaksi saat ini (misal: `purchases_archive`, `transactions_archive`, `transaction_details_archive`, `stock_movements_archive`, dll.).
2.  **Buat Menu UI:** Siapkan halaman khusus di dalam aplikasi untuk admin bisa memicu proses "Tutup Tahun". Halaman ini harus memiliki peringatan yang jelas mengenai dampak dari proses ini.

### Tahap 2: Logika Proses "Tutup Tahun"
Proses ini harus dijalankan di dalam satu transaksi database besar (`DB::transaction`) untuk memastikan semua langkah berhasil atau semuanya dibatalkan.

1.  **Validasi Awal:**
    -   Tentukan periode yang akan ditutup (misal: Tahun 2025).
    -   Periksa semua `purchases` dan `transactions` di periode tersebut.
    -   **Tolak proses** jika ditemukan ada transaksi yang statusnya belum lunas (`unpaid` atau `partial`).

2.  **Buat Entitas Saldo Awal:**
    -   Buat satu "Supplier" sistem baru jika belum ada, dengan nama "Saldo Awal Sistem".
    -   Buat satu `Purchase` sistem baru yang terkait dengan supplier tersebut, dengan tanggal 1 Januari tahun baru dan nomor invoice seperti `SA-2026`.

3.  **Proses Saldo Awal Stok:**
    -   Cari semua `ProductBatch` dari periode lama yang masih memiliki `stock > 0`.
    -   Untuk setiap `ProductBatch` tersebut, buat `ProductBatch` **baru** dengan data berikut:
        -   `purchase_id`: ID dari `Purchase` sistem yang baru dibuat.
        -   `product_id`: Sama seperti batch lama.
        -   `stock`: Sisa stok dari batch lama.
        -   `purchase_price`: Harga beli dari batch lama (harga per satuan dasar).
        -   `expiration_date`: Tanggal kadaluarsa dari batch lama.
        -   `product_unit_id`: ID satuan dari batch lama.
        -   `batch_number`: Nomor batch dari batch lama, bisa ditambahkan prefix "SA-".

4.  **Proses Arsialisasi Data Lama:**
    -   Setelah saldo awal berhasil dibuat, lakukan pemindahan data untuk semua transaksi dari periode yang ditutup:
    -   `INSERT INTO purchases_archive SELECT * FROM purchases WHERE ...`
    -   `DELETE FROM purchases WHERE ...`
    -   Lakukan hal yang sama untuk `transactions`, `transaction_details`, `product_batches` (yang stoknya sudah 0 atau sudah disalin), `stock_movements`, dll.

### Tahap 3: Finalisasi
-   Setelah transaksi database berhasil, tampilkan pesan sukses kepada admin.
-   Sistem kini berjalan di atas data yang lebih ramping untuk periode baru.
