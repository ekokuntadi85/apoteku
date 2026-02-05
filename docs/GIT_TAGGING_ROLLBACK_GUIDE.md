# Git Tagging Strategy & Rollback Guide

**Date:** 2025-12-17  
**Branch:** main  
**Latest Release:** v1.1.0-finance

---

## 📌 TAG OVERVIEW

Sistem ini menggunakan **semantic versioning** dengan tag khusus untuk memudahkan tracking dan rollback.

### Current Tags:

| Tag | Type | Description | Date | Purpose |
|-----|------|-------------|------|---------|
| `v1.0.0-pre-finance` | Backup | State sebelum merge finance module | 2025-12-17 | Rollback point |
| `v1.0.0-finance` | Release | Finance module initial release (di feature/new) | 2025-12-15 | Historical |
| `v1.1.0-finance` | Release | Finance module + bug fixes (merged to main) | 2025-12-17 | **CURRENT** |

---

## 🏷️ TAGGING CONVENTION

### Format:
```
v{MAJOR}.{MINOR}.{PATCH}-{LABEL}
```

### Examples:
- `v1.0.0-finance` - Major release dengan finance module
- `v1.1.0-finance` - Minor update dengan bug fixes
- `v1.0.0-pre-finance` - Backup sebelum merge
- `v2.0.0-inventory` - Future: Major inventory update

### Labels:
- `pre-{feature}` - Backup sebelum merge feature besar
- `{feature}` - Release dengan feature utama
- `hotfix` - Emergency bug fix
- `stable` - Stable production version

---

## 📋 CARA MELIHAT TAG

### List All Tags:
```bash
git tag -l
```

### List Tags dengan Detail:
```bash
git tag -l -n5
```

### Show Tag Detail:
```bash
git show v1.1.0-finance
```

### List Tags by Pattern:
```bash
git tag -l "v1.*-finance"
```

---

## 🔙 ROLLBACK GUIDE

### Scenario 1: Rollback ke State Sebelum Finance Module

Jika ada masalah setelah deploy v1.1.0-finance:

```bash
# 1. Checkout ke tag backup
git checkout v1.0.0-pre-finance

# 2. Buat branch baru dari tag
git checkout -b rollback-pre-finance

# 3. Restore database dari backup
docker compose exec -T db mysql -u root -p apoteku < backup_before_finance.sql

# 4. Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# 5. Test aplikasi
# Jika OK, push branch rollback ke main
git checkout main
git reset --hard v1.0.0-pre-finance
git push origin main --force  # HATI-HATI!
```

### Scenario 2: Rollback Migration Saja

Jika hanya ingin rollback migration tanpa rollback code:

```bash
# Rollback 8 migrations dari finance module
docker compose exec app php artisan migrate:rollback --step=8

# Verify
docker compose exec app php artisan migrate:status
```

### Scenario 3: Partial Rollback (Revert Specific Commit)

Jika hanya ingin revert commit tertentu:

```bash
# Find commit hash
git log --oneline -10

# Revert specific commit
git revert <commit-hash>

# Push
git push origin main
```

---

## 🚀 DEPLOYMENT DENGAN TAG

### Deploy Specific Version:

```bash
# 1. Checkout ke tag yang diinginkan
git checkout v1.1.0-finance

# 2. Deploy
docker compose exec app composer install --no-dev
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize

# 3. Verify version
git describe --tags
```

### Deploy Latest:

```bash
# 1. Pull latest
git checkout main
git pull origin main

# 2. Check current tag
git describe --tags --abbrev=0

# 3. Deploy
# ... deployment steps ...
```

---

## 📊 TAG COMPARISON

### Compare Two Tags:

```bash
# See differences between tags
git diff v1.0.0-pre-finance..v1.1.0-finance --stat

# See commit log between tags
git log v1.0.0-pre-finance..v1.1.0-finance --oneline

# See file changes
git diff v1.0.0-pre-finance..v1.1.0-finance -- path/to/file
```

---

## 🔍 TROUBLESHOOTING

### Tag Not Found:

```bash
# Fetch tags from remote
git fetch --tags

# List remote tags
git ls-remote --tags origin
```

### Delete Wrong Tag:

```bash
# Delete local tag
git tag -d v1.1.0-finance

# Delete remote tag
git push origin :refs/tags/v1.1.0-finance

# Or
git push origin --delete v1.1.0-finance
```

### Recreate Tag:

```bash
# Delete old tag
git tag -d v1.1.0-finance
git push origin :refs/tags/v1.1.0-finance

# Create new tag
git tag -a v1.1.0-finance -m "New message"
git push origin v1.1.0-finance
```

---

## 📝 BEST PRACTICES

### 1. Always Create Backup Tag Before Major Merge

```bash
# Before merging feature/new
git checkout main
git tag -a v1.0.0-pre-{feature} -m "Backup before {feature}"
git push origin v1.0.0-pre-{feature}
```

### 2. Create Release Tag After Successful Merge

```bash
# After merging and testing
git tag -a v1.1.0-{feature} -m "Release v1.1.0 - {feature}"
git push origin v1.1.0-{feature}
```

### 3. Document Tag in CHANGELOG

Update `CHANGELOG.md` dengan informasi tag:

```markdown
## [v1.1.0-finance] - 2025-12-17

### Added
- Finance module
- ...

### Fixed
- Journal deletion bug
- ...
```

### 4. Use Annotated Tags (Not Lightweight)

```bash
# ✅ Good: Annotated tag
git tag -a v1.1.0 -m "Release message"

# ❌ Bad: Lightweight tag
git tag v1.1.0
```

### 5. Push Tags Explicitly

```bash
# Push single tag
git push origin v1.1.0-finance

# Push all tags
git push origin --tags
```

---

## 🎯 QUICK REFERENCE

### Common Commands:

```bash
# Create backup tag
git tag -a v1.0.0-pre-feature -m "Backup before feature"

# Create release tag
git tag -a v1.1.0-feature -m "Release v1.1.0"

# Push tags
git push origin --tags

# List tags
git tag -l

# Checkout tag
git checkout v1.1.0-finance

# Delete tag
git tag -d v1.1.0-finance
git push origin :refs/tags/v1.1.0-finance

# Compare tags
git diff v1.0.0..v1.1.0 --stat
```

---

## 📞 EMERGENCY ROLLBACK

Jika production down dan perlu rollback CEPAT:

```bash
# 1. Checkout ke backup tag
git checkout v1.0.0-pre-finance

# 2. Force push ke main (EMERGENCY ONLY!)
git checkout main
git reset --hard v1.0.0-pre-finance
git push origin main --force

# 3. Restore database
docker compose exec -T db mysql -u root -p apoteku < backup_before_finance.sql

# 4. Clear cache
docker compose exec app php artisan optimize:clear

# 5. Restart services
docker compose restart app

# Total time: ~5 minutes
```

**⚠️ WARNING:** `git push --force` akan menghapus history! Hanya gunakan untuk emergency!

---

## ✅ VERIFICATION CHECKLIST

After rollback:

- [ ] Application accessible
- [ ] Database restored correctly
- [ ] No errors in logs
- [ ] Test critical features
- [ ] Inform users
- [ ] Document incident
- [ ] Plan fix for next deployment

---

## 📚 REFERENCES

- Git Tagging: https://git-scm.com/book/en/v2/Git-Basics-Tagging
- Semantic Versioning: https://semver.org/
- Rollback Strategies: docs/DEPLOYMENT_FEATURE_NEW.md

---

**Last Updated:** 2025-12-17  
**Maintainer:** Development Team  
**Status:** Active
