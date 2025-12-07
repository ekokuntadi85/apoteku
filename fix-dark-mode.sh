#!/bin/bash

# Script untuk menambahkan dark mode classes ke file blade
# Usage: ./fix-dark-mode.sh

echo "🔧 Fixing dark mode classes in Livewire views..."

# Backup directory
BACKUP_DIR=".agent/backups/dark-mode-fix-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Find all blade files
FILES=$(find resources/views/livewire -name "*.blade.php")

for file in $FILES; do
    echo "Processing: $file"
    
    # Backup original file
    cp "$file" "$BACKUP_DIR/$(basename $file)"
    
    # Fix common patterns
    sed -i 's/bg-green-100 text-green-800"/bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"/g' "$file"
    sed -i 's/bg-yellow-100 text-yellow-800"/bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"/g' "$file"
    sed -i 's/bg-red-100 text-red-800"/bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"/g' "$file"
    sed -i 's/bg-blue-100 text-blue-800"/bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"/g' "$file"
    sed -i 's/bg-indigo-100 text-indigo-800"/bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"/g' "$file"
    
    # Fix white backgrounds
    sed -i 's/class="bg-white shadow/class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-700/g' "$file"
    sed -i 's/class="bg-white rounded/class="bg-white dark:bg-gray-800 rounded/g' "$file"
    
    # Fix text colors
    sed -i 's/text-gray-700"/text-gray-700 dark:text-gray-300"/g' "$file"
    sed -i 's/text-gray-800"/text-gray-800 dark:text-gray-200"/g' "$file"
    sed -i 's/text-gray-900"/text-gray-900 dark:text-gray-100"/g' "$file"
    
    # Fix borders
    sed -i 's/border-gray-300"/border-gray-300 dark:border-gray-600"/g' "$file"
    sed -i 's/border-gray-200"/border-gray-200 dark:border-gray-700"/g' "$file"
done

echo "✅ Done! Backups saved to: $BACKUP_DIR"
echo "⚠️  Please review changes before committing!"
