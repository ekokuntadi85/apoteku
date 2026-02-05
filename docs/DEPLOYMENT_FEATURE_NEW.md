# Panduan Deployment feature/new ke Production

**Tanggal:** 17 Desember 2025  
**Branch:** feature/new → main  
**Status:** ✅ SIAP DEPLOY

---

## 📋 RINGKASAN PERUBAHAN

Branch `feature/new` telah diperbaiki dan siap untuk di-merge ke `main`. Semua bug critical telah diperbaiki dan test sudah PASS.

### Fitur Baru:
1. ✅ Sistem Akuntansi Double-Entry lengkap
2. ✅ Laporan Keuangan (General Ledger, Income Statement, Balance Sheet)
3. ✅ Integrasi Dropbox untuk backup otomatis
4. ✅ Purchase Order linking
5. ✅ Sinkronisasi jurnal historis
6. ✅ Validasi form yang lebih ketat

### Bug yang Telah Diperbaiki:
1. ✅ Journal entries sekarang terhapus otomatis saat data dihapus
2. ✅ COGS race condition telah diperbaiki
3. ✅ Validasi tanggal lebih ketat
4. ✅ Error handling yang lebih baik
5. ✅ Performance optimization dengan chunking

---

## ⚠️ PERSIAPAN SEBELUM DEPLOYMENT

### 1. Backup Database Production

**WAJIB!** Backup database sebelum melakukan deployment.

```bash
# Di server production
docker compose exec app php artisan backup:database

# Atau manual backup
docker compose exec db mysqldump -u root -p apoteku > backup_before_feature_new_$(date +%Y%m%d_%H%M%S).sql
```

**Simpan backup di lokasi aman!**

---

### 2. Cek Requirement

Pastikan server production memenuhi requirement:

- ✅ PHP >= 8.2
- ✅ MySQL/MariaDB >= 8.0
- ✅ Composer installed
- ✅ Node.js & NPM (untuk asset compilation)
- ✅ Disk space minimal 500MB free
- ✅ Memory minimal 512MB untuk PHP

---

### 3. Informasikan User

**Notifikasi ke user minimal 24 jam sebelum deployment:**

```
PEMBERITAHUAN MAINTENANCE

Tanggal: [ISI TANGGAL]
Waktu: [ISI JAM] - [ISI JAM] (estimasi 30 menit)

Sistem akan di-update dengan fitur baru:
- Modul Keuangan lengkap dengan laporan
- Backup otomatis ke Dropbox
- Perbaikan bug dan peningkatan performa

Selama maintenance, sistem tidak dapat diakses.
Mohon simpan pekerjaan Anda sebelum waktu maintenance.

Terima kasih atas pengertiannya.
```

---

## 🚀 LANGKAH DEPLOYMENT

### Step 1: Persiapan di Server

```bash
# 1. Masuk ke server production
ssh user@your-production-server

# 2. Masuk ke direktori aplikasi
cd /path/to/apoteku

# 3. Cek status git
git status
git branch

# 4. Pastikan tidak ada uncommitted changes
# Jika ada, backup atau commit terlebih dahulu
```

### Step 2: Backup & Maintenance Mode

```bash
# 1. Aktifkan maintenance mode
docker compose exec app php artisan down --message="Sedang update sistem. Mohon tunggu beberapa menit."

# 2. Backup database (WAJIB!)
docker compose exec app php artisan backup:database

# 3. Backup file .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# 4. Backup direktori storage
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/
```

### Step 3: Pull & Merge Changes

```bash
# 1. Fetch latest changes
git fetch origin

# 2. Checkout ke main branch
git checkout main

# 3. Pull latest main
git pull origin main

# 4. Merge feature/new ke main
git merge origin/feature/new

# Jika ada conflict, resolve dengan hati-hati
# Jangan lanjutkan jika tidak yakin!
```

### Step 4: Install Dependencies

```bash
# 1. Update composer dependencies
docker compose exec app composer install --no-dev --optimize-autoloader

# 2. Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Step 5: Run Migrations

```bash
# 1. Jalankan migration (HATI-HATI!)
docker compose exec app php artisan migrate --force

# Output yang diharapkan:
# - 2025_12_13_070523_add_purchase_order_id_to_purchases_table
# - 2025_12_15_042512_create_expense_categories_table
# - 2025_12_15_042515_create_expenses_table
# - 2025_12_15_043513_create_journal_entries_table
# - 2025_12_15_043515_create_accounts_table
# - 2025_12_15_043540_create_journal_details_table
# - 2025_12_15_045304_ensure_default_accounts_exist
# - 2025_12_15_045755_add_finance_permissions

# 2. Seed default accounts
docker compose exec app php artisan db:seed --class=AccountSeeder --force
```

### Step 6: Sinkronisasi Data Historis

**PENTING:** Ini akan membuat journal entries untuk semua transaksi/pembelian/expense yang sudah ada.

```bash
# Jalankan sync command
docker compose exec app php artisan finance:sync-historical-journals

# Proses ini bisa memakan waktu tergantung jumlah data
# Estimasi: 100 records/detik
# Jika ada 1000 transaksi = ~10-15 menit
```

**Monitor output:**
- ✅ "Syncing Purchases..." - harus selesai tanpa error
- ✅ "Syncing Transactions (Sales)..." - harus selesai tanpa error
- ✅ "Syncing Expenses..." - harus selesai tanpa error
- ✅ "Synchronization complete!" - konfirmasi selesai

### Step 7: Verifikasi

```bash
# 1. Cek apakah migration berhasil
docker compose exec app php artisan migrate:status

# 2. Cek jumlah journal entries
docker compose exec app php artisan tinker
>>> \App\Models\JournalEntry::count();
>>> \App\Models\Account::count(); // Harus >= 10
>>> exit

# 3. Test akses halaman keuangan (via browser atau curl)
curl -I https://your-domain.com/financial-reports/income-statement
# Harus return 200 atau 302 (redirect to login)
```

### Step 8: Optimize & Cache

```bash
# 1. Optimize autoloader
docker compose exec app composer dump-autoload --optimize

# 2. Cache config
docker compose exec app php artisan config:cache

# 3. Cache routes
docker compose exec app php artisan route:cache

# 4. Cache views
docker compose exec app php artisan view:cache

# 5. Optimize (optional, untuk production)
docker compose exec app php artisan optimize
```

### Step 9: Nonaktifkan Maintenance Mode

```bash
# Aktifkan kembali aplikasi
docker compose exec app php artisan up
```

### Step 10: Testing Post-Deployment

**Lakukan testing manual:**

1. ✅ Login ke sistem
2. ✅ Buat transaksi penjualan baru
3. ✅ Cek apakah journal entry terbuat (Menu: Keuangan > Jurnal Umum)
4. ✅ Buat pembelian baru
5. ✅ Cek laporan keuangan (Income Statement, Balance Sheet)
6. ✅ Test hapus transaksi → cek journal entry ikut terhapus
7. ✅ Test backup Dropbox (jika sudah dikonfigurasi)

---

## 🔧 KONFIGURASI TAMBAHAN (OPTIONAL)

### Dropbox Backup Integration

Jika ingin mengaktifkan backup otomatis ke Dropbox:

```bash
# 1. Edit .env
nano .env

# 2. Tambahkan konfigurasi Dropbox
DROPBOX_APP_KEY=your_app_key
DROPBOX_APP_SECRET=your_app_secret
DROPBOX_REFRESH_TOKEN=your_refresh_token

# 3. Save dan restart
docker compose restart app

# 4. Test koneksi
docker compose exec app php artisan dropbox:test
```

### Setup Cron untuk Auto Backup

```bash
# Edit crontab
crontab -e

# Tambahkan (backup setiap hari jam 2 pagi)
0 2 * * * cd /path/to/apoteku && docker compose exec -T app php artisan backup:database >> /var/log/apoteku-backup.log 2>&1
```

---

## 🆘 ROLLBACK PLAN

Jika terjadi masalah setelah deployment:

### Option 1: Rollback Git

```bash
# 1. Aktifkan maintenance mode
docker compose exec app php artisan down

# 2. Rollback ke commit sebelumnya
git log --oneline -5  # Lihat 5 commit terakhir
git reset --hard <commit-hash-sebelum-merge>

# 3. Rollback migration
docker compose exec app php artisan migrate:rollback --step=8

# 4. Restore database dari backup
docker compose exec -T db mysql -u root -p apoteku < backup_before_feature_new_*.sql

# 5. Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# 6. Nonaktifkan maintenance mode
docker compose exec app php artisan up
```

### Option 2: Restore Full Backup

```bash
# 1. Stop aplikasi
docker compose down

# 2. Restore database
docker compose up -d db
docker compose exec -T db mysql -u root -p apoteku < backup_before_feature_new_*.sql

# 3. Restore .env
cp .env.backup.* .env

# 4. Restore storage (jika perlu)
tar -xzf storage_backup_*.tar.gz

# 5. Start aplikasi
docker compose up -d

# 6. Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
```

---

## 📊 MONITORING POST-DEPLOYMENT

### Hal yang Perlu Dimonitor (24-48 jam pertama):

1. **Error Logs**
   ```bash
   # Monitor error log
   tail -f storage/logs/laravel.log
   
   # Atau via Docker
   docker compose logs -f app
   ```

2. **Database Performance**
   ```bash
   # Cek query slow
   docker compose exec db mysql -u root -p -e "SHOW PROCESSLIST;"
   ```

3. **Disk Space**
   ```bash
   df -h
   ```

4. **Memory Usage**
   ```bash
   free -h
   docker stats
   ```

### Metrics yang Harus Dicatat:

- ✅ Jumlah journal entries sebelum dan sesudah sync
- ✅ Response time halaman laporan keuangan
- ✅ Jumlah error di log (harus 0 atau minimal)
- ✅ User feedback

---

## 📞 KONTAK DARURAT

Jika ada masalah:

1. **Developer:** [Your Name/Team]
2. **Email:** [your-email@domain.com]
3. **Phone:** [your-phone]
4. **Slack/Discord:** [channel-name]

---

## ✅ CHECKLIST DEPLOYMENT

Gunakan checklist ini saat deployment:

### Pre-Deployment
- [ ] Backup database production
- [ ] Backup file .env
- [ ] Backup direktori storage
- [ ] Informasikan user tentang maintenance
- [ ] Pastikan ada akses SSH ke server
- [ ] Pastikan ada backup rollback plan

### Deployment
- [ ] Aktifkan maintenance mode
- [ ] Pull & merge changes dari feature/new
- [ ] Install dependencies (composer install)
- [ ] Clear all cache
- [ ] Run migrations
- [ ] Seed default accounts
- [ ] Run sync historical journals
- [ ] Verify migration status
- [ ] Optimize & cache
- [ ] Nonaktifkan maintenance mode

### Post-Deployment
- [ ] Test login
- [ ] Test create transaction
- [ ] Test journal entry creation
- [ ] Test financial reports
- [ ] Test delete transaction (journal cleanup)
- [ ] Monitor error logs (15 menit pertama)
- [ ] Konfirmasi ke user bahwa sistem sudah aktif
- [ ] Monitor performance (24 jam)

### Dokumentasi
- [ ] Update changelog
- [ ] Update user manual (jika ada)
- [ ] Dokumentasi masalah yang ditemukan
- [ ] Update deployment log

---

## 📝 CATATAN PENTING

1. **Jangan skip backup!** Ini adalah safety net Anda.
2. **Sync historical journals bisa lama** - jangan panic jika progress bar bergerak lambat.
3. **Monitor error log** selama 24 jam pertama setelah deployment.
4. **Siapkan rollback plan** sebelum mulai deployment.
5. **Test di staging dulu** jika memungkinkan.

---

## 🎯 EXPECTED OUTCOME

Setelah deployment berhasil:

✅ Sistem akuntansi aktif dan berfungsi  
✅ Semua transaksi historis memiliki journal entries  
✅ Laporan keuangan menampilkan data yang akurat  
✅ Backup otomatis berjalan (jika dikonfigurasi)  
✅ Tidak ada error di log  
✅ User dapat menggunakan fitur baru tanpa masalah  

---

**Good luck dengan deployment! 🚀**

*Dokumen ini dibuat: 2025-12-17*  
*Last updated: 2025-12-17*
