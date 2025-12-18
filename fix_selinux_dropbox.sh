#!/bin/bash

# SELinux Fix for Dropbox Connectivity
# This allows PHP-FPM to make outbound HTTPS connections

echo "========================================="
echo "Fixing SELinux for Dropbox Connectivity"
echo "========================================="
echo ""

# Check if SELinux is enforcing
SELINUX_STATUS=$(getenforce 2>/dev/null)
if [ "$SELINUX_STATUS" != "Enforcing" ]; then
    echo "SELinux is not enforcing. No fix needed."
    exit 0
fi

echo "SELinux Status: $SELINUX_STATUS"
echo ""

# The issue: SELinux blocks httpd/php-fpm from making network connections
# Solution: Enable httpd_can_network_connect boolean

echo "Checking current httpd_can_network_connect status..."
CURRENT_STATUS=$(getsebool httpd_can_network_connect 2>/dev/null | awk '{print $3}')
echo "Current: $CURRENT_STATUS"
echo ""

if [ "$CURRENT_STATUS" = "on" ]; then
    echo "✓ httpd_can_network_connect is already enabled!"
    echo "  SELinux should not be blocking connections."
else
    echo "Enabling httpd_can_network_connect..."
    echo ""
    
    # Enable permanently
    sudo setsebool -P httpd_can_network_connect 1
    
    if [ $? -eq 0 ]; then
        echo "✓ Successfully enabled httpd_can_network_connect"
        echo ""
        echo "This allows PHP-FPM/Apache to:"
        echo "  - Make outbound HTTPS connections"
        echo "  - Connect to external APIs (like Dropbox)"
        echo "  - Access remote services"
        echo ""
        echo "========================================="
        echo "Fix Applied Successfully!"
        echo "========================================="
        echo ""
        echo "Next steps:"
        echo "1. Test backup from your application"
        echo "2. Should now see: ✅ Backup berhasil dibuat dan diupload ke Dropbox"
    else
        echo "✗ Failed to enable httpd_can_network_connect"
        echo "  Make sure you have sudo privileges"
        exit 1
    fi
fi

echo ""
echo "Verifying fix..."
echo "Testing connection to Dropbox..."

# Test with curl
if curl -s --connect-timeout 5 https://api.dropbox.com/ > /dev/null 2>&1; then
    echo "✓ Connection to Dropbox successful!"
else
    echo "⚠ Connection test inconclusive"
    echo "  Try from your application"
fi

echo ""
echo "Done!"
