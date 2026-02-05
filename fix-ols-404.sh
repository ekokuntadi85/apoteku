#!/bin/bash

################################################################################
# OpenLiteSpeed 404 Fix Script
# Diagnose and fix virtual host configuration
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

if [[ $EUID -ne 0 ]]; then
   log_error "This script must be run as root"
   exit 1
fi

DOMAIN="apotek.appjawah.my.id"
APP_DIR="/var/www/apoteku"
OLS_DIR="/usr/local/lsws"
SERVER_IP=$(hostname -I | awk '{print $1}')

log_info "Diagnosing OpenLiteSpeed 404 error..."
echo ""

################################################################################
# STEP 1: Check if app directory exists
################################################################################

log_info "Step 1: Checking application directory..."

if [ -d "$APP_DIR" ]; then
    log_success "Application directory exists: $APP_DIR"
    
    if [ -f "$APP_DIR/public/index.php" ]; then
        log_success "Laravel entry point found: $APP_DIR/public/index.php"
    else
        log_error "Laravel index.php NOT FOUND at $APP_DIR/public/index.php"
        exit 1
    fi
else
    log_error "Application directory NOT FOUND: $APP_DIR"
    exit 1
fi

################################################################################
# STEP 2: Check OpenLiteSpeed status
################################################################################

log_info "Step 2: Checking OpenLiteSpeed status..."

if systemctl is-active --quiet lsws; then
    log_success "OpenLiteSpeed is running"
else
    log_error "OpenLiteSpeed is NOT running!"
    log_info "Starting OpenLiteSpeed..."
    systemctl start lsws
    sleep 2
fi

################################################################################
# STEP 3: Check virtual host configuration
################################################################################

log_info "Step 3: Checking virtual host configuration..."

VHOST_CONF="$OLS_DIR/conf/vhosts/$DOMAIN/vhconf.conf"

if [ -f "$VHOST_CONF" ]; then
    log_success "Virtual host config exists: $VHOST_CONF"
else
    log_error "Virtual host config NOT FOUND: $VHOST_CONF"
    log_info "Creating virtual host configuration..."
    
    mkdir -p "$OLS_DIR/conf/vhosts/$DOMAIN"
    
    cat > "$VHOST_CONF" <<'VHOSTEOF'
docRoot                   $VH_ROOT/public
vhDomain                  $VH_NAME
enableGzip                1
enableIpGeo               1

index  {
  useServer               0
  indexFiles              index.php, index.html
}

errorlog {
  useServer               0
  logLevel                ERROR
  rollingSize             10M
  keepDays                7
}

accesslog {
  useServer               0
  logFormat               "%h %l %u %t \"%r\" %>s %b"
  logHeaders              5
  rollingSize             10M
  keepDays                7
}

scripthandler  {
  add                     lsapi:lsphp83 php
}

extprocessor lsphp83 {
  type                    lsapi
  address                 uds://tmp/lshttpd/lsphp83.sock
  maxConns                35
  env                     PHP_LSAPI_CHILDREN=10
  env                     LSAPI_AVOID_FORK=200M
  initTimeout             60
  retryTimeout            0
  persistConn             1
  respBuffer              0
  autoStart               2
  path                    /usr/local/lsws/lsphp83/bin/lsphp
  backlog                 100
  instances               1
  priority                0
  memSoftLimit            2047M
  memHardLimit            2047M
  procSoftLimit           1400
  procHardLimit           1500
}

phpIniOverride  {
php_admin_value upload_max_filesize "100M"
php_admin_value post_max_size "100M"
php_admin_value max_execution_time "300"
php_admin_value memory_limit "256M"
}

rewrite  {
  enable                  1
  autoLoadHtaccess        1
  logLevel                0
}

context / {
  type                    NULL
  location                $VH_ROOT/public
  allowBrowse             1
  
  rewrite  {
    enable                1
    inherit               1
  }
  addDefaultCharset       off
}
VHOSTEOF
    
    log_success "Virtual host config created!"
fi

################################################################################
# STEP 4: Check main httpd_config.conf
################################################################################

log_info "Step 4: Checking main configuration..."

MAIN_CONF="$OLS_DIR/conf/httpd_config.conf"

# Check if virtual host is defined
if grep -q "virtualhost $DOMAIN" "$MAIN_CONF"; then
    log_success "Virtual host defined in main config"
else
    log_warning "Virtual host NOT defined in main config"
    log_info "Adding virtual host to main config..."
    
    cat >> "$MAIN_CONF" <<EOF

virtualhost $DOMAIN {
  vhRoot                  $APP_DIR
  configFile              $OLS_DIR/conf/vhosts/$DOMAIN/vhconf.conf
  allowSymbolLink         1
  enableScript            1
  restrained              1
  setUIDMode              0
}
EOF
    
    log_success "Virtual host added to main config!"
fi

# Check if listener exists
if grep -q "listener.*80" "$MAIN_CONF"; then
    log_success "HTTP listener found"
else
    log_warning "HTTP listener NOT found"
    log_info "Adding HTTP listener..."
    
    cat >> "$MAIN_CONF" <<EOF

listener Default {
  address                 *:80
  secure                  0
  map                     $DOMAIN $DOMAIN
}
EOF
    
    log_success "HTTP listener added!"
fi

# Check if domain is mapped to listener
if grep -q "map.*$DOMAIN" "$MAIN_CONF"; then
    log_success "Domain mapped to listener"
else
    log_warning "Domain NOT mapped to listener"
    log_info "Adding domain mapping..."
    
    # Find listener block and add mapping
    sed -i "/listener.*{/a\  map                     $DOMAIN $DOMAIN" "$MAIN_CONF"
    
    log_success "Domain mapping added!"
fi

################################################################################
# STEP 5: Check if Example vhost is interfering
################################################################################

log_info "Step 5: Checking for Example virtual host..."

if grep -q "virtualhost Example" "$MAIN_CONF"; then
    log_warning "Example virtual host found (may cause conflicts)"
    log_info "Commenting out Example virtual host..."
    
    # Comment out Example vhost
    sed -i '/virtualhost Example/,/^}/s/^/#/' "$MAIN_CONF"
    
    log_success "Example virtual host disabled!"
fi

################################################################################
# STEP 6: Fix file permissions
################################################################################

log_info "Step 6: Checking file permissions..."

# Check ownership
CURRENT_OWNER=$(stat -c "%U:%G" "$APP_DIR")
if [ "$CURRENT_OWNER" = "nobody:nogroup" ]; then
    log_success "Ownership correct: nobody:nogroup"
else
    log_warning "Ownership incorrect: $CURRENT_OWNER"
    log_info "Fixing ownership..."
    chown -R nobody:nogroup "$APP_DIR"
    log_success "Ownership fixed!"
fi

# Check public directory permissions
if [ -d "$APP_DIR/public" ]; then
    PUBLIC_PERM=$(stat -c "%a" "$APP_DIR/public")
    if [ "$PUBLIC_PERM" = "755" ]; then
        log_success "Public directory permissions correct: 755"
    else
        log_warning "Public directory permissions incorrect: $PUBLIC_PERM"
        chmod 755 "$APP_DIR/public"
        log_success "Public directory permissions fixed!"
    fi
fi

# Check index.php
if [ -f "$APP_DIR/public/index.php" ]; then
    INDEX_PERM=$(stat -c "%a" "$APP_DIR/public/index.php")
    if [ "$INDEX_PERM" = "644" ]; then
        log_success "index.php permissions correct: 644"
    else
        log_warning "index.php permissions incorrect: $INDEX_PERM"
        chmod 644 "$APP_DIR/public/index.php"
        log_success "index.php permissions fixed!"
    fi
fi

################################################################################
# STEP 7: Test PHP execution
################################################################################

log_info "Step 7: Testing PHP execution..."

# Create test PHP file
cat > "$APP_DIR/public/test.php" <<'PHPEOF'
<?php
phpinfo();
PHPEOF

chmod 644 "$APP_DIR/public/test.php"
chown nobody:nogroup "$APP_DIR/public/test.php"

log_success "Test PHP file created: $APP_DIR/public/test.php"

################################################################################
# STEP 8: Restart OpenLiteSpeed
################################################################################

log_info "Step 8: Restarting OpenLiteSpeed..."

systemctl restart lsws

sleep 3

if systemctl is-active --quiet lsws; then
    log_success "OpenLiteSpeed restarted successfully!"
else
    log_error "OpenLiteSpeed failed to restart!"
    log_info "Check logs: tail -f $OLS_DIR/logs/error.log"
    exit 1
fi

################################################################################
# STEP 9: Test access
################################################################################

log_info "Step 9: Testing access..."

echo ""
log_info "Testing localhost access..."

# Test localhost
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/ 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "301" ]; then
    log_success "✓ localhost → HTTP $HTTP_CODE (OK!)"
elif [ "$HTTP_CODE" = "404" ]; then
    log_error "✗ localhost → HTTP 404 (Still not working)"
else
    log_warning "✗ localhost → HTTP $HTTP_CODE"
fi

# Test with domain
log_info "Testing domain access..."
HTTP_CODE_DOMAIN=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: $DOMAIN" http://localhost/ 2>/dev/null || echo "000")

if [ "$HTTP_CODE_DOMAIN" = "200" ] || [ "$HTTP_CODE_DOMAIN" = "302" ] || [ "$HTTP_CODE_DOMAIN" = "301" ]; then
    log_success "✓ $DOMAIN → HTTP $HTTP_CODE_DOMAIN (OK!)"
elif [ "$HTTP_CODE_DOMAIN" = "404" ]; then
    log_error "✗ $DOMAIN → HTTP 404 (Still not working)"
else
    log_warning "✗ $DOMAIN → HTTP $HTTP_CODE_DOMAIN"
fi

# Test PHP
log_info "Testing PHP execution..."
HTTP_CODE_PHP=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/test.php 2>/dev/null || echo "000")

if [ "$HTTP_CODE_PHP" = "200" ]; then
    log_success "✓ PHP test → HTTP 200 (Working!)"
else
    log_warning "✗ PHP test → HTTP $HTTP_CODE_PHP"
fi

################################################################################
# Display Results
################################################################################

echo ""
echo "========================================"
echo "DIAGNOSIS COMPLETE"
echo "========================================"
echo ""

cat << EOF
${YELLOW}📊 CONFIGURATION SUMMARY${NC}

  Application:     $APP_DIR
  Public Dir:      $APP_DIR/public
  Virtual Host:    $VHOST_CONF
  Main Config:     $MAIN_CONF
  Domain:          $DOMAIN
  Server IP:       $SERVER_IP

${YELLOW}🧪 TEST RESULTS${NC}

  localhost:       HTTP $HTTP_CODE
  $DOMAIN:         HTTP $HTTP_CODE_DOMAIN
  test.php:        HTTP $HTTP_CODE_PHP

${YELLOW}📝 NEXT STEPS${NC}

EOF

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    cat << EOF
${GREEN}✅ SUCCESS! Your application is working!${NC}

Test in browser:
  • http://$SERVER_IP
  • http://$DOMAIN (if DNS is configured)
  • http://$SERVER_IP/test.php (PHP info)

To remove test file:
  ${GREEN}rm $APP_DIR/public/test.php${NC}

EOF
else
    cat << EOF
${RED}⚠️  Still getting 404. Additional troubleshooting needed.${NC}

1. Check OpenLiteSpeed error log:
   ${GREEN}tail -f $OLS_DIR/logs/error.log${NC}

2. Check access log:
   ${GREEN}tail -f $OLS_DIR/logs/access.log${NC}

3. Check vhost error log:
   ${GREEN}tail -f $OLS_DIR/logs/vhost/$DOMAIN/error.log${NC}

4. Verify virtual host in WebAdmin:
   ${GREEN}https://$SERVER_IP:7080${NC}
   → Virtual Hosts → Check if $DOMAIN exists

5. Check listener mapping in WebAdmin:
   → Listeners → Default → Virtual Host Mappings

6. Try restarting:
   ${GREEN}systemctl restart lsws${NC}

EOF
fi

echo "========================================"

# Save diagnostic info
cat > /root/ols-diagnostic.txt <<EOF
OpenLiteSpeed Diagnostic Report
Generated: $(date)

Application Directory: $APP_DIR
Public Directory: $APP_DIR/public
Index File: $APP_DIR/public/index.php
Virtual Host Config: $VHOST_CONF
Main Config: $MAIN_CONF

Test Results:
- localhost: HTTP $HTTP_CODE
- $DOMAIN: HTTP $HTTP_CODE_DOMAIN
- test.php: HTTP $HTTP_CODE_PHP

OpenLiteSpeed Status: $(systemctl is-active lsws)

File Permissions:
- $APP_DIR: $(stat -c "%U:%G %a" "$APP_DIR")
- $APP_DIR/public: $(stat -c "%U:%G %a" "$APP_DIR/public")
- $APP_DIR/public/index.php: $(stat -c "%U:%G %a" "$APP_DIR/public/index.php")

Virtual Host Defined: $(grep -q "virtualhost $DOMAIN" "$MAIN_CONF" && echo "YES" || echo "NO")
Listener Defined: $(grep -q "listener.*80" "$MAIN_CONF" && echo "YES" || echo "NO")
Domain Mapped: $(grep -q "map.*$DOMAIN" "$MAIN_CONF" && echo "YES" || echo "NO")
EOF

log_info "Diagnostic report saved to: /root/ols-diagnostic.txt"
