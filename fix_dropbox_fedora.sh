#!/bin/bash

# Dropbox Connectivity Fix Script for Fedora (Non-Docker)
# Run this on the problematic Fedora server

echo "========================================="
echo "Dropbox Connectivity Fix for Fedora"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if running as root for some commands
if [ "$EUID" -ne 0 ]; then 
    echo -e "${YELLOW}Note: Some fixes require sudo privileges${NC}"
    echo ""
fi

# Step 1: Test current connectivity
echo -e "${BLUE}Step 1: Testing current Dropbox connectivity...${NC}"
if curl -s --connect-timeout 5 https://api.dropbox.com/ > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Basic HTTPS connection works${NC}"
else
    echo -e "${RED}✗ Cannot connect to Dropbox${NC}"
fi
echo ""

# Step 2: Check for IPv6 issue
echo -e "${BLUE}Step 2: Checking for IPv6 issue...${NC}"
IPV6_ISSUE=false
if curl -v https://api.dropbox.com/ 2>&1 | grep -q "Trying.*:.*:.*:"; then
    if curl -v https://api.dropbox.com/ 2>&1 | grep -q "Network unreachable"; then
        echo -e "${RED}✗ IPv6 connection failing (Network unreachable)${NC}"
        IPV6_ISSUE=true
    else
        echo -e "${YELLOW}⚠ IPv6 is being attempted${NC}"
    fi
else
    echo -e "${GREEN}✓ No IPv6 issue detected${NC}"
fi
echo ""

# Step 3: Apply fixes
if [ "$IPV6_ISSUE" = true ]; then
    echo -e "${BLUE}Step 3: Applying IPv6 fix...${NC}"
    
    # Check current IPv6 status
    IPV6_DISABLED=$(sysctl net.ipv6.conf.all.disable_ipv6 2>/dev/null | awk '{print $3}')
    
    if [ "$IPV6_DISABLED" = "1" ]; then
        echo -e "${GREEN}✓ IPv6 already disabled${NC}"
    else
        echo -e "${YELLOW}Disabling IPv6...${NC}"
        
        # Temporary disable
        sudo sysctl -w net.ipv6.conf.all.disable_ipv6=1 2>/dev/null
        sudo sysctl -w net.ipv6.conf.default.disable_ipv6=1 2>/dev/null
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ IPv6 disabled temporarily${NC}"
            
            # Ask for permanent fix
            echo ""
            read -p "Make this change permanent? (y/n) " -n 1 -r
            echo ""
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                if ! grep -q "net.ipv6.conf.all.disable_ipv6" /etc/sysctl.conf 2>/dev/null; then
                    echo "net.ipv6.conf.all.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf > /dev/null
                    echo "net.ipv6.conf.default.disable_ipv6 = 1" | sudo tee -a /etc/sysctl.conf > /dev/null
                    sudo sysctl -p > /dev/null 2>&1
                    echo -e "${GREEN}✓ IPv6 disabled permanently${NC}"
                else
                    echo -e "${YELLOW}⚠ Already configured in /etc/sysctl.conf${NC}"
                fi
            fi
        else
            echo -e "${RED}✗ Failed to disable IPv6 (need sudo)${NC}"
        fi
    fi
else
    echo -e "${BLUE}Step 3: Checking other potential issues...${NC}"
    
    # Check firewall
    if command -v firewall-cmd &> /dev/null; then
        if sudo firewall-cmd --query-service=https 2>/dev/null | grep -q "yes"; then
            echo -e "${GREEN}✓ Firewall allows HTTPS${NC}"
        else
            echo -e "${YELLOW}⚠ HTTPS not explicitly allowed in firewall${NC}"
            read -p "Allow HTTPS in firewall? (y/n) " -n 1 -r
            echo ""
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                sudo firewall-cmd --permanent --add-service=https
                sudo firewall-cmd --reload
                echo -e "${GREEN}✓ HTTPS allowed in firewall${NC}"
            fi
        fi
    fi
    
    # Check SELinux
    if command -v getenforce &> /dev/null; then
        SELINUX_STATUS=$(getenforce 2>/dev/null)
        if [ "$SELINUX_STATUS" = "Enforcing" ]; then
            echo -e "${YELLOW}⚠ SELinux is enforcing (may block connections)${NC}"
            echo "  If issues persist, check: sudo ausearch -m avc -ts recent"
        fi
    fi
fi
echo ""

# Step 4: Test PHP cURL
echo -e "${BLUE}Step 4: Testing PHP cURL...${NC}"
if command -v php &> /dev/null; then
    PHP_CURL_TEST=$(php -r "echo @file_get_contents('https://api.dropbox.com/');" 2>&1)
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ PHP can connect to Dropbox${NC}"
    else
        echo -e "${RED}✗ PHP cannot connect to Dropbox${NC}"
        
        # Check if php-curl is installed
        if ! php -m | grep -q curl; then
            echo -e "${YELLOW}⚠ PHP cURL extension not found${NC}"
            read -p "Install php-curl? (y/n) " -n 1 -r
            echo ""
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                sudo dnf install -y php-curl
                sudo systemctl restart php-fpm 2>/dev/null
                echo -e "${GREEN}✓ PHP cURL installed${NC}"
            fi
        fi
    fi
else
    echo -e "${YELLOW}⚠ PHP not found in PATH${NC}"
fi
echo ""

# Step 5: Clear Laravel cache
echo -e "${BLUE}Step 5: Clearing Laravel cache...${NC}"
if [ -f "artisan" ]; then
    php artisan config:clear > /dev/null 2>&1
    php artisan cache:clear > /dev/null 2>&1
    echo -e "${GREEN}✓ Laravel cache cleared${NC}"
else
    echo -e "${YELLOW}⚠ Not in Laravel directory${NC}"
fi
echo ""

# Step 6: Final verification
echo -e "${BLUE}Step 6: Final verification...${NC}"
echo "Testing connection to Dropbox API..."

CURL_OUTPUT=$(curl -v https://api.dropbox.com/oauth2/token 2>&1)
if echo "$CURL_OUTPUT" | grep -q "Trying.*:.*:.*:"; then
    echo -e "${RED}✗ Still attempting IPv6${NC}"
    echo "  Manual fix needed - contact system administrator"
elif echo "$CURL_OUTPUT" | grep -q "Connected to"; then
    echo -e "${GREEN}✓ Successfully connected to Dropbox!${NC}"
    echo ""
    echo -e "${GREEN}=========================================${NC}"
    echo -e "${GREEN}Fix applied successfully!${NC}"
    echo -e "${GREEN}=========================================${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Test backup from your application"
    echo "2. Monitor logs: tail -f storage/logs/laravel.log"
else
    echo -e "${YELLOW}⚠ Connection status unclear${NC}"
    echo "  Check manually: curl -v https://api.dropbox.com/"
fi
echo ""

echo "========================================="
echo "Script completed"
echo "========================================="
