# 🏥 Apoteku - Sistem Manajemen Apotek

Aplikasi manajemen apotek modern dengan fitur lengkap untuk mengelola inventory, penjualan, dan backup database otomatis.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-pink.svg)](https://livewire.laravel.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://www.docker.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## ✨ **Features**

### **Core Features**
- 📦 **Inventory Management** - Kelola stok obat dengan mudah
- 💰 **Point of Sale (POS)** - Sistem penjualan yang cepat
- 👥 **User Management** - Role & permissions dengan Spatie
- 📊 **Reporting** - Laporan penjualan dan inventory
- 🎨 **Dark Mode** - UI modern dengan dark/light mode

### **Backup Features** ⭐ NEW!
- ☁️ **Dropbox Integration** - Backup otomatis ke cloud (permanent token)
- 🗜️ **GZIP Compression** - Hemat 70-90% ukuran file
- 🧹 **Auto-cleanup** - Hapus backup lokal >10 hari otomatis
- ⏰ **Scheduled Backup** - Backup otomatis setiap hari jam 21:30
- 🔔 **Real-time Notifications** - Notifikasi dengan auto-hide
- 📈 **Progress Indicators** - Track backup progress real-time

---

## 🚀 **Quick Start**

### **Prerequisites**
- Docker & Docker Compose
- Git
- Port 80 available

### **Installation**

```bash
# 1. Clone repository
git clone https://github.com/ekokuntadi85/apoteku.git
cd apoteku

# 2. Checkout branch
git checkout feature/new

# 3. Copy environment file
cp .env.example .env

# 4. Edit .env (set DB_PASSWORD)
nano .env

# 5. Build & start Docker
docker compose up -d --build

# 6. Generate app key
docker compose exec app php artisan key:generate

# 7. Run migrations
docker compose exec app php artisan migrate

# 8. Access application
open http://localhost
```

**Selesai!** 🎉

---

## 📖 **Documentation**

Dokumentasi lengkap tersedia di folder `docs/`:

- 📘 **[Deployment Guide](docs/DEPLOYMENT_GUIDE.md)** - Setup di mesin baru
- 📗 **[Dropbox Integration](docs/DROPBOX_BACKUP_INTEGRATION.md)** - Setup Dropbox backup
- 📙 **[Token Management](docs/DROPBOX_TOKEN_MANAGEMENT.md)** - Manage Dropbox tokens
- 📕 **[Backup Enhancements](docs/BACKUP_ENHANCEMENTS.md)** - Fitur backup terbaru

---

## 🛠️ **Tech Stack**

### **Backend**
- **Framework:** Laravel 12.x
- **Database:** MariaDB 10.6
- **Cache:** Database driver
- **Queue:** Database driver

### **Frontend**
- **UI Framework:** Livewire 3.x + Flux
- **CSS:** Tailwind CSS
- **Icons:** Heroicons
- **Dark Mode:** Built-in

### **DevOps**
- **Containerization:** Docker + Docker Compose
- **Web Server:** Nginx 1.25
- **PHP:** 8.3 FPM
- **Cloud Storage:** Dropbox API

---

## 📦 **Key Dependencies**

```json
{
  "laravel/framework": "^12.0",
  "livewire/livewire": "^3.6",
  "livewire/flux": "^2.2",
  "spatie/laravel-permission": "^6.21",
  "spatie/flysystem-dropbox": "^3.0",
  "maatwebsite/excel": "^3.1",
  "barryvdh/laravel-dompdf": "^3.1"
}
```

---

## ⚙️ **Configuration**

### **Environment Variables**

```env
# Application
APP_NAME=Muazara
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=muazara
DB_USERNAME=user
DB_PASSWORD=your_secure_password

# Dropbox (Optional)
DROPBOX_ENABLED=true
DROPBOX_APP_KEY=your_app_key
DROPBOX_APP_SECRET=your_app_secret
DROPBOX_REFRESH_TOKEN=your_refresh_token
```

---

## 🔄 **Backup System**

### **Automated Backup**
- **Schedule:** Daily at 21:30 WIB
- **Compression:** GZIP (85-90% reduction)
- **Storage:** Local + Dropbox (optional)
- **Retention:** Local 10 days, Dropbox permanent

### **Manual Backup**

```bash
# Via UI
Navigate to: Manajemen Backup → Buat Backup Baru

# Via Command
docker compose exec app php artisan backup:auto
```

### **Test Dropbox Connection**

```bash
docker compose exec app php artisan dropbox:test
```

---

## 📊 **Database Schema**

```
├── users (User accounts)
├── roles (User roles)
├── permissions (Access permissions)
├── products (Inventory items)
├── sales (Sales transactions)
├── customers (Customer data)
└── ... (and more)
```

---

## 🔐 **Security**

- ✅ CSRF Protection
- ✅ SQL Injection Prevention
- ✅ XSS Protection
- ✅ Password Hashing (Bcrypt)
- ✅ Role-based Access Control
- ✅ Secure Session Management
- ✅ Environment Variables for Secrets

---

## 🧪 **Testing**

```bash
# Run tests
docker compose exec app php artisan test

# Run specific test
docker compose exec app php artisan test --filter=BackupTest

# Code coverage
docker compose exec app php artisan test --coverage
```

---

## 📈 **Performance**

### **Optimization**
- ✅ Database indexing
- ✅ Query optimization
- ✅ Asset compilation (Vite)
- ✅ GZIP compression
- ✅ Opcache enabled
- ✅ Docker multi-stage build

### **Caching**

```bash
# Cache config
docker compose exec app php artisan config:cache

# Cache routes
docker compose exec app php artisan route:cache

# Cache views
docker compose exec app php artisan view:cache

# Clear all cache
docker compose exec app php artisan optimize:clear
```

---

## 🐛 **Troubleshooting**

### **Common Issues**

**Port 80 already in use:**
```bash
sudo lsof -i :80
sudo systemctl stop apache2
```

**Permission denied:**
```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

**Database connection failed:**
```bash
docker compose restart db
docker compose logs db
```

**Dropbox token expired:**
```bash
# Generate new refresh token (see docs/DROPBOX_TOKEN_MANAGEMENT.md)
```

---

## 🤝 **Contributing**

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 **Authors**

- **Eko Kuntadi** - *Initial work* - [@ekokuntadi85](https://github.com/ekokuntadi85)

---

## 🙏 **Acknowledgments**

- Laravel Team for the amazing framework
- Livewire Team for reactive components
- Spatie for excellent packages
- All contributors and supporters

---

## 📞 **Support**

- 📧 Email: support@example.com
- 🐛 Issues: [GitHub Issues](https://github.com/ekokuntadi85/apoteku/issues)
- 📖 Docs: [Documentation](docs/)

---

## 🗺️ **Roadmap**

- [ ] Multi-branch support
- [ ] Barcode scanner integration
- [ ] WhatsApp notifications
- [ ] Advanced reporting & analytics
- [ ] Mobile app (React Native)
- [ ] API documentation (Swagger)

---

**Made with ❤️ in Indonesia**

---

## 📸 **Screenshots**

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### POS System
![POS](docs/screenshots/pos.png)

### Backup Management
![Backup](docs/screenshots/backup.png)

---

**Version:** 1.1.0  
**Last Updated:** 2025-12-09
