# 🚨 URGENT FIX - Column 'notes' not found AFTER migrate:fresh

**Error:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'notes'
```

**Terjadi SETELAH:** `php artisan migrate:fresh`

---

## 🔍 **Root Cause**

Migration file di mesin lain **TIDAK UP TO DATE**!

File `2025_11_24_000000_create_purchase_orders_tables.php` versi lama tidak memiliki kolom `notes`.

---

## ✅ **SOLUSI LENGKAP**

### **Step 1: PASTIKAN Code Terbaru**

```bash
# Di mesin yang error
cd ~/apoteku  # atau path project Anda

# Stash perubahan lokal (jika ada)
git stash

# Pull code terbaru
git pull origin main

# Cek apakah sudah terbaru
git log --oneline -1
# Harus menunjukkan commit terbaru dari main
```

### **Step 2: Clear Composer Cache**

```bash
# Clear composer cache
docker compose exec app composer clear-cache

# Dump autoload ulang
docker compose exec app composer dump-autoload
```

### **Step 3: Verify Migration File**

```bash
# Cek isi migration file
cat database/migrations/2025_11_24_000000_create_purchase_orders_tables.php | grep notes
```

**Expected output:**
```php
$table->text('notes')->nullable();
```

Jika **TIDAK ADA**, berarti code belum up to date!

### **Step 4: Reset Database Ulang**

```bash
# Setelah yakin code sudah terbaru
docker compose exec app php artisan migrate:fresh

# Atau dengan seed (jika ada)
docker compose exec app php artisan migrate:fresh --seed
```

### **Step 5: Test**

```bash
# Test create purchase order
# Seharusnya tidak ada error lagi
```

---

## 🔧 **TROUBLESHOOTING**

### **Issue 1: Git pull tidak mengupdate file**

```bash
# Force pull
git fetch origin main
git reset --hard origin/main

# Verify
git log --oneline -5
```

### **Issue 2: Migration file masih versi lama**

```bash
# Cek file migration secara manual
cat database/migrations/2025_11_24_000000_create_purchase_orders_tables.php

# Harus ada baris ini:
# $table->text('notes')->nullable();
```

Jika tidak ada, **MANUAL EDIT** file tersebut:

```php
// Tambahkan setelah baris estimated_price
$table->text('notes')->nullable();
```

### **Issue 3: Docker volume cache**

```bash
# Rebuild container
docker compose down
docker compose build --no-cache
docker compose up -d

# Migrate ulang
docker compose exec app php artisan migrate:fresh
```

---

## 📋 **VERIFICATION CHECKLIST**

Sebelum migrate:fresh, pastikan:

- [ ] `git pull origin main` berhasil
- [ ] `git log` menunjukkan commit terbaru
- [ ] File migration ada kolom `notes`:
  ```bash
  grep -n "notes" database/migrations/2025_11_24_000000_create_purchase_orders_tables.php
  ```
- [ ] Composer autoload di-refresh
- [ ] Docker container up to date

---

## 🎯 **COMPLETE RESET PROCEDURE**

Jika masih error, lakukan full reset:

```bash
# 1. Pull code terbaru
git fetch origin main
git reset --hard origin/main

# 2. Rebuild container
docker compose down -v  # -v untuk hapus volume
docker compose build --no-cache
docker compose up -d

# 3. Install dependencies
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app npm run build

# 4. Reset database
docker compose exec app php artisan migrate:fresh

# 5. Storage link
docker compose exec app php artisan storage:link

# 6. Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```

---

## 🔍 **DEBUG: Cek Versi Migration**

Jalankan ini untuk melihat isi migration:

```bash
docker compose exec app cat database/migrations/2025_11_24_000000_create_purchase_orders_tables.php
```

**Expected (BENAR):**
```php
Schema::create('purchase_order_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->foreignId('product_unit_id')->constrained('product_units');
    $table->integer('quantity');
    $table->decimal('estimated_price', 15, 2)->nullable();
    $table->text('notes')->nullable();  // ← HARUS ADA INI!
    $table->timestamps();
});
```

**Wrong (SALAH):**
```php
// Jika tidak ada baris:
$table->text('notes')->nullable();
// Berarti file migration BELUM UPDATE!
```

---

## 💡 **Quick Check Command**

```bash
# One-liner untuk cek apakah migration sudah benar
docker compose exec app grep -c "notes" database/migrations/2025_11_24_000000_create_purchase_orders_tables.php

# Output harus: 1 (atau lebih)
# Jika output: 0 → Migration BELUM UPDATE!
```

---

## 🆘 **LAST RESORT: Manual Fix**

Jika semua cara di atas gagal, edit manual:

```bash
# Edit file migration
nano database/migrations/2025_11_24_000000_create_purchase_orders_tables.php

# Tambahkan baris ini di dalam Schema::create('purchase_order_details'):
$table->text('notes')->nullable();

# Save (Ctrl+O, Enter, Ctrl+X)

# Migrate ulang
docker compose exec app php artisan migrate:fresh
```

---

## ✅ **SUCCESS INDICATORS**

Migration berhasil jika:

1. ✅ `git log` menunjukkan commit terbaru
2. ✅ Migration file ada kolom `notes`
3. ✅ `migrate:fresh` selesai tanpa error
4. ✅ Bisa create purchase order tanpa error
5. ✅ Database ada kolom `notes`:
   ```bash
   docker compose exec mysql mysql -u root -proot apoteku -e "DESCRIBE purchase_order_details;"
   ```

---

**PENTING:** Masalah ini 99% karena code di mesin lain BELUM UP TO DATE!

**Solusi:** `git pull origin main` dan pastikan file migration sudah ada kolom `notes`!
