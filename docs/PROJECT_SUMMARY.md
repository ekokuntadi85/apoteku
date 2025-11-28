# Ringkasan Proyek: Sistem POS dan Manajemen Inventaris

Dokumen ini memberikan ringkasan fungsional dan teknis dari aplikasi. Tujuannya adalah untuk mempercepat proses pemahaman kembali (inisiasi) terhadap codebase.

## 1. Gambaran Umum

Aplikasi ini adalah sistem **Point of Sale (POS) dan Manajemen Inventaris** berbasis web yang dibangun menggunakan **PHP Laravel** dan **Livewire**. Aplikasi ini dirancang untuk mengelola seluruh siklus produk, mulai dari pembelian, manajemen stok, penjualan, hingga pelaporan.

- **Framework Backend:** Laravel
- **Framework Frontend:** Livewire (untuk komponen UI yang dinamis)
- **Fitur Utama:** Manajemen Produk, Pembelian, Penjualan (POS), Laporan, Manajemen Stok, Manajemen Pengguna.

---

## 2. Entitas Inti (Core Models)

Struktur data utama aplikasi berpusat pada model-model berikut di direktori `app/Models/`:

- **`Product`**: Merepresentasikan produk yang dijual.
- **`ProductBatch`**: Entitas kunci untuk manajemen stok. Setiap kali ada pembelian, sebuah *batch* baru dibuat dengan informasi harga beli, stok, dan tanggal kedaluwarsa. Stok dikelola per-batch.
- **`Purchase`**: Mencatat transaksi pembelian barang dari `Supplier`.
- **`Transaction`**: Mencatat transaksi penjualan kepada `Customer`.
- **`TransactionDetail`**: Rincian item produk dalam sebuah `Transaction`.
- **`StockMovement`**: Log untuk setiap pergerakan stok (masuk/keluar), memberikan jejak audit yang jelas.
- **`StockOpname`**: Digunakan untuk proses penghitungan dan penyesuaian stok fisik.
- **`Customer` & `Supplier`**: Manajemen data pelanggan dan pemasok.
- **`User`**: Manajemen pengguna dan hak akses.
- **`Category` & `Unit`**: Data master untuk kategori dan satuan produk.

---

## 3. Fitur Utama (Berdasarkan Komponen Livewire)

Fitur-fitur aplikasi sebagian besar diimplementasikan sebagai komponen Livewire (`app/Livewire/`).

### a. Manajemen Data Master
- **Manajemen Produk (`ProductManager`, `ProductCreate`, `ProductEdit`)**: Operasi CRUD untuk produk.
- **Manajemen Kategori (`CategoryManager`)**: Mengelola kategori produk.
- **Manajemen Satuan (`UnitManager`)**: Mengelola satuan produk (Pcs, Box, etc).
- **Manajemen Pelanggan & Pemasok (`CustomerManager`, `SupplierManager`)**: CRUD untuk data pelanggan dan pemasok.

### b. Manajemen Transaksi
- **Point of Sale (POS) (`PointOfSale`)**: Antarmuka kasir utama untuk melakukan penjualan dengan cepat.
- **Manajemen Penjualan (`TransactionManager`, `TransactionCreate`)**: Mengelola riwayat transaksi penjualan.
- **Manajemen Pembelian (`PurchaseManager`, `PurchaseCreate`)**: Mengelola riwayat transaksi pembelian dari pemasok.
- **Piutang (`AccountsReceivable`)**: Memonitor dan mengelola faktur penjualan yang belum lunas.

### c. Manajemen Stok & Inventaris
- **Kartu Stok (`StockCard`)**: Melacak riwayat pergerakan stok untuk setiap produk.
- **Stok Opname (`InventoryCount`)**: Fitur untuk melakukan penghitungan stok fisik dan membuat penyesuaian.
- **Pemeriksaan Konsistensi Stok (`StockConsistencyCheck`)**: Alat bantu untuk memvalidasi integritas data stok.

### d. Pelaporan
- **Pusat Laporan Penjualan (`SalesReportCenter`)**: Menyediakan berbagai jenis laporan penjualan (ringkasan, detail, per item).
- **Laporan Stok Kritis (`LowStockReport`)**: Menampilkan produk yang stoknya di bawah ambang batas minimum.
- **Laporan Stok Kedaluwarsa (`ExpiringStockReport`)**: Menampilkan produk yang mendekati tanggal kedaluwarsa.
- **Ekspor Laporan**: Sebagian besar laporan dapat diekspor ke format Excel (misal: `DetailedSalesReportExport.php`).

### e. Utilitas & Pengaturan
- **Manajemen Pengguna (`UserManager`)**: Mengelola akun pengguna dan peran (dibatasi untuk Super Admin).
- **Impor Produk (`ProductImportManager`)**: Mengimpor data produk secara massal dari file.
- **Backup & Restore Database (`DatabaseBackupManager`, `DatabaseRestoreManager`)**: Utilitas untuk mencadangkan dan memulihkan database.
- **Pengaturan (`Settings/`)**: Mengubah profil, password, dan tampilan.

---

## 4. Logika Bisnis Kunci

Logika bisnis paling kritikal terpusat di beberapa area:

### a. `StockService.php`
Ini adalah otak dari manajemen stok.
- **`decrementStock(TransactionDetail $detail)`**:
    1.  Dipanggil secara otomatis saat penjualan dibuat.
    2.  Mengambil stok dari `ProductBatch` berdasarkan **FEFO (First Expired First Out)** dengan mengurutkan batch berdasarkan `expiration_date`.
    3.  Memiliki validasi untuk mencegah penjualan melebihi stok yang tersedia (`overselling`).
    4.  Mencatat setiap pengurangan stok di tabel `stock_movements`.
    5.  Menghubungkan detail transaksi dengan batch spesifik dari mana stok diambil (`transaction_detail_batches`).
- **`incrementStock(TransactionDetail $detail)`**:
    1.  Dipanggil saat penjualan dibatalkan atau ada retur.
    2.  Mengembalikan stok ke `ProductBatch` asalnya.
    3.  Mencatat penambahan stok di `stock_movements`.

### b. `Observers` (`app/Observers/`)
- **`TransactionDetailObserver`**: Kemungkinan besar observer ini yang memanggil `StockService->decrementStock()` setelah `TransactionDetail` dibuat dan `StockService->incrementStock()` saat `TransactionDetail` dihapus.
- **`ProductBatchObserver`**: Mungkin digunakan untuk mencatat pergerakan stok awal saat `ProductBatch` dibuat dari transaksi pembelian.

---

## 5. Rencana Fitur Masa Depan

Berdasarkan file `rencana_fitur_tutup_tahun.md`, ada rencana signifikan untuk fitur **Tutup Tahun**.

- **Tujuan**: Menjaga performa aplikasi dengan mengarsipkan data transaksi lama.
- **Metode yang Direkomendasikan**: **Arsialisasi**, bukan penghapusan. Data lama (transaksi, pembelian, pergerakan stok) akan dipindahkan ke tabel arsip (misal: `transactions_archive`).
- **Proses**:
    1.  Memvalidasi bahwa tidak ada transaksi yang belum lunas pada periode yang akan ditutup.
    2.  Membuat "Saldo Awal" untuk tahun berikutnya dengan menyalin sisa stok dari semua `ProductBatch` yang ada ke dalam satu `Purchase` sistem baru.
    3.  Memindahkan data transaksi dari periode yang telah ditutup ke tabel arsip.
    4.  Menghapus data yang sudah diarsipkan dari tabel utama.
