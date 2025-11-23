# 🚀 Automatic Deployment Setup Guide

## Overview
This guide will help you set up automatic deployment from GitHub to your production server.

## Prerequisites

### On Your Server:
- [ ] Ubuntu/Debian Linux server (or similar)
- [ ] PHP 8.4+ installed
- [ ] Composer installed
- [ ] Node.js 22+ and npm installed
- [ ] Git installed
- [ ] Nginx or Apache configured
- [ ] MySQL/PostgreSQL/SQLite database
- [ ] SSH access enabled

### On GitHub:
- [ ] Repository pushed to GitHub
- [ ] Admin access to repository settings

---

## Step 1: Prepare Your Server

### 1.1 Create Deployment User (Recommended)

```bash
# SSH into your server
ssh user@your-server.com

# Create a deployment user
sudo adduser deployer
sudo usermod -aG www-data deployer

# Switch to deployer user
su - deployer
```

### 1.2 Clone Your Repository

```bash
# Navigate to web directory
cd /var/www

# Clone your repository
git clone https://github.com/YOUR_USERNAME/apoteku.git
cd apoteku

# Set up Laravel
cp .env.example .env
nano .env  # Edit with your production settings

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Set permissions
sudo chown -R deployer:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 1.3 Configure Web Server

**For Nginx:**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/apoteku/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Step 2: Generate SSH Keys

### 2.1 On Your Local Machine:

```bash
# Generate a new SSH key pair for deployment
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_deploy

# This creates:
# - Private key: ~/.ssh/github_deploy
# - Public key: ~/.ssh/github_deploy.pub
```

### 2.2 Add Public Key to Server:

```bash
# Copy the public key
cat ~/.ssh/github_deploy.pub

# SSH to your server
ssh user@your-server.com

# Add the public key to authorized_keys
su - deployer
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys
# Paste the public key, save and exit

chmod 600 ~/.ssh/authorized_keys
```

### 2.3 Test SSH Connection:

```bash
# From your local machine
ssh -i ~/.ssh/github_deploy deployer@your-server.com

# If successful, you're connected!
```

---

## Step 3: Configure GitHub Secrets

### 3.1 Go to GitHub Repository Settings:

1. Navigate to your repository on GitHub
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**

### 3.2 Add These Secrets:

| Secret Name | Value | Example |
|-------------|-------|---------|
| `SSH_HOST` | Your server IP or domain | `123.45.67.89` or `server.example.com` |
| `SSH_USERNAME` | SSH username | `deployer` |
| `SSH_PRIVATE_KEY` | Private key content | Contents of `~/.ssh/github_deploy` |
| `SSH_PORT` | SSH port (optional) | `22` (default) |
| `DEPLOY_PATH` | Path to project on server | `/var/www/apoteku` |

**To get private key content:**
```bash
cat ~/.ssh/github_deploy
# Copy the ENTIRE output including:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ... key content ...
# -----END OPENSSH PRIVATE KEY-----
```

### 3.3 Create Production Environment:

1. Go to **Settings** → **Environments**
2. Click **New environment**
3. Name it `Production`
4. (Optional) Add protection rules:
   - Required reviewers
   - Wait timer
   - Deployment branches (only `main`)

---

## Step 4: Test Deployment

### 4.1 Manual Test:

1. Go to **Actions** tab in GitHub
2. Click **Deploy to Production** workflow
3. Click **Run workflow** → **Run workflow**
4. Watch the deployment progress

### 4.2 Automatic Test:

```bash
# Make a small change
echo "# Test deployment" >> README.md
git add README.md
git commit -m "test: trigger deployment"
git push origin main

# Watch GitHub Actions tab for deployment
```

---

## Step 5: Monitoring & Rollback

### 5.1 Monitor Deployments:

- Check **Actions** tab for deployment status
- SSH to server and check logs:
  ```bash
  tail -f /var/www/apoteku/storage/logs/laravel.log
  ```

### 5.2 Rollback if Needed:

```bash
# SSH to server
ssh deployer@your-server.com
cd /var/www/apoteku

# Put in maintenance mode
php artisan down

# Rollback to previous commit
git log --oneline  # Find previous commit hash
git reset --hard <commit-hash>

# Re-install dependencies
composer install --no-dev
npm ci
npm run build

# Run migrations down if needed
php artisan migrate:rollback

# Bring back online
php artisan up
```

---

## Advanced: Zero-Downtime Deployment

For zero-downtime deployments, consider using:

### Option A: Laravel Envoyer (Paid)
- https://envoyer.io
- Managed zero-downtime deployments
- Health checks
- Deployment notifications

### Option B: Deployer (Free)
- https://deployer.org
- PHP-based deployment tool
- Supports Laravel
- Zero-downtime releases

### Option C: Custom Symlink Strategy

```bash
# Create releases directory structure
/var/www/apoteku/
├── current -> releases/20240124-050000
├── releases/
│   ├── 20240124-040000/
│   └── 20240124-050000/
├── shared/
│   ├── storage/
│   └── .env
```

---

## Troubleshooting

### Issue: Permission Denied

```bash
# Fix permissions on server
sudo chown -R deployer:www-data /var/www/apoteku
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Composer Install Fails

```bash
# Check PHP version
php -v

# Update Composer
composer self-update
```

### Issue: NPM Build Fails

```bash
# Check Node version
node -v

# Clear npm cache
npm cache clean --force
```

### Issue: Database Migration Fails

```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check .env file
cat .env | grep DB_
```

---

## Security Best Practices

1. ✅ Use SSH keys (not passwords)
2. ✅ Use a dedicated deployment user
3. ✅ Disable root SSH login
4. ✅ Use firewall (ufw/iptables)
5. ✅ Keep secrets in GitHub Secrets (never in code)
6. ✅ Use HTTPS (Let's Encrypt)
7. ✅ Enable fail2ban
8. ✅ Regular security updates

---

## Deployment Checklist

Before each deployment:

- [ ] Tests pass locally
- [ ] Tests pass in CI
- [ ] Database migrations tested
- [ ] .env.example updated
- [ ] Dependencies updated in composer.json
- [ ] Assets built successfully
- [ ] Backup database
- [ ] Notify team

After deployment:

- [ ] Check application is accessible
- [ ] Check logs for errors
- [ ] Test critical features
- [ ] Monitor performance
- [ ] Update documentation

---

## Need Help?

- Laravel Deployment Docs: https://laravel.com/docs/deployment
- GitHub Actions Docs: https://docs.github.com/actions
- DigitalOcean Laravel Guide: https://www.digitalocean.com/community/tutorials/how-to-deploy-laravel-application

---

**Happy Deploying! 🚀**
