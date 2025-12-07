# 🚀 Deployment Guide - Update dari Main Branch

**Issue:** Error "Column 'notes' not found" saat membuat Surat Pesanan  
**Cause:** Database di mesin lain belum di-migrate  
**Solution:** Jalankan migration

---

## ⚠️ **PENTING - BACA DULU!**

Perubahan terbaru di branch `main` membutuhkan **UPDATE DATABASE SCHEMA**.

Kolom `notes` dipindahkan dari tabel `purchase_orders` ke `purchase_order_details`.

---

## 📋 **Langkah Deployment ke Mesin Lain**

### **Step 1: Pull Latest Code**

```bash
# Pastikan di branch main
git checkout main

# Pull perubahan terbaru
git pull origin main
```

### **Step 2: Install/Update Dependencies**

```bash
# Update Composer dependencies (jika ada perubahan)
composer install --no-dev --optimize-autoloader

# Update NPM dependencies (jika ada perubahan)
npm install

# Rebuild CSS/JS
npm run build
```

### **Step 3: 🔴 CRITICAL - Run Migrations**

**PERHATIAN:** Ini akan mengubah struktur database!

```bash
# Jalankan migrasi
php artisan migrate
```

**Expected Output:**
```
INFO  Running migrations.

2025_11_24_000000_create_purchase_orders_tables .............. DONE
2025_11_24_000001_add_type_to_purchase_orders_and_details_to_products DONE
2025_11_24_000002_add_substance_fields_to_purchase_order_details DONE
```

### **Step 4: Clear Cache**

```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Rebuild config cache (production only)
php artisan config:cache
php artisan route:cache
```

### **Step 5: Storage Link (jika belum)**

```bash
# Untuk favicon dan logo dinamis
php artisan storage:link
```

### **Step 6: Test**

1. Buka aplikasi di browser
2. Hard refresh (Ctrl+Shift+R)
3. Test buat Surat Pesanan baru
4. ✅ Seharusnya tidak ada error lagi!

---

## 🔍 **Troubleshooting**

### **Error: "Column 'notes' not found"**

**Cause:** Migration belum dijalankan  
**Fix:**
```bash
php artisan migrate
```

### **Error: "Migration already ran"**

**Cause:** Migration sudah pernah dijalankan tapi gagal  
**Fix:**
```bash
# Cek status migration
php artisan migrate:status

# Jika ada yang pending, jalankan lagi
php artisan migrate
```

### **Error: "Nothing to migrate"**

**Cause:** Database sudah up-to-date  
**Action:** Cek manual apakah kolom `notes` ada di tabel `purchase_order_details`

```sql
-- Jalankan di MySQL
DESCRIBE purchase_order_details;
```

Expected output harus ada kolom `notes`:
```
+-------------------+---------------+------+-----+---------+
| Field             | Type          | Null | Key | Default |
+-------------------+---------------+------+-----+---------+
| ...               | ...           | ...  | ... | ...     |
| notes             | text          | YES  |     | NULL    |
| ...               | ...           | ...  | ... | ...     |
+-------------------+---------------+------+-----+---------+
```

---

## 🗄️ **Database Changes Detail**

### **Migration File:**
`database/migrations/2025_11_24_000000_create_purchase_orders_tables.php`

### **Changes:**

#### **Tabel `purchase_orders`:**
```sql
-- REMOVED:
-- notes TEXT NULL

-- Kolom notes DIHAPUS dari tabel ini
```

#### **Tabel `purchase_order_details`:**
```sql
-- ADDED:
notes TEXT NULL

-- Kolom notes DITAMBAHKAN ke tabel ini
```

### **Why?**

Sebelumnya: 1 notes untuk seluruh purchase order  
Sekarang: 1 notes untuk setiap item (lebih fleksibel)

**Contoh:**
```
Purchase Order #SP-001
├─ Item 1: Paracetamol - notes: "Minta ED panjang"
├─ Item 2: Amoxicillin - notes: "Bonus 1 box"
└─ Item 3: Vitamin C - notes: ""
```

---

## 🐳 **Untuk Docker Environment**

Jika menggunakan Docker:

```bash
# Pull latest code
git pull origin main

# Rebuild container (jika ada perubahan Dockerfile)
docker compose build

# Restart container
docker compose down
docker compose up -d

# Jalankan migration di dalam container
docker compose exec app php artisan migrate

# Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear

# Rebuild assets
docker compose exec app npm run build

# Storage link
docker compose exec app php artisan storage:link
```

---

## 📝 **Checklist Deployment**

Gunakan checklist ini untuk memastikan deployment sukses:

- [ ] Pull latest code dari main
- [ ] Composer install
- [ ] NPM install & build
- [ ] **Run php artisan migrate** ⚠️ CRITICAL
- [ ] Clear all caches
- [ ] Storage link
- [ ] Test create Purchase Order
- [ ] Test dark mode toggle
- [ ] Test upload logo (favicon)
- [ ] Hard refresh browser

---

## 🔄 **Rollback Plan (Jika Ada Masalah)**

Jika setelah deployment ada masalah:

### **Option 1: Rollback Migration**

```bash
# Rollback 3 migration terakhir (purchase order migrations)
php artisan migrate:rollback --step=3
```

⚠️ **WARNING:** Ini akan **MENGHAPUS** semua data Purchase Order yang sudah dibuat!

### **Option 2: Rollback Code**

```bash
# Kembali ke commit sebelumnya
git log --oneline -5  # Lihat commit history
git reset --hard <commit-hash-sebelumnya>
```

### **Option 3: Manual Fix**

Jika hanya kolom `notes` yang bermasalah:

```sql
-- Tambah kolom notes manual
ALTER TABLE purchase_order_details 
ADD COLUMN notes TEXT NULL 
AFTER estimated_price;
```

---

## 📊 **Verification**

Setelah deployment, verifikasi dengan:

### **1. Database Check**

```bash
# Masuk ke MySQL
mysql -u root -p

# Gunakan database
USE nama_database;

# Cek struktur tabel
DESCRIBE purchase_order_details;

# Harus ada kolom 'notes'
```

### **2. Application Check**

```bash
# Cek migration status
php artisan migrate:status

# Semua harus 'Ran'
```

### **3. Functional Check**

1. Buka aplikasi
2. Buat Purchase Order baru
3. Tambah item dengan notes
4. Save
5. ✅ Tidak ada error

---

## 🆘 **Need Help?**

Jika masih ada error setelah mengikuti panduan ini:

1. **Cek log error:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Cek migration status:**
   ```bash
   php artisan migrate:status
   ```

3. **Cek database structure:**
   ```sql
   SHOW CREATE TABLE purchase_order_details;
   ```

4. **Screenshot error** dan konsultasi dengan team

---

## ✅ **Success Indicators**

Deployment berhasil jika:

- ✅ `php artisan migrate` selesai tanpa error
- ✅ Kolom `notes` ada di `purchase_order_details`
- ✅ Bisa create Purchase Order tanpa error
- ✅ Dark mode toggle berfungsi
- ✅ Favicon dinamis berfungsi

---

**Last Updated:** 7 Desember 2025  
**Branch:** main  
**Critical:** YES - Requires database migration
