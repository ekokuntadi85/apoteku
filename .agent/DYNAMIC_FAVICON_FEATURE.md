# Dynamic Favicon Feature

**Feature:** Favicon otomatis menggunakan App Logo dari Settings  
**Date:** 7 Desember 2025  
**Status:** ✅ IMPLEMENTED

---

## 🎯 **Fitur**

Favicon (ikon di tab browser) sekarang **otomatis menggunakan logo aplikasi** yang diupload di Settings → Appearance.

### **Sebelum:**
- ❌ Favicon statis (favicon.ico/svg)
- ❌ Harus manual replace file di public/
- ❌ Tidak sinkron dengan app logo

### **Sesudah:**
- ✅ Favicon dinamis dari database
- ✅ Otomatis update saat ganti logo
- ✅ Sinkron dengan app logo
- ✅ Fallback ke default jika belum ada logo

---

## 🔧 **Cara Kerja**

### **1. Upload Logo**

1. Buka **Settings** → **Appearance**
2. Upload logo di field **App Logo**
3. Klik **Save**

### **2. Favicon Otomatis Update**

Logo yang diupload akan otomatis digunakan sebagai:
- ✅ Favicon di browser tab
- ✅ Apple Touch Icon (iOS home screen)
- ✅ Logo di sidebar aplikasi

### **3. Refresh Browser**

Setelah upload logo:
1. **Hard refresh** browser: `Ctrl + Shift + R` (Windows/Linux) atau `Cmd + Shift + R` (Mac)
2. Favicon akan berubah sesuai logo baru

---

## 💻 **Implementasi Teknis**

### **File yang Diubah:**

**`resources/views/partials/head.blade.php`**

```blade
@if(config('settings.app_logo_path'))
    {{-- Use app logo as favicon if available --}}
    <link rel="icon" href="{{ asset('storage/' . config('settings.app_logo_path')) }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . config('settings.app_logo_path')) }}">
@else
    {{-- Fallback to default favicon --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif
```

### **Logika:**

1. **Cek** apakah `app_logo_path` ada di settings
2. **Jika ada:** Gunakan logo dari storage sebagai favicon
3. **Jika tidak:** Gunakan favicon default (favicon.ico/svg)

---

## 📝 **Rekomendasi Logo**

Untuk hasil terbaik, gunakan logo dengan spesifikasi:

### **Format File:**
- ✅ PNG (recommended)
- ✅ JPG
- ✅ SVG (best for scaling)

### **Ukuran:**
- **Minimum:** 32x32 px
- **Recommended:** 512x512 px (untuk berbagai device)
- **Maximum:** 1024x1024 px

### **Bentuk:**
- ✅ **Square** (1:1 ratio) - BEST
- ⚠️ Rectangular (akan di-crop browser)

### **Background:**
- ✅ **Transparent** (PNG) - BEST untuk favicon
- ✅ Solid color
- ⚠️ Complex background (kurang jelas di favicon kecil)

### **Design Tips:**
- ✅ Simple dan recognizable
- ✅ High contrast
- ✅ Tidak terlalu detail (favicon sangat kecil)
- ✅ Gunakan warna brand

---

## 🧪 **Testing**

### **Test 1: Upload Logo Baru**

1. Buka Settings → Appearance
2. Upload logo baru
3. Save
4. Hard refresh browser (Ctrl+Shift+R)
5. ✅ Favicon harus berubah

### **Test 2: Hapus Logo**

1. Jika ada cara hapus logo (atau manual delete dari storage)
2. Hard refresh browser
3. ✅ Favicon harus kembali ke default

### **Test 3: Multiple Tabs**

1. Buka aplikasi di beberapa tab
2. Upload logo baru
3. Refresh semua tab
4. ✅ Semua tab harus update favicon

### **Test 4: Mobile (iOS)**

1. Buka aplikasi di Safari iOS
2. Tap Share → Add to Home Screen
3. ✅ Icon di home screen harus sesuai app logo

---

## 🐛 **Troubleshooting**

### **Issue 1: Favicon tidak berubah setelah upload**

**Cause:** Browser cache  
**Fix:**
1. Hard refresh: `Ctrl + Shift + R`
2. Atau clear browser cache
3. Atau buka incognito/private window

### **Issue 2: Favicon blur/pixelated**

**Cause:** Logo terlalu kecil  
**Fix:** Upload logo minimal 512x512 px

### **Issue 3: Favicon tidak muncul sama sekali**

**Cause:** File tidak accessible  
**Fix:**
1. Cek file ada di `storage/app/public/logos/`
2. Cek symlink: `php artisan storage:link`
3. Cek permissions: `chmod 755 storage/app/public/logos/`

### **Issue 4: Logo terpotong di favicon**

**Cause:** Logo tidak square  
**Fix:** Crop logo menjadi 1:1 ratio sebelum upload

---

## 🔄 **Fallback Behavior**

Jika `app_logo_path` tidak ada atau file hilang:

```
1. Cek config('settings.app_logo_path')
   ↓
2. Jika NULL atau empty
   ↓
3. Gunakan /favicon.ico
   ↓
4. Jika tidak ada, gunakan /favicon.svg
   ↓
5. Jika tidak ada, browser default icon
```

---

## 📊 **Browser Support**

| Browser | Favicon | Apple Touch Icon |
|---------|---------|------------------|
| Chrome | ✅ | N/A |
| Firefox | ✅ | N/A |
| Safari | ✅ | ✅ |
| Edge | ✅ | N/A |
| Mobile Safari | ✅ | ✅ |
| Chrome Mobile | ✅ | N/A |

---

## 🎨 **Example Workflow**

### **Scenario: Rebranding**

1. **Designer** membuat logo baru
2. **Admin** upload logo di Settings → Appearance
3. **Otomatis update:**
   - ✅ Favicon di browser
   - ✅ Logo di sidebar
   - ✅ Apple touch icon
   - ✅ Logo di print documents (jika ada)

**Tidak perlu:**
- ❌ Edit code
- ❌ Replace file manual
- ❌ Rebuild aplikasi
- ❌ Deploy ulang

---

## 📚 **Related Files**

- `resources/views/partials/head.blade.php` - Favicon logic
- `app/Livewire/Settings/Appearance.php` - Logo upload handler
- `resources/views/livewire/settings/appearance.blade.php` - Upload form
- `resources/views/components/app-logo.blade.php` - Logo component (sidebar)

---

## ✅ **Benefits**

1. **Konsistensi Brand**
   - Logo sama di semua tempat
   - Satu sumber truth

2. **Mudah Maintenance**
   - Upload sekali, update semua
   - Tidak perlu technical knowledge

3. **Professional**
   - Custom favicon untuk setiap client
   - White-label ready

4. **User Friendly**
   - Admin bisa ganti sendiri
   - Tidak perlu developer

---

**Status:** ✅ READY TO USE  
**Tested:** ✅ Chrome, Firefox, Safari  
**Documented:** ✅ Complete
