# Dropbox Token Management - Long-Lived Solution

## 🔴 Masalah: Access Token Expired

Error: `expired_access_token`

**Penyebab:**
- Dropbox "Generated access token" hanya berlaku 4 jam
- Setelah itu akan expired dan perlu generate ulang
- Tidak cocok untuk production/automated backup

## ✅ Solusi: 3 Pilihan

### **Option 1: Generate Token Baru (Quick Fix)**

**Kelebihan:**
- ✅ Cepat dan mudah
- ✅ Langsung bisa digunakan

**Kekurangan:**
- ❌ Expired dalam 4 jam
- ❌ Perlu generate ulang manual
- ❌ Tidak cocok untuk scheduled backup

**Cara:**
1. Buka https://www.dropbox.com/developers/apps
2. Pilih app Anda
3. Tab "Settings" → "OAuth 2"
4. Klik "Generate" di "Generated access token"
5. Copy token baru
6. Update `.env`:
   ```bash
   nano .env
   # Ganti DROPBOX_ACCESS_TOKEN dengan token baru
   ```
7. Restart:
   ```bash
   docker compose restart app
   ```

---

### **Option 2: Dropbox App Password (Recommended - No Expiry)**

**Kelebihan:**
- ✅ **Tidak pernah expired**
- ✅ Mudah setup
- ✅ Cocok untuk production

**Kekurangan:**
- ⚠️ Perlu enable 2FA di akun Dropbox

**Cara Setup:**

#### **Step 1: Enable 2FA di Dropbox**
1. Login ke https://www.dropbox.com
2. Klik avatar → Settings
3. Tab "Security"
4. Enable "Two-step verification"
5. Ikuti setup wizard (gunakan authenticator app)

#### **Step 2: Generate App Password**
1. Masih di Settings → Security
2. Scroll ke "App passwords"
3. Klik "Create app password"
4. Beri nama: "Apoteku Backup"
5. Copy password yang dihasilkan (format: `xxxx-xxxx-xxxx-xxxx`)

#### **Step 3: Update Konfigurasi**
```bash
nano .env
```

Ganti dengan app password:
```env
DROPBOX_ACCESS_TOKEN=xxxx-xxxx-xxxx-xxxx
DROPBOX_ENABLED=true
```

**CATATAN:** App password format berbeda dari access token, jadi perlu update driver.

---

### **Option 3: OAuth 2.0 Refresh Token (Advanced)**

**Kelebihan:**
- ✅ Token refresh otomatis
- ✅ Tidak pernah expired
- ✅ Paling aman

**Kekurangan:**
- ❌ Setup lebih kompleks
- ❌ Perlu OAuth flow implementation

**Implementasi:**

Perlu update Dropbox Service Provider untuk handle refresh token.

File: `app/Providers/DropboxServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\Dropbox\RefreshableTokenProvider;
use Spatie\FlysystemDropbox\DropboxAdapter;

class DropboxServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('dropbox', function ($app, $config) {
            // Check if using refresh token
            if (isset($config['refresh_token'])) {
                $tokenProvider = new RefreshableTokenProvider(
                    $config['app_key'],
                    $config['app_secret'],
                    $config['refresh_token']
                );
                
                $client = new DropboxClient($tokenProvider);
            } else {
                // Fallback to access token
                $client = new DropboxClient($config['authorization_token']);
            }

            $adapter = new DropboxAdapter($client);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
```

**Cara Mendapatkan Refresh Token:**

1. **Setup OAuth App:**
   - Buka https://www.dropbox.com/developers/apps
   - Pilih app → Settings
   - Di "OAuth 2" section:
     - Copy "App key"
     - Copy "App secret"
   - Di "Redirect URIs", tambahkan: `http://localhost/dropbox/callback`

2. **Generate Authorization Code:**
   
   Buka URL ini di browser (ganti `YOUR_APP_KEY`):
   ```
   https://www.dropbox.com/oauth2/authorize?client_id=YOUR_APP_KEY&response_type=code&token_access_type=offline
   ```
   
   Authorize app, lalu copy code dari URL redirect.

3. **Exchange Code untuk Refresh Token:**
   
   ```bash
   curl -X POST https://api.dropbox.com/oauth2/token \
     -d code=YOUR_AUTHORIZATION_CODE \
     -d grant_type=authorization_code \
     -u YOUR_APP_KEY:YOUR_APP_SECRET
   ```
   
   Response akan berisi `refresh_token`.

4. **Update `.env`:**
   ```env
   DROPBOX_APP_KEY=your_app_key
   DROPBOX_APP_SECRET=your_app_secret
   DROPBOX_REFRESH_TOKEN=your_refresh_token
   ```

5. **Update `config/filesystems.php`:**
   ```php
   'dropbox' => [
       'driver' => 'dropbox',
       'app_key' => env('DROPBOX_APP_KEY'),
       'app_secret' => env('DROPBOX_APP_SECRET'),
       'refresh_token' => env('DROPBOX_REFRESH_TOKEN'),
   ],
   ```

---

## 🎯 **Rekomendasi:**

### **Untuk Development/Testing:**
→ **Option 1** (Generate token baru setiap 4 jam)

### **Untuk Production:**
→ **Option 2** (App Password - paling mudah) atau
→ **Option 3** (Refresh Token - paling robust)

---

## 🔧 **Quick Fix Sekarang:**

Karena token sudah expired, lakukan ini dulu:

```bash
# 1. Generate token baru di Dropbox Developer Console
# 2. Update .env
nano .env
# Ganti DROPBOX_ACCESS_TOKEN dengan token baru

# 3. Restart
docker compose restart app

# 4. Test
docker compose exec app php artisan dropbox:test
```

---

## 📝 **Catatan Penting:**

- **Short-lived token** expired setelah 4 jam
- **App password** tidak pernah expired (butuh 2FA)
- **Refresh token** auto-refresh, tidak pernah expired
- Untuk scheduled backup (21:30), **HARUS** gunakan Option 2 atau 3

---

**Mana yang Anda pilih?**
1. Quick fix (generate token baru) - untuk test dulu
2. App Password - untuk production (recommended)
3. Refresh Token - untuk production (advanced)
