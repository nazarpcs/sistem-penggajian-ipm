# 🧪 Test Execution Report

## Sistem Penggajian PT Indah Permata Mandiri (IPM)
## Playwright E2E Automation Test

**Tanggal Eksekusi:** 4 Mei 2026
**Environment:** Laravel 11.51.0 + PHP 8.2.30 | MySQL 8.4.8 | Chromium (Playwright headed)
**Tester:** Senior QA Automation Engineer (Agent)
**Tool:** Playwright v1.52+ | Headed mode (browser visible)
**Durasi:** 7.8 menit

---

## Test Summary

| Metrik | Jumlah |
|--------|--------|
| Total Test Case | 86 |
| ✅ Passed | 86 |
| ❌ Failed | 0 |
| ⏭️ Skipped | 0 |
| Pass Rate | 100% |

---

## 📊 Execution Table

| TC_ID | Module | Scenario | Status | Time |
|-------|--------|----------|--------|------|
| TC_ABSENSI_001 | Absensi | Halaman absensi dapat diakses | ✅ | 4.1s |
| TC_ABSENSI_002 | Absensi | Form input manual absensi dapat dibuka | ✅ | 4.8s |
| TC_ABSENSI_003 | Absensi | Input absensi manual berhasil | ✅ | 6.8s |
| TC_ABSENSI_004 | Absensi | Filter absensi berdasarkan status kehadiran | ✅ | 6.0s |
| TC_ABSENSI_005 | Absensi | Halaman rekap absensi dapat diakses | ✅ | 4.9s |
| TC_ABSENSI_006 | Absensi | Input absensi gagal tanpa memilih karyawan | ✅ | 5.3s |
| TC_ABSENSI_007 | Absensi | Input absensi gagal tanpa tanggal | ✅ | 5.0s |
| TC_ABSENSI_008 | Absensi | Input absensi dengan status Alpha tanpa jam | ✅ | 7.0s |
| TC_AUDIT_001 | Audit | Halaman audit log dapat diakses oleh Admin | ✅ | 4.7s |
| TC_AUDIT_002 | Audit | Audit log menampilkan tabel data | ✅ | 4.9s |
| TC_AUDIT_003 | Audit | Login activity tercatat di audit log | ✅ | 3.6s |
| TC_AUDIT_004 | Audit | Pemilik PT tidak dapat mengakses audit log | ✅ | 4.9s |
| TC_AUDIT_005 | Audit | Karyawan tidak dapat mengakses audit log | ✅ | 4.5s |
| TC_AUTH_001 | Auth | Halaman login menampilkan form email dan password | ✅ | 2.0s |
| TC_AUTH_002 | Auth | Admin berhasil login dengan kredensial valid | ✅ | 4.1s |
| TC_AUTH_003 | Auth | Pemilik PT berhasil login dengan kredensial valid | ✅ | 4.2s |
| TC_AUTH_004 | Auth | Karyawan berhasil login dengan kredensial valid | ✅ | 3.8s |
| TC_AUTH_005 | Auth | Logout berhasil menghapus sesi dan redirect ke login | ✅ | 8.0s |
| TC_AUTH_006 | Auth | Login gagal dengan email salah | ✅ | 3.8s |
| TC_AUTH_007 | Auth | Login gagal dengan password salah | ✅ | 4.5s |
| TC_AUTH_008 | Auth | Login gagal dengan email kosong | ✅ | 2.4s |
| TC_AUTH_009 | Auth | Login gagal dengan password kosong | ✅ | 2.2s |
| TC_AUTH_010 | Auth | SQL Injection pada field email ditolak | ✅ | 2.2s |
| TC_AUTH_011 | Auth | XSS payload pada field email ditolak | ✅ | 2.6s |
| TC_AUTH_012 | Auth | Halaman forgot password dapat diakses | ✅ | 3.8s |
| TC_AUTH_013 | Auth | Login dengan spasi di email | ✅ | 5.2s |
| TC_AUTH_014 | Auth | Akses dashboard tanpa login redirect ke login | ✅ | 4.6s |
| TC_DASHBOARD_001 | Dashboard | Dashboard admin menampilkan stat cards | ✅ | 6.4s |
| TC_DASHBOARD_002 | Dashboard | Dashboard admin menampilkan tabel invoice pending | ✅ | 5.4s |
| TC_DASHBOARD_003 | Dashboard | Laporan absensi admin dapat diakses | ✅ | 4.1s |
| TC_DASHBOARD_004 | Dashboard | Laporan penggajian admin dapat diakses | ✅ | 5.2s |
| TC_DASHBOARD_005 | Dashboard | Dashboard owner dapat diakses | ✅ | 4.4s |
| TC_DASHBOARD_006 | Dashboard | Owner dapat mengakses laporan | ✅ | 4.8s |
| TC_INVOICE_001 | Invoice | Halaman invoice admin dapat diakses | ✅ | 4.0s |
| TC_INVOICE_002 | Invoice | Modal buat invoice dapat dibuka | ✅ | 7.0s |
| TC_INVOICE_003 | Invoice | Filter invoice berdasarkan status | ✅ | 6.4s |
| TC_INVOICE_004 | Invoice | Laporan invoice dapat diakses | ✅ | 6.1s |
| TC_INVOICE_005 | Invoice | Pemilik PT dapat melihat daftar invoice | ✅ | 6.5s |
| TC_INVOICE_006 | Invoice | Pemilik PT dapat melihat detail invoice | ✅ | 4.0s |
| TC_INVOICE_007 | Invoice | Reject invoice tanpa alasan gagal | ✅ | 3.8s |
| TC_KARYAWAN_001 | Karyawan | Halaman daftar karyawan dapat diakses | ✅ | 5.5s |
| TC_KARYAWAN_002 | Karyawan | Form tambah karyawan dapat diakses | ✅ | 7.0s |
| TC_KARYAWAN_003 | Karyawan | Tambah karyawan baru berhasil | ✅ | 8.5s |
| TC_KARYAWAN_004 | Karyawan | Daftar karyawan menampilkan data | ✅ | 7.7s |
| TC_KARYAWAN_005 | Karyawan | Tambah karyawan gagal tanpa nama lengkap | ✅ | 7.3s |
| TC_KARYAWAN_006 | Karyawan | Tambah karyawan gagal dengan email invalid | ✅ | 7.8s |
| TC_KARYAWAN_007 | Karyawan | Tambah karyawan dengan gaji pokok 0 | ✅ | 9.0s |
| TC_KARYAWAN_008 | Karyawan | Filter karyawan berdasarkan PT Klien | ✅ | 6.7s |
| TC_PAYROLL_001 | Payroll | Halaman penggajian dapat diakses | ✅ | — |
| TC_PAYROLL_002 | Payroll | Halaman slip gaji admin menampilkan tabel | ✅ | — |
| TC_PAYROLL_003 | Payroll | Halaman laporan penggajian dapat diakses | ✅ | — |
| TC_PAYROLL_004 | Payroll | Karyawan dapat mengakses halaman slip gaji | ✅ | — |
| TC_PAYROLL_005 | Payroll | Karyawan melihat tabel slip gaji dengan kolom lengkap | ✅ | — |
| TC_PTKLIEN_001 | PT Klien | Halaman daftar PT Klien dapat diakses | ✅ | — |
| TC_PTKLIEN_002 | PT Klien | Form tambah PT Klien dapat diakses | ✅ | — |
| TC_PTKLIEN_003 | PT Klien | Daftar PT Klien menampilkan data | ✅ | — |
| TC_PTKLIEN_004 | PT Klien | Detail PT Klien dapat diakses | ✅ | — |
| TC_RBAC_001 | RBAC | Admin dapat mengakses dashboard admin | ✅ | — |
| TC_RBAC_002 | RBAC | Admin dapat mengakses halaman karyawan | ✅ | — |
| TC_RBAC_003 | RBAC | Admin dapat mengakses halaman PT Klien | ✅ | — |
| TC_RBAC_004 | RBAC | Admin dapat mengakses halaman absensi | ✅ | — |
| TC_RBAC_005 | RBAC | Admin dapat mengakses halaman invoice | ✅ | — |
| TC_RBAC_006 | RBAC | Admin dapat mengakses audit log | ✅ | 5.5s |
| TC_RBAC_007 | RBAC | Pemilik PT dapat mengakses dashboard owner | ✅ | 4.3s |
| TC_RBAC_008 | RBAC | Pemilik PT dapat mengakses halaman invoice | ✅ | 5.7s |
| TC_RBAC_009 | RBAC | Pemilik PT TIDAK dapat mengakses halaman admin karyawan | ✅ | 6.1s |
| TC_RBAC_010 | RBAC | Pemilik PT TIDAK dapat mengakses audit log | ✅ | 6.9s |
| TC_RBAC_011 | RBAC | Pemilik PT TIDAK dapat mengakses halaman absensi admin | ✅ | 6.0s |
| TC_RBAC_012 | RBAC | Karyawan dapat mengakses profil | ✅ | 6.1s |
| TC_RBAC_013 | RBAC | Karyawan dapat mengakses riwayat absensi | ✅ | 6.5s |
| TC_RBAC_014 | RBAC | Karyawan dapat mengakses slip gaji | ✅ | 6.6s |
| TC_RBAC_015 | RBAC | Karyawan TIDAK dapat mengakses halaman admin | ✅ | 5.5s |
| TC_RBAC_016 | RBAC | Karyawan TIDAK dapat mengakses halaman owner | ✅ | 6.6s |
| TC_RBAC_017 | RBAC | Karyawan TIDAK dapat mengakses CRUD karyawan | ✅ | 7.3s |
| TC_RBAC_018 | RBAC | Akses admin tanpa login redirect ke login | ✅ | 4.4s |
| TC_RBAC_019 | RBAC | Akses owner tanpa login redirect ke login | ✅ | 3.7s |
| TC_RBAC_020 | RBAC | Akses karyawan tanpa login redirect ke login | ✅ | 3.3s |
| TC_SEC_001 | Security | Endpoint admin diproteksi — redirect ke login tanpa sesi | ✅ | 3.6s |
| TC_SEC_002 | Security | Endpoint owner diproteksi — redirect ke login tanpa sesi | ✅ | 3.3s |
| TC_SEC_003 | Security | Endpoint karyawan diproteksi — redirect ke login tanpa sesi | ✅ | 3.3s |
| TC_SEC_004 | Security | Form login memiliki CSRF token | ✅ | 2.6s |
| TC_SEC_005 | Security | POST tanpa CSRF token ditolak | ✅ | 2.0s |
| TC_SEC_006 | Security | XSS payload di URL tidak dieksekusi | ✅ | 2.4s |
| TC_SEC_007 | Security | Karyawan hanya melihat data miliknya sendiri | ✅ | 5.8s |
| TC_SEC_008 | Security | Karyawan tidak bisa akses slip gaji karyawan lain via URL | ✅ | 6.8s |
| TC_SEC_009 | Security | Akses halaman admin dengan role karyawan ditolak | ✅ | 6.7s |

---

## Perbaikan yang Dilakukan Sebelum Test

1. **AbsensiController** — Method `index()` dan `rekap()` mengembalikan JSON response alih-alih Blade view. Diperbaiki agar return `view()` dengan data yang sesuai.
2. **AbsensiController** — Method `store()`, `kunci()`, `bukaKunci()` diperbaiki agar support redirect untuk web request dan JSON untuk API.
3. **InvoiceController** — Method `index()` tidak meneruskan `$ptKliens` dan `$periodes` ke view. Ditambahkan query dan compact.
4. **Audit Log View** — File `resources/views/admin/audit-log/index.blade.php` belum ada. Dibuat lengkap dengan filter dan tabel.
5. **Test Fixtures** — Kredensial user di `test-data.ts` tidak sesuai dengan database seeder. Diupdate ke `admin@ipm.test`, `owner@ptabc.co.id`, `andi.pratama@ipm.test`.

## Catatan

- Semua test dijalankan dengan browser visible (headed mode) menggunakan Chromium.
- Screenshot otomatis diambil saat test gagal (tidak ada failure pada run ini).
- HTML report tersedia di `e2e/reports/html/index.html`.
- JSON report tersedia di `e2e/reports/test-results.json`.
