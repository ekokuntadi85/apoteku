# Dropbox Integration untuk Database Backup

## 📋 Overview

Fitur ini memungkinkan backup database otomatis diupload ke Dropbox dengan kompresi GZIP untuk menghemat storage dan bandwidth.

## ✨ Fitur

- ✅ **Auto-upload ke Dropbox** - Backup otomatis diupload setelah dibuat
- ✅ **Kompresi GZIP** - Mengurangi ukuran file 70-90%
- ✅ **Progress Indicator** - Real-time feedback saat backup
- ✅ **Test Connection** - Verifikasi koneksi Dropbox
- ✅ **Toggle Enable/Disable** - Kontrol mudah via UI

## 🔧 Setup Dropbox

### 1. Buat Dropbox App

1. Kunjungi [Dropbox Developer Console](https://www.dropbox.com/developers/apps)
2. Klik **"Create App"**
3. Pilih **"Scoped access"**
4. Pilih **"Full Dropbox"** atau **"App folder"** (recommended)
5. Beri nama aplikasi (contoh: `Apoteku-Backup`)
6. Klik **"Create App"**

### 2. Generate Access Token

1. Di halaman app settings, scroll ke **"OAuth 2"**
2. Di bagian **"Generated access token"**, klik **"Generate"**
3. Copy token yang dihasilkan (hanya ditampilkan sekali!)

### 3. Set Permissions

Di tab **"Permissions"**, aktifkan:
- ✅ `files.metadata.write`
- ✅ `files.metadata.read`
- ✅ `files.content.write`
- ✅ `files.content.read`

Klik **"Submit"** untuk menyimpan.

## ⚙️ Konfigurasi Laravel

### 1. Update `.env`

Tambahkan konfigurasi berikut:

```env
# Dropbox Configuration for Database Backup
DROPBOX_ENABLED=true
DROPBOX_ACCESS_TOKEN=your_dropbox_access_token_here
```

### 2. Restart Container (jika menggunakan Docker)

```bash
docker compose restart app
```

### 3. Clear Cache

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

## 📦 Package yang Digunakan

- `spatie/flysystem-dropbox` (v3.0.2)
- `spatie/dropbox-api` (v1.23.0)

## 🚀 Cara Menggunakan

### Via UI (Recommended)

1. Login ke aplikasi
2. Buka menu **"Manajemen Backup"**
3. Di panel **"Pengaturan Dropbox"**:
   - Toggle **"Auto-upload ke Dropbox"** ke ON
   - Toggle **"Kompresi GZIP"** ke ON (recommended)
   - Masukkan **Dropbox Access Token**
   - Klik **"Test Koneksi"** untuk verifikasi
   - Klik **"Simpan Pengaturan"**
4. Klik **"Buat Backup Baru"**
5. Tunggu proses selesai (akan ada progress indicator)

### Via Command Line

```bash
# Backup database (akan auto-upload jika DROPBOX_ENABLED=true)
docker compose exec app php artisan db:backup
```

## 📁 Struktur File di Dropbox

Backup akan disimpan di:
```
/backups/
  ├── backup_2025-12-08_143052.sql.gz
  ├── backup_2025-12-08_150230.sql.gz
  └── backup_2025-12-09_091545.sql.gz
```

## 🔍 Troubleshooting

### Error: "Dropbox upload failed"

**Solusi:**
1. Pastikan Access Token valid
2. Cek permissions di Dropbox App
3. Pastikan koneksi internet stabil
4. Test koneksi via UI

### Error: "Cannot create compressed file"

**Solusi:**
1. Cek permission folder `storage/app/db-backups`
2. Pastikan disk space cukup
3. Cek log: `storage/logs/laravel.log`

### Backup tidak muncul di Dropbox

**Solusi:**
1. Pastikan `DROPBOX_ENABLED=true` di `.env`
2. Restart container setelah update `.env`
3. Clear cache Laravel
4. Cek log error

## 📊 Estimasi Kompresi

| Original Size | Compressed Size | Saving |
|--------------|-----------------|--------|
| 10 MB        | 1-2 MB         | 80-90% |
| 50 MB        | 5-10 MB        | 80-90% |
| 100 MB       | 10-20 MB       | 80-90% |

## 🔐 Keamanan

- ✅ Access token disimpan di `.env` (tidak di-commit ke git)
- ✅ Token di-mask di UI (type password)
- ✅ Koneksi menggunakan HTTPS
- ✅ File backup ter-enkripsi saat transit

## 📝 Catatan

- Dropbox free account memiliki limit 2GB storage
- Backup lama tidak otomatis dihapus dari Dropbox
- Recommended: Setup cron job untuk cleanup backup lama
- File `.gz` dapat di-extract dengan tools standar (7zip, WinRAR, gunzip, dll)

## 🔄 Update/Maintenance

### Update Access Token

1. Generate token baru di Dropbox Developer Console
2. Update `DROPBOX_ACCESS_TOKEN` di `.env`
3. Restart container
4. Test koneksi via UI

### Disable Dropbox Backup

1. Set `DROPBOX_ENABLED=false` di `.env`, atau
2. Toggle OFF via UI

## 📚 Referensi

- [Spatie Flysystem Dropbox Documentation](https://github.com/spatie/flysystem-dropbox)
- [Dropbox API Documentation](https://www.dropbox.com/developers/documentation)
- [Laravel Filesystem Documentation](https://laravel.com/docs/filesystem)

## 🎯 Fitur Mendatang

- [ ] Auto-cleanup backup lama (> 30 hari)
- [ ] Download backup dari Dropbox
- [ ] Restore dari Dropbox
- [ ] Multiple cloud storage (Google Drive, OneDrive)
- [ ] Scheduled backup otomatis
