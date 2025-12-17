# 🚀 Fitur Baru - Database Backup Enhancement

## ✨ 3 Fitur Baru yang Ditambahkan

### 1️⃣ **Auto-hide Notifikasi (3 detik)**

**Deskripsi:**
- Notifikasi sukses/error akan hilang otomatis setelah 3 detik
- Menggunakan Alpine.js dengan smooth fade-out animation
- User experience lebih baik tanpa perlu close manual

**Implementasi:**
- File: `resources/views/livewire/database-backup-manager.blade.php`
- Teknologi: Alpine.js `x-data`, `x-show`, `x-init`, `x-transition`

**Cara Kerja:**
```javascript
x-init="setTimeout(() => show = false, 3000)"
```

---

### 2️⃣ **Auto-cleanup Backup Lokal (> 10 hari)**

**Deskripsi:**
- Backup lokal yang lebih dari 10 hari otomatis dihapus
- Backup di Dropbox **TIDAK** dihapus (tetap tersimpan permanent)
- Hemat storage lokal server
- Cleanup dilakukan setiap kali backup baru dibuat

**Implementasi:**
- Method: `DatabaseBackupManager::cleanupOldBackups()`
- Trigger: Otomatis setelah `performBackup()` selesai
- Log: Tercatat di `storage/logs/laravel.log`

**Konfigurasi:**
```php
$cutoffDate = now()->subDays(10); // 10 hari
```

**Untuk mengubah retention period:**
Edit file: `app/Livewire/DatabaseBackupManager.php`
```php
// Line ~318
$cutoffDate = now()->subDays(30); // Ubah ke 30 hari
```

---

### 3️⃣ **Scheduled Auto-backup (Cron Job - 21:30)**

**Deskripsi:**
- Backup database otomatis setiap hari jam 21:30 (9:30 PM)
- Timezone: Asia/Jakarta
- Include: Export → Compress → Upload Dropbox → Cleanup old backups
- Logging: Success/failure tercatat di log

**Implementasi:**
- Command: `php artisan backup:auto`
- Schedule: `bootstrap/app.php` → `withSchedule()`
- Timezone: Asia/Jakarta

**Cara Kerja:**
```php
$schedule->command('backup:auto')
    ->dailyAt('21:30')
    ->timezone('Asia/Jakarta')
```

---

## 📋 **Setup Cron Job di Server**

### **Untuk Docker (Recommended):**

Tambahkan service cron di `docker-compose.yml`:

```yaml
services:
  # ... existing services ...
  
  scheduler:
    build:
      context: .
      dockerfile: .docker/Dockerfile
    container_name: muazara-scheduler
    restart: unless-stopped
    working_dir: /app
    volumes:
      - ./:/app
      - vendor_data:/app/vendor
    command: sh -c "while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done"
    depends_on:
      - db
    env_file:
      - ./.env
```

Lalu restart:
```bash
docker compose up -d
```

### **Alternatif: Manual Cron (Host Server):**

Edit crontab:
```bash
crontab -e
```

Tambahkan:
```cron
* * * * * cd /path/to/apoteku && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 **Testing**

### **1. Test Notifikasi Auto-hide:**
1. Buka halaman "Manajemen Backup"
2. Klik "Buat Backup Baru"
3. Notifikasi sukses akan muncul dan hilang setelah 3 detik

### **2. Test Auto-cleanup:**
```bash
# Buat backup dummy yang lama
docker compose exec app php artisan tinker
>>> Storage::disk('local')->put('db-backups/old_backup.sql.gz', 'test');
>>> touch(storage_path('app/db-backups/old_backup.sql.gz'), time() - (11 * 24 * 60 * 60));
>>> exit

# Buat backup baru (akan trigger cleanup)
docker compose exec app php artisan backup:auto

# Cek log
docker compose exec app tail -20 storage/logs/laravel.log
```

### **3. Test Scheduled Backup:**
```bash
# Lihat schedule list
docker compose exec app php artisan schedule:list

# Test manual run
docker compose exec app php artisan backup:auto

# Test schedule work (simulate)
docker compose exec app php artisan schedule:work
```

---

## 📊 **Monitoring & Logs**

### **Cek Log Backup:**
```bash
docker compose exec app tail -f storage/logs/laravel.log | grep -i backup
```

### **Cek Backup Files:**
```bash
# Local
docker compose exec app ls -lh storage/app/db-backups/

# Dropbox
docker compose exec app php artisan dropbox:list /backups
```

### **Cek Schedule Status:**
```bash
docker compose exec app php artisan schedule:list
```

---

## 🎯 **Summary Fitur**

| Fitur | Status | Keterangan |
|-------|--------|------------|
| **Auto-hide Notifikasi** | ✅ | 3 detik, smooth fade-out |
| **Auto-cleanup Lokal** | ✅ | > 10 hari, Dropbox tetap |
| **Scheduled Backup** | ✅ | Daily 21:30 WIB |
| **Compression GZIP** | ✅ | 70-90% size reduction |
| **Dropbox Upload** | ✅ | Automatic cloud backup |
| **Error Logging** | ✅ | Comprehensive logs |

---

## 🔧 **Konfigurasi Lanjutan**

### **Ubah Waktu Backup:**
Edit `bootstrap/app.php`:
```php
$schedule->command('backup:auto')
    ->dailyAt('02:00') // Ubah ke jam 2 pagi
```

### **Ubah Retention Period:**
Edit `app/Livewire/DatabaseBackupManager.php`:
```php
$cutoffDate = now()->subDays(30); // Ubah ke 30 hari
```

### **Disable Auto-cleanup:**
Comment line di `performBackup()`:
```php
// $this->cleanupOldBackups();
```

### **Multiple Backup Schedule:**
```php
// Backup 2x sehari
$schedule->command('backup:auto')->dailyAt('09:00');
$schedule->command('backup:auto')->dailyAt('21:30');
```

---

## 📞 **Troubleshooting**

### **Schedule tidak jalan:**
1. Pastikan cron service running
2. Cek timezone: `docker compose exec app php -r "echo date_default_timezone_get();"`
3. Test manual: `docker compose exec app php artisan schedule:run`

### **Cleanup tidak bekerja:**
1. Cek log: `storage/logs/laravel.log`
2. Verify file permissions
3. Test manual cleanup

### **Notifikasi tidak hilang:**
1. Pastikan Alpine.js ter-load (cek browser console)
2. Clear browser cache
3. Cek network tab untuk errors

---

## 📝 **Changelog**

**Version 1.1.0** - 2025-12-08
- ✅ Added auto-hide notifications (3s timeout)
- ✅ Added auto-cleanup local backups (>10 days)
- ✅ Added scheduled auto-backup (daily 21:30)
- ✅ Improved logging and error handling
- ✅ Enhanced user experience

---

**Semua fitur sudah berfungsi dengan baik!** 🎉
