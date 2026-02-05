# Dropbox Backup Fix - Quick Guide

## Problem
Backup berhasil lokal tapi gagal upload ke Dropbox dengan error:
```
cURL error 7: Failed to connect to api.dropbox.com
```

## Root Cause
**IPv6 connectivity timeout** - sistem mencoba IPv6 dulu, gagal, baru fallback ke IPv4.

---

## Solution

### Untuk Mesin dengan Docker (Sudah Fixed ✅)
Sudah diperbaiki di `docker-compose.yml` dengan menambahkan:
```yaml
extra_hosts:
  - "api.dropbox.com:162.125.81.19"
```

### Untuk Mesin Fedora Non-Docker

#### Option 1: Automated Fix (Recommended)
Copy dan jalankan script ini di mesin Fedora:

```bash
# Copy file ini ke mesin Fedora
scp fix_dropbox_fedora.sh user@fedora-server:/path/to/app/

# Di mesin Fedora, jalankan:
cd /path/to/app
chmod +x fix_dropbox_fedora.sh
./fix_dropbox_fedora.sh
```

Script akan:
- ✅ Detect masalah IPv6
- ✅ Disable IPv6 (temporary & permanent)
- ✅ Check firewall
- ✅ Test PHP cURL
- ✅ Clear Laravel cache
- ✅ Verify fix

#### Option 2: Manual Fix

**Step 1: Disable IPv6**
```bash
# Temporary
sudo sysctl -w net.ipv6.conf.all.disable_ipv6=1
sudo sysctl -w net.ipv6.conf.default.disable_ipv6=1

# Permanent
echo "net.ipv6.conf.all.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf
echo "net.ipv6.conf.default.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

**Step 2: Allow HTTPS in Firewall**
```bash
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

**Step 3: Clear Laravel Cache**
```bash
php artisan config:clear
php artisan cache:clear
```

**Step 4: Test**
```bash
curl -v https://api.dropbox.com/
# Should show: "Trying 162.125.81.19:443..." (IPv4 only)
```

---

## Verification

### Test dari Command Line
```bash
curl -I https://api.dropbox.com/
# Expected: HTTP/2 200 or 404 (connection successful)
```

### Test dari Laravel
```bash
php artisan tinker
>>> Storage::disk('dropbox')->directories('/');
# Expected: array of directories
```

### Test Backup
Buat backup dari web interface - seharusnya muncul:
```
✅ Backup berhasil dibuat dan diupload ke Dropbox.
```

---

## Troubleshooting

### Masih Gagal Setelah Fix?

**Check 1: Verify IPv6 Disabled**
```bash
sysctl net.ipv6.conf.all.disable_ipv6
# Expected: net.ipv6.conf.all.disable_ipv6 = 1
```

**Check 2: Test Connection**
```bash
curl -v https://api.dropbox.com/ 2>&1 | grep "Trying"
# Should NOT show IPv6 addresses (format: xxxx:xxxx:xxxx)
# Should show IPv4: Trying 162.125.81.19:443...
```

**Check 3: SELinux**
```bash
sudo ausearch -m avc -ts recent | grep curl
# If blocking, create policy or set to permissive
```

**Check 4: PHP cURL**
```bash
php -m | grep curl
# Should show: curl
```

**Check 5: Dropbox Credentials**
```bash
php artisan tinker
>>> config('filesystems.disks.dropbox.authorization_token')
# Should show your token
```

---

## Files Reference

- [`fix_dropbox_fedora.sh`](fix_dropbox_fedora.sh) - Automated fix script
- [`DROPBOX_FIX_NON_DOCKER.md`](DROPBOX_FIX_NON_DOCKER.md) - Detailed troubleshooting
- [`diagnose_dropbox.sh`](diagnose_dropbox.sh) - Diagnostic script

---

## Support

Jika masih bermasalah setelah menjalankan fix:
1. Jalankan `./diagnose_dropbox.sh` dan share outputnya
2. Check logs: `tail -100 storage/logs/laravel.log`
3. Test manual: `curl -v https://api.dropbox.com/oauth2/token`
