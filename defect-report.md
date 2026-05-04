# 🐞 Defect Report

## Sistem Penggajian PT Indah Permata Mandiri (IPM)
## Playwright E2E Automation Test

**Tanggal:** 4 Mei 2026
**Environment:** Laravel 11.51.0 + PHP 8.2.30 | MySQL 8.4.8 | Chromium (Playwright)

---

## Status Summary

| Severity | Count |
|----------|-------|
| Critical | 0 |
| High | 0 |
| Medium | 0 |
| Low | 0 |
| **Total** | **0** |

---

## Hasil

Tidak ditemukan defect pada eksekusi test suite E2E. Seluruh 86 test case passed.

### Defect yang Ditemukan dan Diperbaiki Sebelum Test Run

Berikut bug yang ditemukan saat persiapan test dan sudah diperbaiki:

---

### Bug ID: BUG-001 (FIXED)

- **Title:** AbsensiController index mengembalikan JSON alih-alih HTML view
- **Module:** Absensi
- **Severity:** High
- **Priority:** High
- **Environment:** Laravel 11.51.0 + PHP 8.2.30 | MySQL 8.4.8
- **Steps to Reproduce:**
  1. Login sebagai Admin
  2. Navigasi ke /admin/absensi
  3. Halaman menampilkan raw JSON response
- **Expected Result:** Halaman HTML dengan tabel data absensi dan form filter
- **Actual Result:** JSON response `{"success":true,"data":{"current_page":1,...}}`
- **Fix:** Ubah return type dari `JsonResponse` ke `View` dengan data `$absensis`, `$karyawans`, `$ptKliens`
- **Status:** Fixed

---

### Bug ID: BUG-002 (FIXED)

- **Title:** InvoiceController index tidak meneruskan ptKliens dan periodes ke view
- **Module:** Invoice
- **Severity:** Medium
- **Priority:** Medium
- **Environment:** Laravel 11.51.0 + PHP 8.2.30 | MySQL 8.4.8
- **Steps to Reproduce:**
  1. Login sebagai Admin
  2. Navigasi ke /admin/invoice
  3. Dropdown filter PT Klien dan Periode kosong
  4. Modal buat invoice tidak memiliki opsi
- **Expected Result:** Dropdown terisi dengan data PT Klien dan Periode
- **Actual Result:** Dropdown kosong karena variabel tidak diteruskan dari controller
- **Fix:** Tambahkan query `PtKlien::orderBy('nama')->get()` dan `PeriodePenggajian` ke compact
- **Status:** Fixed

---

### Bug ID: BUG-003 (FIXED)

- **Title:** View audit-log/index.blade.php tidak ada
- **Module:** Audit Log
- **Severity:** High
- **Priority:** High
- **Environment:** Laravel 11.51.0 + PHP 8.2.30
- **Steps to Reproduce:**
  1. Login sebagai Admin
  2. Navigasi ke /admin/audit-log
  3. Error 500 — View not found
- **Expected Result:** Halaman audit log dengan tabel dan filter
- **Actual Result:** 500 Internal Server Error
- **Fix:** Buat file `resources/views/admin/audit-log/index.blade.php` dengan tabel dan filter
- **Status:** Fixed
