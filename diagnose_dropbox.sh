#!/bin/bash

echo "========================================="
echo "Dropbox Connectivity Diagnostic Script"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: DNS Resolution
echo -e "${YELLOW}Test 1: DNS Resolution${NC}"
if nslookup api.dropbox.com > /dev/null 2>&1; then
    echo -e "${GREEN}✓ DNS resolution working${NC}"
    nslookup api.dropbox.com | grep "Address:" | tail -2
else
    echo -e "${RED}✗ DNS resolution failed${NC}"
fi
echo ""

# Test 2: Ping
echo -e "${YELLOW}Test 2: Ping Dropbox API${NC}"
if ping -c 3 api.dropbox.com > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Ping successful${NC}"
    ping -c 3 api.dropbox.com | tail -2
else
    echo -e "${RED}✗ Ping failed${NC}"
fi
echo ""

# Test 3: HTTPS Connection
echo -e "${YELLOW}Test 3: HTTPS Connection${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 https://api.dropbox.com/ 2>&1)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ HTTPS connection successful (HTTP $HTTP_CODE)${NC}"
else
    echo -e "${RED}✗ HTTPS connection failed${NC}"
    echo "Error: $HTTP_CODE"
fi
echo ""

# Test 4: Firewall Status (Fedora)
echo -e "${YELLOW}Test 4: Firewall Status${NC}"
if command -v firewall-cmd &> /dev/null; then
    if sudo firewall-cmd --state > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Firewalld is running${NC}"
        echo "HTTPS service allowed: $(sudo firewall-cmd --query-service=https && echo 'yes' || echo 'no')"
        echo ""
        echo "Active zones:"
        sudo firewall-cmd --get-active-zones
    else
        echo -e "${YELLOW}⚠ Firewalld is not running${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Firewalld not installed${NC}"
fi
echo ""

# Test 5: Proxy Settings
echo -e "${YELLOW}Test 5: Proxy Settings${NC}"
if [ -n "$HTTP_PROXY" ] || [ -n "$HTTPS_PROXY" ]; then
    echo -e "${YELLOW}⚠ Proxy detected:${NC}"
    echo "HTTP_PROXY: $HTTP_PROXY"
    echo "HTTPS_PROXY: $HTTPS_PROXY"
    echo "NO_PROXY: $NO_PROXY"
else
    echo -e "${GREEN}✓ No proxy configured${NC}"
fi
echo ""

# Test 6: Docker Network (if applicable)
echo -e "${YELLOW}Test 6: Docker Container Test${NC}"
if command -v docker &> /dev/null; then
    if docker compose ps | grep -q "app"; then
        echo "Testing from Docker container..."
        docker compose exec -T app curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" --connect-timeout 10 https://api.dropbox.com/
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓ Docker container can reach Dropbox${NC}"
        else
            echo -e "${RED}✗ Docker container cannot reach Dropbox${NC}"
        fi
    else
        echo -e "${YELLOW}⚠ Docker app container not running${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Docker not installed${NC}"
fi
echo ""

# Test 7: Laravel Dropbox Test
echo -e "${YELLOW}Test 7: Laravel Dropbox Connection${NC}"
if [ -f "test_dropbox.php" ]; then
    echo "Running Laravel Dropbox test..."
    docker compose exec -T app php test_dropbox.php 2>&1 | tail -5
else
    echo -e "${YELLOW}⚠ test_dropbox.php not found${NC}"
fi
echo ""

echo "========================================="
echo "Diagnostic Complete"
echo "========================================="
echo ""
echo "If all tests pass but backup still fails, check:"
echo "1. Dropbox credentials in .env file"
echo "2. Application logs: storage/logs/laravel.log"
echo "3. Try running: docker compose exec app php artisan config:clear"
