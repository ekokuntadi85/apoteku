# Muazara - Aplikasi Manajemen Inventaris & Point of Sale (POS) untuk Apotek

Muazara adalah aplikasi berbasis web yang dirancang untuk membantu mengelola operasional bisnis, dengan fokus pada manajemen inventaris (stok barang), pembelian, dan penjualan melalui antarmuka Point of Sale (POS) yang terintegrasi.

Aplikasi ini dibangun menggunakan **Laravel** dan **Livewire**, memberikan pengalaman pengguna yang reaktif dan modern tanpa perlu menulis banyak kode JavaScript.

## Fitur Utama

-   **Dashboard Overview**: Tampilan ringkas mengenai status bisnis terkini.
-   **Point of Sale (POS)**: Antarmuka kasir untuk melakukan transaksi penjualan dengan cepat.
-   **Manajemen Produk**: Mengelola data produk, termasuk multi-satuan (misal: pcs, box, karton) dan harga.
-   **Manajemen Stok & Inventaris**:
    -   Pelacakan stok barang berdasarkan batch dan tanggal kedaluwarsa.
    -   Fitur `Stock Opname` untuk penyesuaian stok fisik.
    -   Kartu Stok untuk melacak semua pergerakan barang.
-   **Manajemen Pembelian**: Mencatat transaksi pembelian dari supplier.
-   **Manajemen Pelanggan & Supplier**: Mengelola data pelanggan dan pemasok.
-   **Laporan**:
    -   Laporan Penjualan.
    -   Laporan Stok Menipis (`Low Stock`).
    -   Laporan Stok Mendekati Kedaluwarsa (`Expiring Stock`).
-   **Manajemen Pengguna**: Mengatur hak akses untuk setiap pengguna.
-   **Utilitas**:
    -   Backup dan Restore database.
    -   Impor data produk dari file.

## Teknologi

-   **Backend**: Laravel 11
-   **Frontend**: Livewire 3, Tailwind CSS, Alpine.js
-   **Database**: MySQL / MariaDB
-   **Web Server**: Nginx (atau Caddy, sesuai konfigurasi)
-   **Containerization**: Docker

## Instalasi & Deployment (via Docker)

Metode instalasi yang direkomendasikan adalah menggunakan Docker untuk memastikan konsistensi lingkungan pengembangan dan produksi.

### Prasyarat

-   [Docker](https://docs.docker.com/get-docker/)
-   [Docker Compose](https://docs.docker.com/compose/install/)
-   Git

### Langkah-langkah Instalasi

1.  **Clone Repository**

    ```bash
    git clone https://github.com/ekokuntadi85/muazara.git
    cd muazara
    ```

2.  **Konfigurasi Environment**
    Salin file `.env.sample` menjadi `.env`. Tidak ada perubahan yang diperlukan jika Anda menggunakan konfigurasi Docker bawaan.

    ```bash
    cp .env.sample .env
    ```

3.  **Build dan Jalankan Container Docker**
    Perintah ini akan membuat image dan menjalankan semua service (aplikasi, database, web server) di background.

    ```bash
    docker-compose up -d --build
    ```

4.  **Generate Application Key**

    ```bash
    docker-compose exec app php artisan key:generate
    ```

5.  **Jalankan Migrasi dan Seeder Database**
    Perintah ini akan membuat struktur tabel dan mengisi data awal yang diperlukan.

    ```bash
    docker-compose exec app php artisan migrate
    docker-compose exec app php artisan db:seed
    ```

6.  **Install Dependensi Node.js & Build Aset**

    ```bash
    docker-compose exec app npm run build
    ```

7.  **Link Storage**
    Agar file yang diunggah dapat diakses publik.

    ```bash
    docker-compose exec app php artisan storage:link
    ```

8.  **Selesai!**
    Aplikasi sekarang dapat diakses melalui browser di alamat `http://localhost`.

## Dokumentasi

Untuk dokumentasi lengkap mengenai deployment, fitur, dan pengembangan aplikasi, silakan lihat folder **[docs/](./docs/)**.

Dokumentasi yang tersedia:
- **[Deployment Guide](./docs/DEPLOYMENT.md)** - Panduan deployment lengkap
- **[Project Summary](./docs/PROJECT_SUMMARY.md)** - Ringkasan proyek dan fitur
- **[Custom Artisan Commands](./docs/CUSTOM_ARTISAN_COMMANDS.md)** - Dokumentasi command artisan custom
- **[Improvement Strategy](./docs/IMPROVEMENT_STRATEGY.md)** - Strategi pengembangan
- **[Rencana Fitur Tutup Tahun](./docs/rencana_fitur_tutup_tahun.md)** - Rencana fitur mendatang
