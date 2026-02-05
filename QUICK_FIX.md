# 🚀 QUICK FIX - Error "Column 'notes' not found"

**Error:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'notes' in 'INSERT INTO'
```

---

## ✅ **SOLUSI CEPAT (1 Perintah)**

Di mesin yang error, jalankan:

```bash
php artisan migrate
```

Atau jika pakai Docker:

```bash
docker compose exec app php artisan migrate
```

**Selesai!** ✅

---

## 📋 **Penjelasan**

Migration terbaru akan **menambahkan kolom `notes`** ke tabel `purchase_order_details`.

Migration ini **AMAN** karena:
- ✅ Cek dulu apakah kolom sudah ada
- ✅ Hanya tambah jika belum ada
- ✅ Tidak akan error jika sudah ada
- ✅ Tidak menghapus data

---

## 🔍 **Verifikasi**

Setelah migrate, cek apakah berhasil:

```bash
# Cek status migration
php artisan migrate:status | grep notes
```

Output harus:
```
✓ 2025_12_07_220826_add_notes_to_purchase_order_details_table ... Ran
```

---

## 🧪 **Test**

1. Refresh browser (Ctrl+Shift+R)
2. Buat Surat Pesanan baru
3. Tambah item dengan keterangan
4. Save
5. ✅ Tidak ada error!

---

## 🆘 **Jika Masih Error**

### **Option 1: Manual Add Column**

```sql
-- Masuk ke MySQL
mysql -u root -p nama_database

-- Tambah kolom manual
ALTER TABLE purchase_order_details 
ADD COLUMN notes TEXT NULL 
AFTER estimated_price;
```

### **Option 2: Check Migration Status**

```bash
# Lihat migration yang pending
php artisan migrate:status

# Jalankan migration yang pending
php artisan migrate --force
```

---

## 📝 **Full Deployment Steps**

Untuk deployment lengkap ke mesin baru:

```bash
# 1. Pull code terbaru
git pull origin main

# 2. Install dependencies (jika perlu)
composer install --no-dev
npm install
npm run build

# 3. Run migration (CRITICAL!)
php artisan migrate

# 4. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 5. Storage link (jika belum)
php artisan storage:link
```

---

## ✅ **Checklist**

- [ ] Pull latest code
- [ ] Run `php artisan migrate`
- [ ] Test create Purchase Order
- [ ] Verify no errors

---

**Migration File:**
`database/migrations/2025_12_07_220826_add_notes_to_purchase_order_details_table.php`

**Status:** ✅ Safe to run on any environment
