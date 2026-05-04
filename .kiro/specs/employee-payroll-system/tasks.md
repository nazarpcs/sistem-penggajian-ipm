# Rencana Implementasi: Sistem Penggajian Karyawan PT IPM

## Ikhtisar

Implementasi sistem penggajian berbasis Laravel 11 dengan Clean Architecture. Task diorganisir berdasarkan domain agent: arsitektur dasar, backend, frontend, payroll engine, document generator, validator, report/analytics, security, dan QA. Setiap task merujuk ke requirement dan correctness property yang relevan.

## Tasks

- [x] 1. Setup Struktur Proyek dan Infrastruktur Database (system-architect)
  - [x] 1.1 Inisialisasi proyek Laravel 11 dan konfigurasi dasar
    - Buat proyek Laravel 11 baru dengan PHP 8.2+
    - Konfigurasi `.env` untuk MySQL 8.0, queue driver database, session driver database
    - Install dependensi: `barryvdh/laravel-dompdf`, `maatwebsite/excel`, `pestphp/pest`
    - Buat struktur folder `app/Domain/Payroll`, `app/Domain/Validation`, `app/Domain/Document`, `app/Services`
    - Jalankan `php artisan queue:table` untuk migration tabel jobs
    - Pastikan migration `password_reset_tokens` tersedia (Laravel default)
    - _Requirements: Seluruh requirement (fondasi proyek)_

  - [x] 1.2 Buat migration untuk seluruh tabel database
    - Migration tabel `users` (dengan kolom: role, is_active, locked_until, login_attempts, last_login)
    - Migration tabel `karyawan` (relasi ke users dan pt_klien)
    - Migration tabel `pt_klien` (termasuk fee_jasa, nomor_kontrak, tgl_mulai, tgl_berakhir)
    - Migration tabel `konfigurasi_gaji` (relasi ke pt_klien, kolom JSON komponen_tunjangan)
    - Migration tabel `absensi` (unique constraint: karyawan_id + tanggal)
    - Migration tabel `periode_penggajian` (bulan, tahun, status)
    - Migration tabel `slip_gaji` dan `komponen_slip_gaji`
    - Migration tabel `invoice` (termasuk approved_by, rejected_by, alasan_penolakan)
    - Migration tabel `audit_logs`
    - _Requirements: 3.1, 4.1, 5.1, 5.6, 7.1, 9.1, 9.2, 11.1, 11.2_

  - [x] 1.3 Buat Eloquent Models dengan relasi
    - Model `User` dengan relasi ke Karyawan, method `isAdmin()`, `isPemilikPt()`, `isKaryawan()`
    - Model `Karyawan` dengan relasi ke User, PtKlien, Absensi, SlipGaji
    - Model `PtKlien` dengan relasi ke Karyawan, KonfigurasiGaji, Invoice
    - Model `KonfigurasiGaji` dengan cast JSON untuk komponen_tunjangan
    - Model `Absensi`, `PeriodePenggajian`, `SlipGaji`, `KomponenSlipGaji`, `Invoice`, `AuditLog`
    - _Requirements: 3.1, 4.1, 7.1, 9.1, 11.2_

  - [x] 1.4 Buat database seeder untuk data awal
    - Seeder user Admin default
    - Seeder contoh PT_Klien, Karyawan, KonfigurasiGaji
    - _Requirements: 2.1_

  - [x] 1.5 Buat stub trait HasAuditLog
    - Buat trait `HasAuditLog` di `app/Traits/HasAuditLog.php` dengan method dasar `logActivity()`
    - Implementasi minimal agar task-task selanjutnya bisa langsung menggunakannya
    - Implementasi lengkap akan dilakukan di Task 13.1
    - _Requirements: 11.1, 11.2_

- [x] 2. Checkpoint — Pastikan migration dan model berjalan
  - Jalankan `php artisan migrate` dan pastikan semua migration berhasil
  - Jalankan seeder dan pastikan data terisi
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

- [x] 3. Implementasi Autentikasi dan Keamanan (security-engineer)
  - [x] 3.1 Implementasi AuthController dan AuthService
    - Buat `AuthController` dengan method login, logout
    - Buat `AuthService` untuk logika autentikasi: validasi kredensial, buat sesi, hapus sesi
    - Implementasi lockout setelah 5 kali gagal login selama 15 menit
    - Implementasi sesi kedaluwarsa setelah 8 jam tidak aktif
    - Password harus di-hash dengan bcrypt
    - Redirect ke halaman login saat sesi kedaluwarsa
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 12.5_

  - [x] 3.2 Implementasi PasswordResetController
    - Buat fitur forgot password: kirim email dengan token reset
    - Token reset kedaluwarsa dalam 60 menit
    - Buat fitur proses reset password
    - _Requirements: 1.8_

  - [x] 3.3 Implementasi Middleware CheckRole
    - Buat middleware `CheckRole` yang memvalidasi peran pengguna pada setiap request
    - Daftarkan middleware di route group: admin, owner, karyawan
    - Return 403 dan catat ke audit log jika akses ditolak
    - _Requirements: 2.2, 2.3, 2.4, 2.5, 2.6_

  - [x] 3.4 Implementasi Middleware SanitizeInput dan ThrottleLoginAttempts
    - Buat `SanitizeInput` middleware untuk mencegah XSS dan SQL Injection
    - Buat `ThrottleLoginAttempts` middleware: max 10 percobaan per menit per IP
    - Terapkan proteksi CSRF pada seluruh form
    - _Requirements: 1.9, 12.1, 12.2, 12.3, 12.6_

  - [x] 3.5 Implementasi Laravel Policies untuk otorisasi resource-level
    - Buat `KaryawanPolicy`: Admin full access
    - Buat `SlipGajiPolicy`: Karyawan hanya akses slip miliknya, Admin akses semua
    - Buat `InvoicePolicy`: Admin buat/lihat, Pemilik_PT approve/reject/lihat
    - _Requirements: 2.2, 2.3, 2.4, 8.5, 12.4_

  - [ ]* 3.6 Tulis property test untuk autentikasi
    - **Property 1: Autentikasi Kredensial Valid** — kredensial valid selalu berhasil autentikasi
    - **Property 2: Penolakan Kredensial Tidak Valid** — kredensial tidak valid selalu ditolak
    - **Property 3: Logout Menghapus Sesi (Round-Trip)** — logout menghapus sesi aktif
    - **Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt**
    - **Validates: Requirements 1.2, 1.3, 1.4, 1.6, 1.7**

  - [ ]* 3.7 Tulis property test untuk RBAC dan isolasi data
    - **Property 5: RBAC — Akses Sesuai Peran** — akses sesuai matriks hak akses
    - **Property 6: Isolasi Data Karyawan** — karyawan hanya lihat data sendiri
    - **Validates: Requirements 2.2, 2.3, 2.4, 2.5, 8.5, 12.4**

- [x] 4. Implementasi Manajemen Data Karyawan (backend-developer)
  - [x] 4.1 Implementasi KaryawanService dan KaryawanController
    - Buat `KaryawanService` dengan method: index (filter), store, show, update, destroy
    - Buat `KaryawanController` di namespace Admin dengan CRUD lengkap
    - Buat `KaryawanRequest` FormRequest untuk validasi input (nama, NIK, email, dll)
    - Implementasi filter karyawan berdasarkan nama, PT_Klien, jabatan, status aktif
    - _Requirements: 3.1, 3.5, 3.6_

  - [x] 4.2 Implementasi pembuatan akun otomatis dan notifikasi karyawan baru
    - Buat `KaryawanObserver` yang membuat User otomatis saat karyawan baru ditambahkan
    - Generate password sementara dan kirim via `KredensialKaryawanNotification`
    - Sinkronisasi status: nonaktifkan akun login saat karyawan dinonaktifkan
    - _Requirements: 3.2, 3.3, 3.7_

  - [x] 4.3 Implementasi soft delete dan proteksi penghapusan karyawan
    - Tampilkan peringatan jika karyawan memiliki data absensi atau slip gaji aktif
    - Minta konfirmasi sebelum melanjutkan penghapusan
    - Catat semua perubahan ke Audit_Log
    - _Requirements: 3.4, 3.5_

  - [ ]* 4.4 Tulis property test untuk manajemen karyawan
    - **Property 7: Penyimpanan Data Karyawan (Round-Trip)** — data tersimpan dan terbaca identik
    - **Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat**
    - **Property 9: Sinkronisasi Status Karyawan dan Akun Login**
    - **Property 10: Filter Karyawan Konsisten** — filter mengembalikan hasil yang tepat
    - **Validates: Requirements 3.1, 3.2, 3.6, 3.7**

- [x] 5. Implementasi Manajemen PT Klien dan Konfigurasi Gaji (backend-developer)
  - [x] 5.1 Implementasi PtKlienService dan PtKlienController
    - Buat `PtKlienService` dengan method CRUD dan daftar karyawan per klien
    - Buat `PtKlienController` di namespace Admin
    - Buat `PtKlienRequest` FormRequest untuk validasi input
    - Catat semua perubahan ke Audit_Log
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 5.2 Implementasi KonfigurasiGajiController dan manajemen konfigurasi gaji
    - Buat `KonfigurasiGajiController` untuk CRUD konfigurasi gaji per PT_Klien
    - Field: gaji_pokok_default, jam_kerja_normal, tarif_lembur_per_jam, potongan_per_hari, komponen_tunjangan (JSON)
    - Catat perubahan ke Audit_Log, pastikan perubahan hanya berlaku untuk periode berikutnya
    - _Requirements: 4.5, 4.6, 4.7_

  - [x] 5.3 Implementasi notifikasi kontrak akan berakhir
    - Buat `CheckKontrakKadaluarsaJob` yang berjalan harian via scheduler
    - Deteksi kontrak PT_Klien yang berakhir dalam 30 hari
    - Tampilkan notifikasi peringatan kepada Admin
    - _Requirements: 4.4_

- [x] 6. Checkpoint — Pastikan CRUD karyawan dan PT Klien berfungsi
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

- [x] 7. Implementasi Absensi dan Validasi (validator-agent)
  - [x] 7.1 Implementasi AbsensiService dan AbsensiController
    - Buat `AbsensiService` dengan method: index (filter), store (manual), update, rekap, kunciPeriode
    - Buat `AbsensiController` di namespace Admin
    - Buat `AbsensiRequest` FormRequest untuk validasi input manual
    - Implementasi filter absensi berdasarkan karyawan, periode, PT_Klien
    - Implementasi response 409 Conflict untuk duplikasi absensi dengan data existing dan opsi overwrite
    - _Requirements: 5.1, 5.6, 5.7_

  - [x] 7.2 Implementasi AbsensiValidator untuk validasi data absensi
    - Buat `AbsensiValidator` di `app/Domain/Validation`
    - Method `validasiSatuBaris`: validasi format, kelengkapan field, kevalidan data
    - Method `validasiBulk`: validasi seluruh baris file Excel secara sinkron
    - Method `cekDuplikasi`: cek duplikasi karyawan_id + tanggal
    - Implementasi atomicity: jika ada satu baris invalid, tolak seluruh file
    - Implementasi struktur response error yang konsisten: JSON dengan field `success`, `message`, `errors` (422 untuk validasi gagal), dan format khusus untuk Excel import (total_baris, baris_valid, baris_error, detail per baris)
    - _Requirements: 5.3, 5.4, 5.6_

  - [x] 7.3 Implementasi ExcelAbsensiValidator dan ProsesImportAbsensi Job
    - Buat `ExcelAbsensiValidator` untuk validasi format file Excel dan kolom wajib
    - Buat `ProsesImportAbsensi` Job untuk penyimpanan data secara asinkron setelah validasi berhasil
    - Tampilkan daftar baris bermasalah jika validasi gagal
    - Tampilkan notifikasi progres saat proses background berjalan
    - _Requirements: 5.2, 5.3, 5.4, 5.5_

  - [x] 7.4 Implementasi rekap absensi (backend-developer)
    - Buat method rekap: hitung total hadir, izin, sakit, alpha, jam lembur per karyawan
    - Hitung jam lembur = selisih jam_keluar dengan jam_kerja_normal per PT_Klien
    - Peringatan jika ada karyawan tanpa data absensi dalam periode
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 7.5 Implementasi penguncian periode absensi (backend-developer)
    - Implementasi kunci/buka kunci periode absensi (hanya Admin)
    - Catat buka kunci ke Audit_Log dengan identitas Admin dan alasan
    - _Requirements: 6.5, 6.6, 6.7_

  - [ ]* 7.6 Tulis property test untuk absensi
    - **Property 11: Validasi Import Excel — Atomicity** — file dengan baris invalid ditolak seluruhnya
    - **Property 12: Uniqueness Absensi per Karyawan per Tanggal** — tidak ada duplikasi
    - **Property 20: Validasi Sinkron Sebelum Import Async** — validasi selesai sebelum job dimulai
    - **Validates: Requirements 5.3, 5.4, 5.5, 5.6**

- [x] 8. Implementasi Mesin Perhitungan Gaji (payroll-engine)
  - [x] 8.1 Implementasi KalkulatorGaji (domain pure class)
    - Buat `KalkulatorGaji` di `app/Domain/Payroll` sebagai pure class tanpa dependensi framework
    - Buat DTO `KomponenGaji` dan `HasilPerhitunganGaji`
    - Implementasi rumus: `Gaji_Bersih = Gaji_Pokok + Σ(Tunjangan) + (jam_lembur × tarif_lembur) - (hari_alpha × potongan_per_hari)`
    - Pastikan Gaji_Bersih tidak pernah negatif (minimum 0), tampilkan peringatan jika hasil negatif
    - Dukung komponen tunjangan yang berbeda per PT_Klien
    - _Requirements: 7.1, 7.2, 7.3, 7.7, 7.8_

  - [x] 8.2 Implementasi PenggajianService dan PenggajianController
    - Buat `PenggajianService` dengan method: hitungGaji (per PT_Klien + periode), listSlipGaji, detailSlipGaji
    - Buat `PenggajianController` di namespace Admin
    - Proses perhitungan dalam database transaction untuk konsistensi
    - Simpan rincian komponen gaji ke tabel `slip_gaji` dan `komponen_slip_gaji`
    - Pastikan data gaji historis tidak berubah saat konfigurasi diubah (immutability)
    - _Requirements: 7.1, 7.4, 7.5, 7.6_

  - [ ]* 8.3 Tulis property test untuk perhitungan gaji
    - **Property 13: Kebenaran Rumus Perhitungan Gaji** — rumus gaji benar untuk semua kombinasi input valid
    - **Property 14: Immutability Data Gaji Historis** — perubahan konfigurasi tidak mengubah slip gaji lama
    - **Property 18: Batas Minimum Gaji Bersih** — gaji bersih tidak pernah negatif
    - **Validates: Requirements 7.1, 7.2, 7.3, 7.6, 7.8**

- [x] 9. Checkpoint — Pastikan perhitungan gaji dan absensi berfungsi
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

- [x] 10. Implementasi Invoice dan Approval (backend-developer)
  - [x] 10.1 Implementasi InvoiceService dan InvoiceController (Admin)
    - Buat `InvoiceService` dengan method: buatInvoice, listInvoice, detailInvoice
    - Buat `InvoiceController` di namespace Admin
    - Generate Nomor_Invoice format `IPM-{KODE_KLIEN}-{TAHUN}-{BULAN}-{NOMOR_URUT}`
    - Gunakan database-level locking untuk mencegah duplikasi nomor invoice concurrent
    - Cegah duplikasi invoice untuk kombinasi PT_Klien + Periode yang sama
    - Set status awal "menunggu_approval"
    - Buat `InvoiceRequest` FormRequest untuk validasi input pembuatan invoice (pt_klien_id, periode_id, cek duplikasi)
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.9, 9.10_

  - [x] 10.2 Implementasi InvoiceApprovalController (Pemilik PT)
    - Buat `InvoiceApprovalController` di namespace Owner
    - Method approve: ubah status ke "disetujui", catat waktu dan identitas ke Audit_Log
    - Method reject: ubah status ke "ditolak", wajibkan alasan penolakan, catat ke Audit_Log
    - _Requirements: 9.5, 9.6, 9.7_

  - [ ]* 10.3 Tulis property test untuk invoice
    - **Property 15: Format dan Uniqueness Nomor Invoice** — format benar dan unik
    - **Property 16: Pencegahan Duplikasi Invoice** — tidak ada duplikasi per PT_Klien + Periode
    - **Property 19: Atomicity Generate Nomor Invoice** — concurrent generation menghasilkan nomor unik
    - **Validates: Requirements 9.2, 9.3, 9.10**

- [x] 11. Implementasi Generator Dokumen PDF/Excel (document-generator)
  - [x] 11.1 Implementasi SlipGajiPdfGenerator
    - Buat template Blade `resources/views/pdf/slip-gaji.blade.php`
    - Buat `SlipGajiPdfGenerator` di `app/Domain/Document` menggunakan DomPDF
    - Tampilkan rincian: nama karyawan, PT_Klien, periode, gaji pokok, tunjangan, lembur, potongan, gaji bersih
    - Buat `GeneratePdfBulk` Job untuk generate PDF massal via queue
    - _Requirements: 8.1, 8.2, 8.4_

  - [x] 11.2 Implementasi InvoicePdfGenerator
    - Buat template Blade `resources/views/pdf/invoice.blade.php`
    - Buat `InvoicePdfGenerator` di `app/Domain/Document` menggunakan DomPDF
    - Tampilkan: nomor invoice, data PT_Klien, rincian gaji karyawan, subtotal, fee jasa, pajak, total tagihan
    - Hanya bisa diunduh jika status invoice "disetujui"
    - _Requirements: 9.1, 9.8_

  - [x] 11.3 Implementasi export laporan Excel/PDF
    - Buat `GeneratorDokumen` facade untuk generate laporan absensi, penggajian, invoice
    - Gunakan Maatwebsite Excel untuk export Excel
    - Gunakan DomPDF untuk export PDF
    - _Requirements: 10.6_

- [x] 12. Implementasi Slip Gaji Karyawan Self-Service (backend-developer)
  - [x] 12.1 Implementasi ProfilController, AbsensiController, dan SlipGajiController (Karyawan)
    - Buat `ProfilController` di namespace Karyawan: lihat dan update data diri (terbatas)
    - Buat `AbsensiController` di namespace Karyawan: riwayat absensi pribadi (GET /karyawan/absensi)
    - Buat `SlipGajiController` di namespace Karyawan: list slip gaji milik sendiri, unduh PDF
    - Pastikan isolasi data: karyawan hanya akses data miliknya via Policy
    - Tampilkan daftar slip gaji diurutkan dari periode terbaru
    - _Requirements: 2.4, 8.1, 8.3, 8.4, 8.5, 8.6_

- [x] 13. Implementasi Audit Log (security-engineer)
  - [x] 13.1 Implementasi trait HasAuditLog dan AuditLog service
    - Buat trait `HasAuditLog` yang bisa digunakan di semua model
    - Catat: user_id, role, jenis_aktivitas, waktu, data sebelum/sesudah perubahan, IP address
    - Terapkan pada operasi: login/logout, CRUD karyawan, CRUD PT_Klien, import absensi, hitung gaji, buat/approve/tolak invoice
    - _Requirements: 11.1, 11.2, 11.4, 11.5_

  - [x] 13.2 Implementasi AuditLogController
    - Buat `AuditLogController` di namespace Admin
    - Tampilkan halaman audit log dengan filter: pengguna, jenis aktivitas, rentang waktu
    - Hanya Admin yang dapat mengakses
    - _Requirements: 11.3_

  - [ ]* 13.3 Tulis property test untuk audit log
    - **Property 17: Invariant Audit Log** — setiap operasi kritis menghasilkan entri audit log lengkap
    - **Validates: Requirements 3.4, 9.5, 11.1, 11.2**

- [x] 14. Checkpoint — Pastikan invoice, dokumen, dan audit log berfungsi
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

- [x] 15. Implementasi Dashboard dan Laporan (report-analytics)
  - [x] 15.1 Implementasi Dashboard Admin
    - Buat `DashboardController` di namespace Admin (atau gunakan LaporanController)
    - Tampilkan: total karyawan aktif, total PT_Klien aktif, ringkasan penggajian bulan berjalan, daftar invoice menunggu approval
    - _Requirements: 10.1_

  - [x] 15.2 Implementasi Dashboard Pemilik PT
    - Buat `DashboardController` di namespace Owner
    - Tampilkan: total pengeluaran gaji per bulan, grafik tren 12 bulan terakhir (diagram batang/garis), daftar invoice memerlukan approval
    - _Requirements: 10.2, 10.7_

  - [x] 15.3 Implementasi LaporanController dan LaporanService
    - Buat `LaporanService` dengan method: laporanAbsensi, laporanPenggajian, laporanInvoice
    - Buat `LaporanController` dengan filter: PT_Klien, karyawan, rentang tanggal, periode, status
    - Integrasikan dengan GeneratorDokumen untuk export PDF/Excel
    - _Requirements: 10.3, 10.4, 10.5, 10.6_

- [x] 16. Implementasi Frontend Views (frontend-developer)
  - [x] 16.1 Buat layout utama dan komponen UI
    - Buat layout Blade utama dengan Tailwind CSS: sidebar navigasi, header, content area
    - Buat komponen Alpine.js reusable: modal konfirmasi, notifikasi flash, dropdown filter, tabel data
    - Buat Blade directive `@role` untuk kontrol tampilan berdasarkan peran
    - _Requirements: 2.2, 2.3, 2.4_

  - [x] 16.2 Buat halaman autentikasi
    - Halaman login dengan form email dan password
    - Halaman forgot password dan reset password
    - _Requirements: 1.1, 1.8_

  - [x] 16.3 Buat halaman Admin: Karyawan dan PT Klien
    - Halaman list karyawan dengan filter (nama, PT_Klien, jabatan, status)
    - Form tambah/edit karyawan
    - Halaman list PT_Klien dan form tambah/edit
    - Halaman konfigurasi gaji per PT_Klien
    - _Requirements: 3.1, 3.6, 4.1, 4.3, 4.6_

  - [x] 16.4 Buat halaman Admin: Absensi dan Penggajian
    - Form input absensi manual per karyawan
    - Halaman upload Excel absensi dengan progress indicator
    - Halaman rekap absensi dengan filter dan tombol kunci periode
    - Halaman list slip gaji dengan filter dan tombol hitung gaji
    - _Requirements: 5.1, 5.2, 6.1, 6.3, 7.4, 8.6_

  - [x] 16.5 Buat halaman Admin: Invoice dan Audit Log
    - Halaman list invoice dengan filter (PT_Klien, periode, status)
    - Form buat invoice baru
    - Halaman detail invoice dengan tombol unduh PDF
    - Halaman audit log dengan filter
    - _Requirements: 9.1, 9.9, 11.3_

  - [x] 16.6 Buat halaman Pemilik PT: Dashboard dan Invoice Approval
    - Dashboard dengan grafik tren pengeluaran (Alpine.js + Chart library)
    - Halaman list invoice menunggu approval
    - Form approve/reject dengan field alasan penolakan (wajib saat reject)
    - _Requirements: 10.2, 10.7, 9.5, 9.6_

  - [x] 16.7 Buat halaman Karyawan: Profil dan Slip Gaji
    - Halaman profil karyawan (lihat dan edit terbatas)
    - Halaman riwayat absensi pribadi
    - Halaman list slip gaji dengan tombol unduh PDF
    - _Requirements: 8.1, 8.3, 8.4, 8.5_

- [x] 16.8 Checkpoint — Pastikan seluruh halaman frontend berfungsi
  - Pastikan semua halaman dapat diakses sesuai role
  - Pastikan form validasi berjalan di client-side
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

- [x] 17. Wiring: Routing dan Integrasi Seluruh Komponen (system-architect)
  - [x] 17.1 Konfigurasi routing lengkap di routes/web.php
    - Route group Admin dengan middleware `auth` dan `role:admin`
    - Route group Owner dengan middleware `auth` dan `role:pemilik_pt`
    - Route group Karyawan dengan middleware `auth` dan `role:karyawan`
    - Daftarkan semua controller dan resource routes
    - _Requirements: 2.2, 2.3, 2.4, 2.6_

  - [x] 17.2 Daftarkan middleware, policies, observers, dan scheduler
    - Daftarkan middleware CheckRole, SanitizeInput, ThrottleLoginAttempts di kernel
    - Daftarkan semua Policies di AuthServiceProvider
    - Daftarkan KaryawanObserver di AppServiceProvider
    - Daftarkan `CheckKontrakKadaluarsaJob` di scheduler (daily)
    - Daftarkan queue worker configuration untuk jobs
    - _Requirements: 2.6, 3.2, 4.4, 5.5, 12.1_

- [x] 18. Checkpoint Final — Pastikan seluruh sistem terintegrasi (qa-engineer)
  - Jalankan seluruh test suite (unit, property, feature)
  - Pastikan semua test lulus, tanyakan user jika ada pertanyaan

## Catatan

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk MVP lebih cepat
- Setiap task merujuk ke requirement spesifik untuk traceability
- Checkpoint memastikan validasi inkremental di setiap fase
- Property test memvalidasi correctness properties universal dari design document
- Unit test memvalidasi contoh spesifik dan edge case
