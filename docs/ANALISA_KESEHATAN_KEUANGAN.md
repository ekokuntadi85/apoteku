# Panduan Analisa Kesehatan Keuangan Apotek

## 📊 Step-by-Step Financial Health Analysis

### STEP 1: Kumpulkan Data Dasar (1 Bulan Terakhir)

#### A. Laporan Laba Rugi (Income Statement)
**Menu:** Keuangan > Laporan Laba Rugi

**Data yang diambil:**
```
Periode: [Pilih bulan terakhir, misal: Desember 2024]

1. Pendapatan (Revenue)           : Rp _____________
2. HPP (Cost of Goods Sold)       : Rp _____________
3. Laba Kotor (Gross Profit)      : Rp _____________
4. Beban Operasional:
   - Gaji Karyawan                : Rp _____________
   - Listrik & Air                : Rp _____________
   - Sewa Tempat                  : Rp _____________
   - Perlengkapan                 : Rp _____________
   - Penyusutan                   : Rp _____________
   - Lain-lain                    : Rp _____________
   Total Beban Operasional        : Rp _____________
5. Laba Bersih (Net Profit)       : Rp _____________
```

#### B. Laporan Neraca (Balance Sheet)
**Menu:** Keuangan > Laporan Neraca

**Data yang diambil:**
```
Per tanggal: [Akhir bulan, misal: 31 Desember 2024]

ASET (ASSETS):
1. Kas (Cash)                     : Rp _____________
2. Piutang Usaha (AR)             : Rp _____________
3. Persediaan (Inventory)         : Rp _____________
   Total Aset Lancar              : Rp _____________

KEWAJIBAN (LIABILITIES):
4. Hutang Usaha (AP)              : Rp _____________
   Total Kewajiban                : Rp _____________

EKUITAS (EQUITY):
5. Modal                          : Rp _____________
6. Laba Ditahan                   : Rp _____________
   Total Ekuitas                  : Rp _____________

TOTAL ASET = KEWAJIBAN + EKUITAS  : Rp _____________
```

#### C. Data Tambahan dari Buku Besar
**Menu:** Keuangan > Buku Besar

**Cek akun-akun berikut (1 bulan terakhir):**
```
1. Akun 101 (Kas):
   - Saldo Awal                   : Rp _____________
   - Total Debit (Masuk)          : Rp _____________
   - Total Kredit (Keluar)        : Rp _____________
   - Saldo Akhir                  : Rp _____________

2. Akun 103 (Piutang Usaha):
   - Jumlah transaksi kredit      : _____ transaksi
   - Total piutang                : Rp _____________

3. Akun 201 (Hutang Usaha):
   - Jumlah pembelian kredit      : _____ transaksi
   - Total hutang                 : Rp _____________
```

---

## 📈 STEP 2: Hitung Rasio Keuangan Kunci

### A. Rasio Profitabilitas

#### 1. **Gross Profit Margin (Margin Laba Kotor)**
```
Formula: (Laba Kotor / Pendapatan) × 100%

Contoh:
Laba Kotor: Rp 50,000,000
Pendapatan: Rp 150,000,000
GPM = (50,000,000 / 150,000,000) × 100% = 33.3%

Interpretasi:
- > 40%  : ✅ Sangat Baik (markup tinggi)
- 30-40% : ✅ Baik (standar apotek)
- 20-30% : ⚠️ Cukup (perlu review harga)
- < 20%  : ❌ Buruk (harga terlalu murah)
```

#### 2. **Net Profit Margin (Margin Laba Bersih)**
```
Formula: (Laba Bersih / Pendapatan) × 100%

Contoh:
Laba Bersih: Rp 15,000,000
Pendapatan: Rp 150,000,000
NPM = (15,000,000 / 150,000,000) × 100% = 10%

Interpretasi:
- > 15%  : ✅ Sangat Baik
- 10-15% : ✅ Baik (standar apotek)
- 5-10%  : ⚠️ Cukup (efisiensi perlu ditingkatkan)
- < 5%   : ❌ Buruk (hampir tidak untung)
```

#### 3. **Operating Expense Ratio (Rasio Beban Operasional)**
```
Formula: (Total Beban Operasional / Pendapatan) × 100%

Contoh:
Beban Operasional: Rp 35,000,000
Pendapatan: Rp 150,000,000
OER = (35,000,000 / 150,000,000) × 100% = 23.3%

Interpretasi:
- < 20%  : ✅ Sangat Efisien
- 20-30% : ✅ Efisien (standar apotek)
- 30-40% : ⚠️ Kurang Efisien (banyak pengeluaran)
- > 40%  : ❌ Tidak Efisien (perlu cost cutting)
```

### B. Rasio Likuiditas (Kemampuan Bayar Hutang)

#### 4. **Current Ratio (Rasio Lancar)**
```
Formula: Aset Lancar / Kewajiban Lancar

Contoh:
Aset Lancar (Kas + Piutang + Inventory): Rp 200,000,000
Kewajiban (Hutang Usaha): Rp 50,000,000
Current Ratio = 200,000,000 / 50,000,000 = 4.0

Interpretasi:
- > 2.0  : ✅ Sangat Sehat (bisa bayar hutang 2x lipat)
- 1.5-2.0: ✅ Sehat
- 1.0-1.5: ⚠️ Cukup (hati-hati)
- < 1.0  : ❌ Bahaya (aset < hutang)
```

#### 5. **Cash Ratio (Rasio Kas)**
```
Formula: Kas / Kewajiban Lancar

Contoh:
Kas: Rp 30,000,000
Hutang Usaha: Rp 50,000,000
Cash Ratio = 30,000,000 / 50,000,000 = 0.6

Interpretasi:
- > 1.0  : ✅ Sangat Likuid (kas cukup bayar semua hutang)
- 0.5-1.0: ✅ Likuid
- 0.3-0.5: ⚠️ Cukup (perlu monitor cash flow)
- < 0.3  : ❌ Kurang Likuid (risiko gagal bayar)
```

### C. Rasio Efisiensi

#### 6. **Inventory Turnover (Perputaran Persediaan)**
```
Formula: HPP / Rata-rata Persediaan

Contoh:
HPP (1 bulan): Rp 100,000,000
Persediaan: Rp 80,000,000
Turnover = 100,000,000 / 80,000,000 = 1.25x per bulan
Atau: 1.25 × 12 = 15x per tahun

Interpretasi:
- > 12x/tahun : ✅ Sangat Baik (inventory cepat terjual)
- 8-12x/tahun : ✅ Baik
- 4-8x/tahun  : ⚠️ Cukup (ada slow moving items)
- < 4x/tahun  : ❌ Buruk (banyak dead stock)
```

#### 7. **Days Sales Outstanding (DSO) - Rata-rata Piutang Tertagih**
```
Formula: (Piutang Usaha / Pendapatan) × 30 hari

Contoh:
Piutang: Rp 20,000,000
Pendapatan (1 bulan): Rp 150,000,000
DSO = (20,000,000 / 150,000,000) × 30 = 4 hari

Interpretasi:
- < 7 hari   : ✅ Sangat Baik (piutang cepat tertagih)
- 7-14 hari  : ✅ Baik
- 14-30 hari : ⚠️ Cukup (perlu follow up)
- > 30 hari  : ❌ Buruk (banyak piutang macet)
```

---

## 🎯 STEP 3: Analisa Trend (Bandingkan 3 Bulan Terakhir)

### Tabel Perbandingan:
```
                        Bulan 1    Bulan 2    Bulan 3    Trend
Pendapatan              _______    _______    _______    📈/📉
Laba Bersih             _______    _______    _______    📈/📉
Gross Profit Margin     _____%     _____%     _____%     📈/📉
Net Profit Margin       _____%     _____%     _____%     📈/📉
Kas                     _______    _______    _______    📈/📉
Piutang                 _______    _______    _______    📈/📉
Hutang                  _______    _______    _______    📈/📉
```

**Analisa Trend:**
- 📈 **Naik** = Positif (untuk pendapatan, laba, kas)
- 📉 **Turun** = Negatif (untuk pendapatan, laba, kas)
- 📉 **Turun** = Positif (untuk piutang, hutang - artinya berkurang)

---

## 📋 STEP 4: Kesimpulan & Rekomendasi

### Template Kesimpulan:

```
KESIMPULAN KESEHATAN KEUANGAN APOTEK [NAMA]
Periode: [Bulan/Tahun]

A. PROFITABILITAS: [✅ Baik / ⚠️ Cukup / ❌ Buruk]
   - Gross Profit Margin: ____%
   - Net Profit Margin: ____%
   - Kesimpulan: _________________________________

B. LIKUIDITAS: [✅ Sehat / ⚠️ Cukup / ❌ Bahaya]
   - Current Ratio: ____
   - Cash Ratio: ____
   - Kesimpulan: _________________________________

C. EFISIENSI OPERASIONAL: [✅ Efisien / ⚠️ Cukup / ❌ Tidak Efisien]
   - Operating Expense Ratio: ____%
   - Inventory Turnover: ____x/tahun
   - DSO: ____ hari
   - Kesimpulan: _________________________________

D. TREND: [📈 Positif / ➡️ Stabil / 📉 Negatif]
   - Pendapatan: [naik/turun] ____%
   - Laba Bersih: [naik/turun] ____%
   - Kesimpulan: _________________________________

E. OVERALL HEALTH SCORE: [A/B/C/D/F]
   - A (90-100): Sangat Sehat
   - B (80-89):  Sehat
   - C (70-79):  Cukup Sehat
   - D (60-69):  Kurang Sehat
   - F (<60):    Tidak Sehat

F. REKOMENDASI:
   1. ___________________________________________
   2. ___________________________________________
   3. ___________________________________________
```

---

## 🚨 Red Flags (Tanda Bahaya)

Segera ambil tindakan jika:
- ❌ Net Profit Margin < 5% (hampir tidak untung)
- ❌ Current Ratio < 1.0 (hutang > aset)
- ❌ Cash Ratio < 0.3 (kas sangat minim)
- ❌ Pendapatan turun 3 bulan berturut-turut
- ❌ Laba Bersih negatif (rugi)
- ❌ Piutang > 30% dari pendapatan (banyak kredit)
- ❌ Inventory Turnover < 4x/tahun (dead stock)

---

## 💡 Tips Meningkatkan Kesehatan Keuangan

### 1. Meningkatkan Profitabilitas:
- ✅ Review harga jual (pastikan markup 30-40%)
- ✅ Fokus jual produk margin tinggi
- ✅ Kurangi diskon berlebihan
- ✅ Negosiasi harga beli lebih rendah

### 2. Meningkatkan Likuiditas:
- ✅ Percepat penagihan piutang
- ✅ Kurangi pembelian kredit
- ✅ Jual slow-moving items (diskon)
- ✅ Kontrol pengeluaran operasional

### 3. Meningkatkan Efisiensi:
- ✅ Otomasi proses (kurangi manual work)
- ✅ Negosiasi sewa/listrik
- ✅ Optimalkan jumlah karyawan
- ✅ Kurangi waste/expired items

---

## 📞 Kapan Konsultasi Akuntan?

Hubungi akuntan profesional jika:
- Overall Health Score = D atau F
- Rugi 2 bulan berturut-turut
- Current Ratio < 1.0
- Tidak paham cara membaca laporan
- Mau ekspansi/pinjam bank (butuh audit)

---

## 🔄 Frekuensi Monitoring

- **Harian:** Cek saldo kas (via Buku Besar akun 101)
- **Mingguan:** Cek pendapatan & laba kotor
- **Bulanan:** Analisa lengkap (semua rasio)
- **Triwulanan:** Review trend & strategi
- **Tahunan:** Audit & perencanaan tahun depan

---

## 📊 Contoh Kasus Nyata

### Apotek A (Sehat):
```
Pendapatan:        Rp 150,000,000
Laba Bersih:       Rp  18,000,000
GPM:               35%
NPM:               12%
Current Ratio:     2.5
Cash Ratio:        0.8
Inventory Turnover: 10x/tahun
DSO:               5 hari

Kesimpulan: ✅ SEHAT
- Profitabilitas baik
- Likuiditas sangat sehat
- Efisiensi operasional baik
- Trend positif

Rekomendasi: Pertahankan, pertimbangkan ekspansi
```

### Apotek B (Kurang Sehat):
```
Pendapatan:        Rp 100,000,000
Laba Bersih:       Rp   3,000,000
GPM:               25%
NPM:               3%
Current Ratio:     0.9
Cash Ratio:        0.2
Inventory Turnover: 3x/tahun
DSO:               45 hari

Kesimpulan: ⚠️ KURANG SEHAT
- Profitabilitas rendah (NPM 3%)
- Likuiditas bahaya (CR < 1.0)
- Banyak dead stock
- Piutang macet

Rekomendasi URGENT:
1. Naikkan harga jual (target GPM 35%)
2. Tagih piutang agresif (target DSO < 14 hari)
3. Clearance sale slow-moving items
4. Kurangi pembelian kredit
5. Review & potong biaya operasional
```

---

**MULAI ANALISA ANDA SEKARANG!**

Gunakan template di atas, isi dengan data dari sistem, dan dapatkan insight kesehatan keuangan apotek Anda! 🚀
