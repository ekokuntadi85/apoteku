# 🚀 Deployment Guide - Setup di Mesin Lain

Dokumentasi lengkap untuk menjalankan aplikasi Apoteku di mesin/server baru menggunakan Docker.

---

## 📋 **Prerequisites**

Pastikan mesin sudah terinstall:
- ✅ **Docker** (v20.10+)
- ✅ **Docker Compose** (v2.0+)
- ✅ **Git**
- ✅ **Port 80** tersedia (untuk web server)

---

## 🔧 **Step 1: Clone Repository**

```bash
# Clone repository
git clone https://github.com/ekokuntadi85/apoteku.git
cd apoteku

# Checkout ke branch feature/new (atau branch yang diinginkan)
git checkout feature/new
```

---

## ⚙️ **Step 2: Setup Environment**

### **2.1 Copy .env.example ke .env**

```bash
cp .env.example .env
```

### **2.2 Edit .env**

```bash
nano .env
```

**Konfigurasi Minimal:**

```env
# Application
APP_NAME=Muazara
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database (akan otomatis dibuat di Docker)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=muazara
DB_USERNAME=user
DB_PASSWORD=GANTI_PASSWORD_INI_DENGAN_YANG_KUAT

# Docker User (sesuaikan dengan user di mesin baru)
CURRENT_UID=1000
CURRENT_GID=1000

# Dropbox (Optional - untuk backup cloud)
DROPBOX_ENABLED=false
DROPBOX_APP_KEY=
DROPBOX_APP_SECRET=
DROPBOX_REFRESH_TOKEN=
```

**PENTING:** Ganti `DB_PASSWORD` dengan password yang kuat!

---

## 🐳 **Step 3: Build & Run Docker**

### **3.1 Build Docker Images**

```bash
# Build images (pertama kali akan lama ~5-10 menit)
docker compose build

# Atau build tanpa cache jika ada masalah
docker compose build --no-cache
```

### **3.2 Start Containers**

```bash
# Start semua services
docker compose up -d

# Cek status containers
docker compose ps
```

**Expected Output:**
```
NAME                IMAGE               STATUS
muazara-app-nginx   nginx:1.25-alpine   Up
muazara-app-php     apoteku-app         Up
muazara-db          mariadb:10.6        Up (healthy)
```

---

## 🔑 **Step 4: Generate APP_KEY**

```bash
# Generate application key
docker compose exec app php artisan key:generate

# Verify key sudah ter-generate
grep APP_KEY .env
```

---

## 💾 **Step 5: Setup Database**

### **5.1 Run Migrations**

```bash
# Run database migrations
docker compose exec app php artisan migrate

# Jika diminta konfirmasi, ketik: yes
```

### **5.2 Seed Database (Optional)**

```bash
# Seed data awal (users, roles, permissions)
docker compose exec app php artisan db:seed
```

---

## 🔐 **Step 6: Setup Permissions & Storage**

```bash
# Link storage
docker compose exec app php artisan storage:link

# Set permissions (jika perlu)
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

## ✅ **Step 7: Verify Installation**

### **7.1 Cek Aplikasi Berjalan**

Buka browser: `http://localhost` atau `http://your-server-ip`

### **7.2 Test Database Connection**

```bash
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
>>> echo "Database connected!";
>>> exit
```

### **7.3 Cek Logs**

```bash
# Cek logs aplikasi
docker compose logs -f app

# Cek logs database
docker compose logs -f db

# Cek logs nginx
docker compose logs -f nginx
```

---

## 🌐 **Step 8: Setup Domain (Production)**

### **8.1 Update .env**

```env
APP_URL=https://your-domain.com
```

### **8.2 Setup Nginx Reverse Proxy (Host)**

Jika menggunakan Nginx di host server:

```nginx
# /etc/nginx/sites-available/apoteku
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/apoteku /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### **8.3 Setup SSL (Recommended)**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot --nginx -d your-domain.com
```

---

## 📦 **Step 9: Setup Dropbox Backup (Optional)**

Jika ingin menggunakan Dropbox backup:

### **9.1 Create Dropbox App**

1. Buka: https://www.dropbox.com/developers/apps
2. Create App → Scoped access → App folder
3. Copy **App key** dan **App secret**

### **9.2 Generate Refresh Token**

```bash
# Buka URL ini di browser (ganti APP_KEY):
https://www.dropbox.com/oauth2/authorize?client_id=APP_KEY&response_type=code&token_access_type=offline

# Setelah authorize, copy code dari URL
# Lalu jalankan:
curl -X POST https://api.dropbox.com/oauth2/token \
  -d code=YOUR_CODE \
  -d grant_type=authorization_code \
  -u APP_KEY:APP_SECRET

# Copy refresh_token dari response
```

### **9.3 Update .env**

```env
DROPBOX_ENABLED=true
DROPBOX_APP_KEY=your_app_key
DROPBOX_APP_SECRET=your_app_secret
DROPBOX_REFRESH_TOKEN=your_refresh_token
```

### **9.4 Restart & Test**

```bash
docker compose restart app
docker compose exec app php artisan dropbox:test
```

---

## ⏰ **Step 10: Setup Scheduled Backup**

### **10.1 Add Scheduler Service**

Edit `docker-compose.yml`, tambahkan:

```yaml
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

### **10.2 Restart Docker**

```bash
docker compose up -d
```

### **10.3 Verify Schedule**

```bash
docker compose exec app php artisan schedule:list
```

**Expected Output:**
```
30 21 * * *  php artisan backup:auto  Next Due: XX hours from now
```

---

## 🔄 **Update Aplikasi (Pull Changes)**

```bash
# Stop containers
docker compose down

# Pull latest changes
git pull origin feature/new

# Rebuild jika ada perubahan dependencies
docker compose build

# Start containers
docker compose up -d

# Run migrations jika ada
docker compose exec app php artisan migrate

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
```

---

## 🛠️ **Troubleshooting**

### **Issue: Port 80 already in use**

```bash
# Cek process yang menggunakan port 80
sudo lsof -i :80

# Stop service yang menggunakan port 80
sudo systemctl stop apache2  # atau nginx
```

### **Issue: Permission denied**

```bash
# Fix permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### **Issue: Database connection failed**

```bash
# Cek database container
docker compose logs db

# Restart database
docker compose restart db

# Wait for healthy status
docker compose ps
```

### **Issue: Composer dependencies error**

```bash
# Clear vendor dan reinstall
docker compose down
docker volume rm apoteku_vendor_data
docker compose up -d --build
```

---

## 📊 **Monitoring**

### **Cek Resource Usage**

```bash
# CPU & Memory usage
docker stats

# Disk usage
docker system df
```

### **Cek Logs**

```bash
# All logs
docker compose logs -f

# Specific service
docker compose logs -f app
docker compose logs -f db
docker compose logs -f nginx
```

### **Backup Database Manual**

```bash
# Via artisan command
docker compose exec app php artisan backup:auto

# Via mysqldump
docker compose exec db mysqldump -u user -p muazara > backup.sql
```

---

## 🔒 **Security Checklist**

- [ ] Ganti `DB_PASSWORD` dengan password kuat
- [ ] Set `APP_DEBUG=false` di production
- [ ] Setup SSL/HTTPS
- [ ] Restrict database port (jangan expose ke public)
- [ ] Setup firewall (UFW/iptables)
- [ ] Regular backup database
- [ ] Monitor logs untuk suspicious activity
- [ ] Keep Docker images updated

---

## 📚 **Useful Commands**

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Restart specific service
docker compose restart app

# View logs
docker compose logs -f app

# Execute command in container
docker compose exec app php artisan [command]

# Access container shell
docker compose exec app sh

# Rebuild containers
docker compose up -d --build

# Remove all (DANGER!)
docker compose down -v
```

---

## 📞 **Support**

Jika ada masalah:
1. Cek logs: `docker compose logs -f`
2. Cek dokumentasi di folder `docs/`
3. Cek issue di repository

---

## 📝 **Changelog**

### **v1.1.0** - 2025-12-09
- ✅ Dropbox integration with OAuth 2.0 refresh token
- ✅ GZIP compression (70-90% reduction)
- ✅ Auto-cleanup local backups (>10 days)
- ✅ Scheduled backup (daily 21:30 WIB)
- ✅ Real-time notifications
- ✅ Comprehensive documentation

---

**Happy Deploying! 🚀**
