Panduan Instalasi Aplikasi Apotek ke server.

Requirement :
Docker Engine dan Docker Compose

Jalankan step berikut.
1. buat file environment .env sesuai konfigurasi yang anda inginkan, jadikan .env.sample sebagai acuan
2. jalankan perintah "docker compose build" terlebih dahulu
3. setelah image terbentuk jalankan perintah "docker compose up -d"
4. lakukan migrasi dengan perintah "docker compose exec app php artisan migrate" dan "docker compose exec app php artisan db:seed" untuk memasukkan user superadmin dan user kasir contoh
5. lakukan build frontend dengan perintah "docker compose exec app npm run build"