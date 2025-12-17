# Quick Fix untuk Mesin yang Sudah Restore

## Langkah-langkah:

### 1. Pull Code Terbaru
```bash
cd /path/to/apoteku
git pull origin feature/new
```

### 2. Clear Cache
```bash
docker compose exec app php artisan optimize:clear
```

### 3. Jalankan Sync Manual
```bash
docker compose exec app php artisan finance:sync-historical-journals
```

**Perhatikan output:**
- Harus ada progress bar untuk Purchases, Transactions, Expenses
- Jika ada warning "Using fallback COGS" → Normal untuk data lama
- Jika ada warning "Skipping COGS (COGS = 0)" → Perlu update product purchase_price

### 4. Verifikasi Hasil

#### Via Web:
1. Buka **Buku Besar**
2. Pilih akun **101 - Kas**
3. Harus ada transaksi penjualan & pembelian

#### Via Terminal:
```bash
docker compose exec app php artisan tinker
>>> \App\Models\JournalEntry::count()
>>> \App\Models\Transaction::count()
>>> \App\Models\Purchase::count()
```

**Expected:** 
- Journal Entries ≈ (Transactions × 2) + (Purchases × 2) + Expenses
- Contoh: 100 transaksi + 50 pembelian = ~300 journal entries

### 5. Jika Masih Kosong

#### Cek Accounts:
```bash
docker compose exec app php artisan tinker
>>> \App\Models\Account::count()
```

Jika 0, seed accounts:
```bash
docker compose exec app php artisan db:seed --class=AccountSeeder
```

Lalu ulangi langkah 3.

### 6. Troubleshooting Lanjutan

Lihat file: `docs/TROUBLESHOOTING_RESTORE.md`
