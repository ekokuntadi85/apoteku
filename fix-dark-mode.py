#!/usr/bin/env python3
"""
Dark Mode Fixer untuk Blade Files
Menganalisis dan memperbaiki class yang belum memiliki dark mode variant
"""

import re
import os
import sys
from pathlib import Path

# Pattern yang perlu diperbaiki
PATTERNS = {
    # Badge colors
    r'bg-green-100 text-green-800"': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"',
    r'bg-yellow-100 text-yellow-800"': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"',
    r'bg-red-100 text-red-800"': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"',
    r'bg-blue-100 text-blue-800"': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"',
    r'bg-indigo-100 text-indigo-800"': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"',
    r'bg-purple-100 text-purple-800"': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"',
    r'bg-pink-100 text-pink-800"': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200"',
}

def has_dark_variant(line, bg_class):
    """Check if line already has dark: variant for the bg class"""
    # Simple check: if 'dark:' appears after the bg class in the same class attribute
    class_match = re.search(r'class="([^"]*' + re.escape(bg_class) + r'[^"]*)"', line)
    if class_match:
        class_content = class_match.group(1)
        return 'dark:' in class_content
    return False

def analyze_file(filepath):
    """Analyze file and return lines that need fixing"""
    issues = []
    
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    for line_num, line in enumerate(lines, 1):
        for pattern in PATTERNS.keys():
            # Extract just the bg class
            bg_match = re.search(r'(bg-\w+-\d+)', pattern)
            if bg_match and bg_match.group(1) in line:
                bg_class = bg_match.group(1)
                if not has_dark_variant(line, bg_class):
                    issues.append({
                        'line': line_num,
                        'content': line.strip(),
                        'pattern': pattern
                    })
    
    return issues

def fix_file(filepath, dry_run=True):
    """Fix file by adding dark mode classes"""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original_content = content
    changes_made = 0
    
    for pattern, replacement in PATTERNS.items():
        # Only replace if dark: is not already present in the same class attribute
        matches = re.finditer(r'class="([^"]*)"', content)
        for match in matches:
            class_attr = match.group(1)
            if re.search(pattern.replace('"', ''), class_attr) and 'dark:' not in class_attr:
                # Replace within this specific class attribute
                new_class_attr = re.sub(pattern.replace('"', ''), replacement.replace('"', ''), class_attr)
                content = content.replace(match.group(0), f'class="{new_class_attr}"', 1)
                changes_made += 1
    
    if changes_made > 0:
        if not dry_run:
            # Backup original
            backup_path = f"{filepath}.backup"
            with open(backup_path, 'w', encoding='utf-8') as f:
                f.write(original_content)
            
            # Write fixed content
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            
            print(f"✅ Fixed {filepath} ({changes_made} changes)")
        else:
            print(f"🔍 Would fix {filepath} ({changes_made} changes)")
    
    return changes_made

def main():
    views_dir = Path("resources/views/livewire")
    
    if not views_dir.exists():
        print(f"❌ Directory not found: {views_dir}")
        return
    
    print("🔍 Analyzing Blade files for dark mode issues...\n")
    
    blade_files = list(views_dir.glob("*.blade.php"))
    total_issues = 0
    files_with_issues = []
    
    for filepath in blade_files:
        issues = analyze_file(filepath)
        if issues:
            total_issues += len(issues)
            files_with_issues.append((filepath, issues))
    
    if total_issues == 0:
        print("✅ All files are already consistent with dark mode!")
        return
    
    print(f"Found {total_issues} potential issues in {len(files_with_issues)} files\n")
    
    # Show summary
    for filepath, issues in files_with_issues[:5]:  # Show first 5
        print(f"\n📄 {filepath.name}:")
        for issue in issues[:3]:  # Show first 3 issues per file
            print(f"   Line {issue['line']}: {issue['content'][:80]}...")
    
    if len(files_with_issues) > 5:
        print(f"\n... and {len(files_with_issues) - 5} more files")
    
    # Ask for confirmation
    print("\n" + "="*60)
    response = input("Do you want to fix these files? (yes/no): ")
    
    if response.lower() in ['yes', 'y']:
        print("\n🔧 Fixing files...\n")
        total_changes = 0
        for filepath, _ in files_with_issues:
            changes = fix_file(filepath, dry_run=False)
            total_changes += changes
        
        print(f"\n✅ Done! Made {total_changes} changes across {len(files_with_issues)} files")
        print("⚠️  Backups saved with .backup extension")
        print("💡 Please review changes before committing!")
    else:
        print("\n❌ Cancelled. No changes made.")

if __name__ == "__main__":
    main()
