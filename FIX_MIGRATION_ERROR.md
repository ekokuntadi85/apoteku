# 🔧 Fix Migration Error - "Table already exists"

**Error:**
```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'transaction_detail_batches' already exists
```

**Penyebab:**
Tabel sudah ada di database, tapi migration record-nya tidak tercatat di tabel `migrations`.

---

## ✅ **SOLUSI 1: Fix Tanpa Kehilangan Data (RECOMMENDED)**

### **Step 1: Tandai Migration Sebagai Sudah Dijalankan**

Jalankan perintah SQL ini di mesin yang error:

```bash
# Masuk ke MySQL
docker compose exec mysql mysql -u root -p apoteku
# Password: root (atau sesuai .env)
```

Kemudian jalankan SQL:

```sql
-- Tandai migration transaction_detail_batches sebagai sudah dijalankan
INSERT INTO migrations (migration, batch) 
VALUES ('2025_08_11_120000_create_transaction_detail_batches_table', 2);

-- Tandai migration kartu_monitoring_suhus sebagai sudah dijalankan (jika tabelnya sudah ada)
INSERT INTO migrations (migration, batch) 
VALUES ('2025_08_17_195259_create_kartu_monitoring_suhus_table', 2);

-- Tandai migration settings sebagai sudah dijalankan (jika tabelnya sudah ada)
INSERT INTO migrations (migration, batch) 
VALUES ('2025_11_14_111630_create_settings_table', 2);

-- Exit
EXIT;
```

### **Step 2: Jalankan Migration Lagi**

```bash
docker compose exec app php artisan migrate
```

Sekarang hanya migration Purchase Order yang akan dijalankan:
- ✅ `create_purchase_orders_tables`
- ✅ `add_type_to_purchase_orders_and_details_to_products`
- ✅ `add_substance_fields_to_purchase_order_details`
- ✅ `add_notes_to_purchase_order_details_table`

---

## 🔄 **SOLUSI 2: Alternatif dengan Artisan Command**

Buat file PHP sementara untuk menandai migration:

```bash
# Di mesin yang error, buat file fix-migration.php
cat > fix-migration.php << 'EOF'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::table('migrations')->insert([
    ['migration' => '2025_08_11_120000_create_transaction_detail_batches_table', 'batch' => 2],
    ['migration' => '2025_08_17_195259_create_kartu_monitoring_suhus_table', 'batch' => 2],
    ['migration' => '2025_11_14_111630_create_settings_table', 'batch' => 2],
]);

echo "Migrations marked as ran!\n";
EOF

# Jalankan
docker compose exec app php fix-migration.php

# Hapus file
rm fix-migration.php

# Jalankan migrate
docker compose exec app php artisan migrate
```

---

## 🗄️ **SOLUSI 3: Reset Database (HANYA JIKA TIDAK ADA DATA PENTING)**

⚠️ **WARNING: Ini akan MENGHAPUS SEMUA DATA!**

```bash
# Backup dulu (PENTING!)
docker compose exec mysql mysqldump -u root -proot apoteku > backup_$(date +%Y%m%d_%H%M%S).sql

# Reset database
docker compose exec app php artisan migrate:fresh

# Atau jika perlu seeding
docker compose exec app php artisan migrate:fresh --seed
```

---

## 🔍 **Verifikasi Tabel yang Sudah Ada**

Cek tabel mana yang sudah ada di database:

```bash
docker compose exec mysql mysql -u root -proot apoteku -e "SHOW TABLES;"
```

Cek migration yang tercatat:

```bash
docker compose exec app php artisan migrate:status
```

---

## 📋 **Checklist Troubleshooting**

1. **Cek apakah tabel benar-benar ada:**
   ```sql
   SHOW TABLES LIKE 'transaction_detail_batches';
   ```

2. **Cek apakah migration tercatat:**
   ```sql
   SELECT * FROM migrations WHERE migration LIKE '%transaction_detail_batches%';
   ```

3. **Jika tabel ada tapi migration tidak tercatat:**
   - ✅ Gunakan Solusi 1 (tandai sebagai ran)

4. **Jika tabel tidak ada:**
   - ✅ Hapus record migration yang salah
   - ✅ Jalankan migrate ulang

---

## 🎯 **Recommended Flow**

```bash
# 1. Pull code terbaru
git pull origin main

# 2. Tandai migration yang tabelnya sudah ada
docker compose exec mysql mysql -u root -proot apoteku << EOF
INSERT IGNORE INTO migrations (migration, batch) VALUES 
('2025_08_11_120000_create_transaction_detail_batches_table', 2),
('2025_08_17_195259_create_kartu_monitoring_suhus_table', 2),
('2025_11_14_111630_create_settings_table', 2);
EOF

# 3. Jalankan migration
docker compose exec app php artisan migrate

# 4. Verify
docker compose exec app php artisan migrate:status
```

---

## ✅ **Expected Result**

Setelah fix, `migrate:status` harus menunjukkan:

```
✓ 2025_08_11_120000_create_transaction_detail_batches_table ... Ran
✓ 2025_08_17_195259_create_kartu_monitoring_suhus_table ....... Ran
✓ 2025_11_14_111630_create_settings_table ..................... Ran
✓ 2025_11_24_000000_create_purchase_orders_tables ............. Ran
✓ 2025_11_24_000001_add_type_to_purchase_orders ............... Ran
✓ 2025_11_24_000002_add_substance_fields ...................... Ran
✓ 2025_12_07_220826_add_notes_to_purchase_order_details ....... Ran
```

---

**Rekomendasi: Gunakan Solusi 1 untuk menghindari kehilangan data!**
