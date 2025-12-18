# Solusi untuk Mesin Non-Docker (Bare Metal / VM)

Jika mesin lain tidak menggunakan Docker, berikut solusi untuk masalah koneksi Dropbox:

## Diagnostic Steps

### 1. Test Basic Connectivity
```bash
# Test DNS
nslookup api.dropbox.com

# Test ping
ping -c 4 api.dropbox.com

# Test HTTPS
curl -v https://api.dropbox.com/oauth2/token
```

### 2. Check IPv6 Issue
Jika curl menunjukkan "Trying IPv6... Network unreachable", disable IPv6:

```bash
# Temporary (sampai reboot)
sudo sysctl -w net.ipv6.conf.all.disable_ipv6=1
sudo sysctl -w net.ipv6.conf.default.disable_ipv6=1

# Permanent (Fedora)
echo "net.ipv6.conf.all.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf
echo "net.ipv6.conf.default.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

### 3. Check Firewall (Fedora)
```bash
# Allow HTTPS
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 4. Test PHP cURL
```bash
php -r "echo file_get_contents('https://api.dropbox.com/');"
```

### 5. Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## Common Issues

### Issue 1: SELinux Blocking
```bash
# Check SELinux status
getenforce

# Temporary disable (testing only)
sudo setenforce 0

# If this fixes it, create proper policy instead of disabling
```

### Issue 2: PHP cURL Not Installed
```bash
# Install PHP cURL
sudo dnf install php-curl
sudo systemctl restart php-fpm
```

### Issue 3: Outdated CA Certificates
```bash
# Update CA certificates
sudo dnf update ca-certificates
```

### Issue 4: DNS Resolution
```bash
# Try different DNS
echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf
```

## Test Dropbox Connection
Create file `test_dropbox_standalone.php`:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

try {
    echo "Testing Dropbox...\n";
    $dirs = Storage::disk('dropbox')->directories('/');
    echo "✓ Success! Found " . count($dirs) . " directories\n";
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
```

Run:
```bash
php test_dropbox_standalone.php
```
