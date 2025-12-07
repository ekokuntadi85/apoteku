# Dark Mode Toggle Fix - Troubleshooting

**Issue:** Theme toggle di Settings → Appearance tidak berfungsi  
**Date:** 7 Desember 2025  
**Status:** ✅ FIXED

---

## 🐛 **Masalah**

Tombol theme (Light/Dark/System) di halaman `http://localhost/settings/appearance` tidak berfungsi. Klik pada tombol tidak mengubah tema aplikasi.

---

## 🔍 **Root Cause**

**Tailwind CSS tidak dikonfigurasi untuk dark mode!**

File `tailwind.config.js` tidak memiliki setting `darkMode: 'class'` yang diperlukan oleh Flux UI untuk mengaktifkan dark mode dengan class strategy.

### **Kode Bermasalah:**
```javascript
// tailwind.config.js (SEBELUM)
export default defineConfig({
  content: [...],
  theme: {  // ❌ Tidak ada darkMode config!
    extend: {...},
  },
  plugins: [],
})
```

---

## ✅ **Solusi**

### **1. Update Tailwind Config**

Tambahkan `darkMode: 'class'` ke `tailwind.config.js`:

```javascript
// tailwind.config.js (SESUDAH)
export default defineConfig({
  content: [...],
  darkMode: 'class', // ✅ Tambahkan ini!
  theme: {
    extend: {...},
  },
  plugins: [],
})
```

### **2. Rebuild CSS**

Setelah mengubah config, rebuild CSS:

```bash
docker compose exec app npm run build
```

Atau untuk development:
```bash
docker compose exec app npm run dev
```

---

## 🎯 **Cara Kerja Dark Mode**

### **1. Flux UI Magic Property**

Flux menggunakan Alpine.js magic property `$flux.appearance`:

```html
<flux:radio.group x-model="$flux.appearance">
    <flux:radio value="light" icon="sun">Light</flux:radio>
    <flux:radio value="dark" icon="moon">Dark</flux:radio>
    <flux:radio value="system" icon="computer-desktop">System</flux:radio>
</flux:radio.group>
```

### **2. Class Strategy**

Ketika user memilih dark mode, Flux akan:
1. Simpan pilihan ke `localStorage`
2. Tambahkan class `dark` ke `<html>` tag
3. Tailwind akan apply semua class `dark:*`

```html
<!-- Light Mode -->
<html>
  <body class="bg-white">...</body>
</html>

<!-- Dark Mode -->
<html class="dark">
  <body class="bg-white dark:bg-gray-800">...</body>
</html>
```

### **3. Tailwind Dark Mode**

Dengan `darkMode: 'class'`, Tailwind akan generate CSS:

```css
/* Light mode */
.bg-white {
  background-color: #ffffff;
}

/* Dark mode */
.dark .dark\:bg-gray-800 {
  background-color: #1f2937;
}
```

---

## 🧪 **Testing**

### **Manual Test:**

1. Buka browser DevTools (F12)
2. Buka Settings → Appearance
3. Klik tombol **Dark**
4. Inspect `<html>` tag - harus ada class `dark`
5. Cek localStorage: `localStorage.getItem('flux_appearance')` harus return `"dark"`

### **Expected Behavior:**

✅ Klik "Light" → Background putih, text hitam  
✅ Klik "Dark" → Background gelap, text putih  
✅ Klik "System" → Ikuti OS setting  
✅ Refresh page → Setting tetap tersimpan  

---

## 📝 **Checklist Troubleshooting**

Jika dark mode masih tidak berfungsi, cek:

- [ ] `tailwind.config.js` ada `darkMode: 'class'`
- [ ] CSS sudah di-rebuild (`npm run build`)
- [ ] Browser cache sudah di-clear (Ctrl+Shift+R)
- [ ] `@fluxAppearance` directive ada di layout
- [ ] Flux scripts loaded (`@fluxScripts`)
- [ ] Alpine.js loaded (cek console errors)

---

## 🔧 **Common Issues**

### **Issue 1: Toggle bekerja tapi style tidak berubah**

**Cause:** CSS belum di-rebuild  
**Fix:** `npm run build`

### **Issue 2: Class `dark` muncul tapi style masih terang**

**Cause:** Class `dark:*` tidak ada di element  
**Fix:** Tambahkan class dark variant ke element

### **Issue 3: Setting tidak tersimpan setelah refresh**

**Cause:** localStorage blocked atau Flux scripts tidak load  
**Fix:** Cek browser console untuk errors

---

## 📚 **Reference**

### **Tailwind Dark Mode Docs:**
https://tailwindcss.com/docs/dark-mode

### **Flux UI Docs:**
https://flux.laravel.com/docs/appearance

### **Alpine.js Docs:**
https://alpinejs.dev/

---

## ✅ **Verification**

Setelah fix, verifikasi dengan:

```bash
# 1. Cek Tailwind config
cat tailwind.config.js | grep darkMode

# Expected output:
# darkMode: 'class',

# 2. Cek build output
docker compose exec app npm run build

# Expected: No errors

# 3. Test di browser
# - Buka http://localhost/settings/appearance
# - Klik Dark
# - Inspect <html> tag
# - Should have class="dark"
```

---

**Status:** ✅ RESOLVED  
**Fixed in commit:** [Current commit]  
**Branch:** feature/new
