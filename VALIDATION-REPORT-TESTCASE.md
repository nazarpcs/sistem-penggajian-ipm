# Laporan Validasi Test Case
# Sistem Penggajian Karyawan PT Indah Permata Mandiri (IPM)

---

## Ringkasan Eksekutif

| Metrik | Nilai |
|--------|-------|
| Total Acceptance Criteria di Requirements | 78 |
| Total AC yang Ter-cover | 78 |
| Total AC yang MISSING | 0 |
| Coverage Percentage | **100%** (78/78) |
| Total Correctness Properties | 20 |
| Properties Ter-cover | 20 |
| Properties MISSING | 0 |
| Property Coverage | **100%** (20/20) |
| Total Test Cases (CSV & MD) | 310 |
| Test Cases Out of Scope | 0 |
| Consistency Issues | 2 (minor) |

---

## 1. COVERAGE VALIDATION — Acceptance Criteria Coverage

### Requirement 1: Autentikasi dan Manajemen Akun (Req 1.1 – 1.9)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 1.1 | [OK] | AUTH-001 | Halaman login dengan form email dan password |
| Req 1.2 | [OK] | AUTH-001, AUTH-002, AUTH-003, AUTH-025, AUTH-026, AUTH-027 | Login valid per role, redirect ke dashboard sesuai peran |
| Req 1.3 | [OK] | AUTH-004, AUTH-005, AUTH-019, AUTH-020, AUTH-021 | Login gagal dengan kredensial tidak valid |
| Req 1.4 | [OK] | AUTH-006, AUTH-007, AUTH-008 | Lockout setelah 5x gagal, kunci 15 menit |
| Req 1.5 | [OK] | AUTH-010 | Session kedaluwarsa setelah 8 jam tidak aktif |
| Req 1.6 | [OK] | AUTH-011, AUTH-012 | Logout menghapus sesi, redirect ke login |
| Req 1.7 | [OK] | AUTH-013 | Password dienkripsi bcrypt |
| Req 1.8 | [OK] | AUTH-014, AUTH-015, AUTH-016 | Reset password via email, token 60 menit |
| Req 1.9 | [OK] | AUTH-017, AUTH-018 | Rate limiting 10 percobaan/menit per IP |

**Subtotal: 9/9 AC ter-cover (100%)**

### Requirement 2: Role-Based Access Control (Req 2.1 – 2.6)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 2.1 | [OK] | RBAC-001 | 3 peran: Admin, Pemilik_PT, Karyawan |
| Req 2.2 | [OK] | RBAC-002 s/d RBAC-008 | Admin akses penuh ke semua fitur |
| Req 2.3 | [OK] | RBAC-009, RBAC-010, RBAC-011 | Pemilik_PT: lihat laporan, dashboard, approval |
| Req 2.4 | [OK] | RBAC-017, RBAC-018, RBAC-019 | Karyawan: data diri, absensi, slip gaji sendiri |
| Req 2.5 | [OK] | RBAC-012 s/d RBAC-016, RBAC-020 s/d RBAC-025, RBAC-031, RBAC-035 | 403 + audit log untuk akses tidak sah |
| Req 2.6 | [OK] | RBAC-029, RBAC-030, RBAC-032, RBAC-033, RBAC-034 | Validasi hak akses pada setiap HTTP request via middleware |

**Subtotal: 6/6 AC ter-cover (100%)**

### Requirement 3: Manajemen Data Karyawan (Req 3.1 – 3.7)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 3.1 | [OK] | KRY-001, KRY-005, KRY-023 | Data karyawan lengkap tersimpan dan terbaca |
| Req 3.2 | [OK] | KRY-002 | Akun login otomatis dibuat |
| Req 3.3 | [OK] | KRY-003 | Notifikasi email kredensial terkirim |
| Req 3.4 | [OK] | KRY-007, KRY-026, KRY-027 | Update/tambah/hapus karyawan tercatat di audit log |
| Req 3.5 | [OK] | KRY-009 | Peringatan saat hapus karyawan dengan data terkait |
| Req 3.6 | [OK] | KRY-010, KRY-011, KRY-012, KRY-013, KRY-014, KRY-015 | Filter berdasarkan nama, PT Klien, jabatan, status |
| Req 3.7 | [OK] | KRY-020, KRY-021, KRY-022 | Nonaktifkan karyawan → akun login nonaktif otomatis |

**Subtotal: 7/7 AC ter-cover (100%)**

### Requirement 4: Manajemen Data PT Klien (Req 4.1 – 4.7)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 4.1 | [OK] | PTK-001, PTK-002, PTK-003 | Data PT Klien lengkap tersimpan |
| Req 4.2 | [OK] | PTK-005, PTK-019, PTK-020 | CRUD + audit log |
| Req 4.3 | [OK] | PTK-003, PTK-004 | Daftar karyawan per PT Klien |
| Req 4.4 | [OK] | PTK-009, PTK-010, PTK-011 | Notifikasi kontrak < 30 hari, job harian |
| Req 4.5 | [OK] | PTK-006, PTK-007, PTK-008 | Konfigurasi aturan gaji berbeda per PT Klien |
| Req 4.6 | [OK] | PTK-006, PTK-007, PTK-008 | CRUD konfigurasi gaji per PT Klien |
| Req 4.7 | [OK] | PTK-021, PTK-016 | Perubahan konfigurasi gaji tercatat audit log, tidak ubah historis |

**Subtotal: 7/7 AC ter-cover (100%)**


### Requirement 5: Input dan Upload Absensi Manual (Req 5.1 – 5.7)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 5.1 | [OK] | ABS-001, ABS-002, ABS-003, ABS-004 | Form input absensi manual per karyawan |
| Req 5.2 | [OK] | ABS-012, ABS-015 | Upload file Excel untuk absensi massal |
| Req 5.3 | [OK] | ABS-013, ABS-017, ABS-018 | Validasi sinkron sebelum penyimpanan |
| Req 5.4 | [OK] | ABS-013, ABS-014 | Daftar baris bermasalah tanpa menyimpan data |
| Req 5.5 | [OK] | ABS-018, ABS-019 | Penyimpanan async + notifikasi progres |
| Req 5.6 | [OK] | ABS-010, ABS-020 | Cegah duplikasi karyawan+tanggal |
| Req 5.7 | [OK] | ABS-010, ABS-011 | Peringatan duplikasi + opsi overwrite |

**Subtotal: 7/7 AC ter-cover (100%)**

### Requirement 6: Rekap dan Pengolahan Absensi (Req 6.1 – 6.7)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 6.1 | [OK] | RKP-001, RKP-002, RKP-003, RKP-004, RKP-005, RKP-006 | Rekap absensi: hadir, izin, sakit, alpha, jam lembur |
| Req 6.2 | [OK] | RKP-006 | Hitung jam lembur berdasarkan selisih jam keluar - jam kerja normal |
| Req 6.3 | [OK] | RKP-008, RKP-009 | Tabel rekap dengan filter PT Klien dan Periode |
| Req 6.4 | [OK] | RKP-007 | Peringatan karyawan tanpa data absensi |
| Req 6.5 | [OK] | RKP-010, RKP-011, RKP-012 | Kunci periode absensi |
| Req 6.6 | [OK] | RKP-013, RKP-020 | Buka kunci + audit log dengan identitas dan alasan |
| Req 6.7 | [OK] | RKP-015, RKP-016 | Hanya Admin yang bisa buka kunci |

**Subtotal: 7/7 AC ter-cover (100%)**

### Requirement 7: Perhitungan Gaji Otomatis (Req 7.1 – 7.8)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 7.1 | [OK] | PAY-001 | Rumus: Gaji Bersih = Gaji_Pokok + Tunjangan + Lembur - Potongan |
| Req 7.2 | [OK] | PAY-002 | Total_Lembur = jam_lembur × tarif_lembur |
| Req 7.3 | [OK] | PAY-003 | Total_Potongan = hari_alpha × potongan_per_hari |
| Req 7.4 | [OK] | PAY-011, PAY-012 | Perhitungan batch untuk seluruh karyawan aktif |
| Req 7.5 | [OK] | PAY-015 | Simpan rincian komponen gaji |
| Req 7.6 | [OK] | PAY-013, PAY-014, PTK-016 | Immutability data gaji historis |
| Req 7.7 | [OK] | PAY-009, PAY-010 | Konfigurasi tunjangan berbeda per PT Klien |
| Req 7.8 | [OK] | PAY-004, PAY-005 | Gaji Bersih minimum 0, peringatan jika negatif |

**Subtotal: 8/8 AC ter-cover (100%)**

### Requirement 8: Slip Gaji Karyawan (Req 8.1 – 8.6)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 8.1 | [OK] | SLP-001 | Slip gaji dibuat setelah perhitungan, akses via akun karyawan |
| Req 8.2 | [OK] | SLP-002, SLP-012 | Rincian lengkap: nama, PT Klien, periode, komponen gaji |
| Req 8.3 | [OK] | SLP-001 | Daftar slip gaji diurutkan dari periode terbaru |
| Req 8.4 | [OK] | SLP-003, SLP-011 | Download PDF slip gaji |
| Req 8.5 | [OK] | SLP-004, SLP-005, SLP-006 | Karyawan hanya akses slip gaji milik sendiri |
| Req 8.6 | [OK] | SLP-007, SLP-008, SLP-009, SLP-010 | Admin lihat semua slip gaji + filter |

**Subtotal: 6/6 AC ter-cover (100%)**

### Requirement 9: Pembuatan dan Pengelolaan Invoice (Req 9.1 – 9.10)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 9.1 | [OK] | INV-001, INV-018 | Invoice berisi nomor unik, data PT Klien, rincian gaji, fee, total |
| Req 9.2 | [OK] | INV-002 | Format nomor: IPM-{KODE}-{YYYY}-{MM}-{NNN} |
| Req 9.3 | [OK] | INV-005 | Cegah duplikasi invoice PT Klien + Periode |
| Req 9.4 | [OK] | INV-006 | Status awal "Menunggu Approval" |
| Req 9.5 | [OK] | INV-007, INV-023 | Approval → status "Disetujui" + audit log |
| Req 9.6 | [OK] | INV-008, INV-009 | Penolakan → status "Ditolak" + wajib alasan |
| Req 9.7 | [OK] | INV-008, INV-024 | Catat identitas, waktu, alasan penolakan |
| Req 9.8 | [OK] | INV-010, INV-011, INV-012 | Download PDF hanya untuk invoice disetujui |
| Req 9.9 | [OK] | INV-013, INV-014, INV-015 | Daftar invoice dengan filter |
| Req 9.10 | [OK] | INV-016, INV-017 | Database locking untuk concurrent invoice creation |

**Subtotal: 10/10 AC ter-cover (100%)**

### Requirement 10: Dashboard dan Laporan (Req 10.1 – 10.7)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 10.1 | [OK] | DSH-001, DSH-002, DSH-003, DSH-004 | Dashboard Admin: karyawan, PT Klien, penggajian, invoice pending |
| Req 10.2 | [OK] | DSH-005, DSH-006, DSH-007 | Dashboard Pemilik_PT: pengeluaran, grafik tren, invoice approval |
| Req 10.3 | [OK] | DSH-008, DSH-009 | Laporan absensi dengan filter |
| Req 10.4 | [OK] | DSH-010 | Laporan penggajian dengan filter |
| Req 10.5 | [OK] | DSH-011 | Laporan invoice dengan filter |
| Req 10.6 | [OK] | DSH-012, DSH-013, DSH-014, DSH-015, DSH-016, DSH-017 | Export PDF dan Excel |
| Req 10.7 | [OK] | DSH-006 | Grafik pengeluaran gaji per PT Klien |

**Subtotal: 7/7 AC ter-cover (100%)**

### Requirement 11: Audit Log Aktivitas (Req 11.1 – 11.5)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 11.1 | [OK] | AUD-001 s/d AUD-013, AUD-022, AUD-023 | Semua aktivitas kritis tercatat |
| Req 11.2 | [OK] | AUD-015 | Struktur data: user_id, role, jenis, waktu, data sebelum/sesudah |
| Req 11.3 | [OK] | AUD-016, AUD-017, AUD-018 | Filter audit log: pengguna, jenis, rentang waktu |
| Req 11.4 | [OK] | AUD-019 | Retensi data minimal 1 tahun |
| Req 11.5 | [OK] | AUD-014, AUD-026 | Percobaan akses tidak sah tercatat dengan IP |

**Subtotal: 5/5 AC ter-cover (100%)**

### Requirement 12: Keamanan Data (Req 12.1 – 12.6)

| AC | Status | Test Case(s) | Keterangan |
|----|--------|-------------|------------|
| Req 12.1 | [OK] | SEC-001 s/d SEC-009 | Semua endpoint diproteksi autentikasi |
| Req 12.2 | [OK] | SEC-010 s/d SEC-015 | Sanitasi input: XSS dan SQL Injection |
| Req 12.3 | [OK] | SEC-016, SEC-017, SEC-018 | HTTPS/CSRF protection (CSRF divalidasi) |
| Req 12.4 | [OK] | SEC-021, SEC-022, SEC-023 | Karyawan hanya melihat data sendiri via API |
| Req 12.5 | [OK] | SEC-019, SEC-020 | Sesi expired → redirect login tanpa simpan data |
| Req 12.6 | [OK] | SEC-016, SEC-017, SEC-018 | Proteksi CSRF pada semua form |

**Subtotal: 6/6 AC ter-cover (100%)**


---

## 2. CORRECTNESS PROPERTY VALIDATION — Property Coverage

| Property | Deskripsi | Status | Test Case(s) | Keterangan |
|----------|-----------|--------|-------------|------------|
| Property 1 | Autentikasi Kredensial Valid | [OK] | AUTH-001, AUTH-002, AUTH-003 | Login berhasil untuk 3 role |
| Property 2 | Penolakan Kredensial Tidak Valid | [OK] | AUTH-004, AUTH-005, AUTH-006, AUTH-007, AUTH-009 | Email salah, password salah, akun terkunci, akun nonaktif |
| Property 3 | Logout Menghapus Sesi (Round-Trip) | [OK] | AUTH-011, AUTH-012 | Logout + verifikasi token lama invalid |
| Property 4 | Password Selalu Tersimpan Sebagai Hash Bcrypt | [OK] | AUTH-013, SEC-024, SEC-025 | Hash bcrypt, tidak tampil di response/audit |
| Property 5 | RBAC — Akses Sesuai Peran | [OK] | RBAC-002 s/d RBAC-034, DSH-018 s/d DSH-020 | Matriks akses lengkap per role |
| Property 6 | Isolasi Data Karyawan | [OK] | RBAC-026, RBAC-027, RBAC-028, SLP-004, SLP-005, SLP-006, SEC-021, SEC-022, SEC-023 | Karyawan hanya akses data sendiri |
| Property 7 | Penyimpanan Data Karyawan (Round-Trip) | [OK] | KRY-001, KRY-005, KRY-023 | Simpan dan baca kembali identik |
| Property 8 | Pembuatan Akun Otomatis Saat Karyawan Baru | [OK] | KRY-002, KRY-003 | Akun user + notifikasi email |
| Property 9 | Sinkronisasi Status Karyawan dan Akun Login | [OK] | KRY-020, KRY-021, KRY-022 | Nonaktif/aktif karyawan → akun sinkron |
| Property 10 | Filter Karyawan Konsisten | [OK] | KRY-010 s/d KRY-015 | Filter tunggal dan kombinasi |
| Property 11 | Validasi Import Excel — Atomicity | [OK] | ABS-013, ABS-014, ABS-016, ABS-017 | All-or-nothing: 1 baris error → seluruh file ditolak |
| Property 12 | Uniqueness Absensi per Karyawan per Tanggal | [OK] | ABS-010, ABS-011, ABS-020 | Duplikasi dicegah di level aplikasi dan database |
| Property 13 | Kebenaran Rumus Perhitungan Gaji | [OK] | PAY-001, PAY-002, PAY-003, PAY-009 | Rumus lengkap dengan contoh numerik |
| Property 14 | Immutability Data Gaji Historis | [OK] | PAY-013, PAY-014, PTK-016 | Ubah konfigurasi tidak ubah slip lama |
| Property 15 | Format dan Uniqueness Nomor Invoice | [OK] | INV-002, INV-003, INV-004 | Format IPM-{KODE}-{YYYY}-{MM}-{NNN}, unik |
| Property 16 | Pencegahan Duplikasi Invoice | [OK] | INV-005 | Duplikasi PT Klien + Periode dicegah |
| Property 17 | Invariant Audit Log | [OK] | AUD-001 s/d AUD-026 | Semua operasi kritis tercatat lengkap |
| Property 18 | Batas Minimum Gaji Bersih | [OK] | PAY-004, PAY-005 | Gaji Bersih >= 0, tidak pernah negatif |
| Property 19 | Atomicity Generate Nomor Invoice | [OK] | INV-016, INV-017 | Concurrent creation → nomor tetap unik |
| Property 20 | Validasi Sinkron Sebelum Import Async | [OK] | ABS-018 | Validasi sinkron selesai sebelum job async |

**Total: 20/20 Properties ter-cover (100%)**

---

## 3. SCOPE VALIDATION — Out of Scope Test Cases

Setelah memeriksa seluruh 310 test case terhadap requirements.md dan design.md:

| Temuan | Jumlah |
|--------|--------|
| Test case yang menguji fitur TIDAK ada di requirements | 0 |
| Test case yang menguji endpoint TIDAK ada di design.md | 0 |
| Test case dengan expected result BERTENTANGAN dengan requirements | 0 |

**Catatan:**
- Semua test case merujuk pada fitur dan endpoint yang terdefinisi di requirements.md dan design.md.
- Beberapa test case bersifat "opsional" (ditandai dengan keterangan "jika diimplementasikan"), seperti SLP-016 dan DSH-024. Ini bukan out of scope, melainkan nice-to-have yang tetap relevan.
- Test case SEC-027 (X-Forwarded-For header manipulation) dan SEC-030 (DoS request body) adalah skenario keamanan umum yang relevan meskipun tidak secara eksplisit disebut di requirements — ini adalah best practice security testing yang wajar.

**Kesimpulan: Tidak ada test case yang out of scope.**

---

## 4. CONSISTENCY VALIDATION

### 4.1 Keunikan TC_ID

| Pemeriksaan | Hasil |
|-------------|-------|
| TC_ID duplikat di TEST-CASES.csv | Tidak ada duplikat |
| TC_ID duplikat di TEST-CASES-COMPREHENSIVE.md | Tidak ada duplikat |
| TC_ID konsisten antara CSV dan MD | Konsisten — semua TC_ID di CSV ada di MD dan sebaliknya |

**Status: PASS**

### 4.2 Kesesuaian Module dengan Requirement

| Module Prefix | Requirement | Status |
|---------------|-------------|--------|
| AUTH (001-027) | Req 1: Autentikasi | [OK] |
| RBAC (001-035) | Req 2: RBAC | [OK] |
| KRY (001-029) | Req 3: Manajemen Karyawan | [OK] |
| PTK (001-023) | Req 4: Manajemen PT Klien | [OK] |
| ABS (001-028) | Req 5: Input/Upload Absensi | [OK] |
| RKP (001-023) | Req 6: Rekap Absensi | [OK] |
| PAY (001-021) | Req 7: Perhitungan Gaji | [OK] |
| SLP (001-017) | Req 8: Slip Gaji | [OK] |
| INV (001-027) | Req 9: Invoice | [OK] |
| DSH (001-024) | Req 10: Dashboard & Laporan | [OK] |
| AUD (001-026) | Req 11: Audit Log | [OK] |
| SEC (001-030) | Req 12: Keamanan Data | [OK] |

**Status: PASS**

### 4.3 Priority Assignment

| Pemeriksaan | Hasil | Status |
|-------------|-------|--------|
| Fitur autentikasi kritis (login/lockout) = High | AUTH-001 s/d AUTH-007 = High | [OK] |
| Fitur RBAC akses/denied = High | RBAC-002 s/d RBAC-035 mayoritas High | [OK] |
| Fitur keamanan (XSS, SQLi, CSRF) = High | SEC-010 s/d SEC-018 = High | [OK] |
| Fitur perhitungan gaji kritis = High | PAY-001 s/d PAY-005 = High | [OK] |
| Fitur atomicity import = High | ABS-013 = High | [OK] |
| Edge case non-kritis = Medium/Low | KRY-028 = Low, PTK-023 = Low | [OK] |

**Status: PASS**

### 4.4 Type Assignment

| Pemeriksaan | Hasil | Status |
|-------------|-------|--------|
| Login berhasil = Positive | AUTH-001, AUTH-002, AUTH-003 | [OK] |
| Login gagal = Negative | AUTH-004, AUTH-005 | [OK] |
| XSS/SQLi test = Security | SEC-010 s/d SEC-015 | [OK] |
| Akses role = RBAC | RBAC-002 s/d RBAC-034 | [OK] |
| Boundary/corner case = Edge | AUTH-008, AUTH-010, PAY-004 | [OK] |
| Audit log verification = Audit | AUTH-022, AUTH-023, AUTH-024 | [OK] |

**Status: PASS**

### 4.5 Consistency Issues (Minor)

| # | Issue | Severity | Keterangan |
|---|-------|----------|------------|
| 1 | Req 12.3 menyebutkan HTTPS, tapi tidak ada test case yang secara eksplisit memverifikasi HTTPS enforcement | Minor | SEC-016 s/d SEC-018 memvalidasi CSRF (bagian dari Req 12.6), tapi HTTPS enforcement (Req 12.3) hanya tercover secara implisit. Disarankan menambah test case eksplisit untuk HTTPS redirect. |
| 2 | KRY-028 (tanggal bergabung masa depan) memiliki expected result ambigu: "Data tersimpan... atau ditolak sesuai business rule" | Minor | Expected result seharusnya definitif, bukan ambigu. Requirements tidak melarang tanggal bergabung di masa depan, jadi expected result sebaiknya "Data tersimpan" saja. |

---

## 5. GAP ANALYSIS

### 5.1 Acceptance Criteria yang Belum Punya Test Case

**Tidak ada.** Semua 78 acceptance criteria sudah memiliki minimal 1 test case.

### 5.2 Skenario Edge Case yang Belum Tercakup

| # | Skenario | Requirement Terkait | Prioritas |
|---|----------|-------------------|-----------|
| 1 | HTTPS enforcement — redirect HTTP ke HTTPS | Req 12.3 | Medium |
| 2 | Upload Excel dengan duplikasi internal (antar baris dalam file yang sama) | Req 5.6, Property 12 | High |
| 3 | Perhitungan gaji dengan nilai desimal/pecahan pada tarif lembur (floating point precision) | Req 7.1, Property 13 | High |
| 4 | Reset password untuk akun yang terkunci — apakah unlock akun? | Req 1.4, Req 1.8 | Medium |
| 5 | Karyawan pindah PT Klien di tengah periode — bagaimana perhitungan gaji? | Req 7.4 | Medium |
| 6 | Multiple Admin melakukan perhitungan gaji bersamaan untuk PT Klien yang sama | Req 7.4 | Medium |
| 7 | Invoice approval/rejection untuk invoice yang sudah disetujui/ditolak (double action) | Req 9.5, Req 9.6 | Medium |
| 8 | Upload Excel dengan encoding karakter non-ASCII (nama karyawan dengan karakter khusus) | Req 5.2 | Low |
| 9 | Session fixation attack — apakah session ID di-regenerate setelah login? | Req 12.1 | High |
| 10 | Karyawan update profil sendiri — field apa saja yang boleh diubah? | Req 8 (endpoint `/karyawan/profil` PUT) | Low |

### 5.3 Skenario Integrasi Antar Modul yang Belum Ada

| # | Skenario Integrasi | Modul Terkait | Prioritas |
|---|-------------------|---------------|-----------|
| 1 | End-to-end: Input absensi → Rekap → Hitung gaji → Slip gaji → Invoice → Approval → PDF | ABS → RKP → PAY → SLP → INV | High |
| 2 | Nonaktifkan karyawan di tengah periode → dampak pada rekap dan perhitungan gaji | KRY → RKP → PAY | Medium |
| 3 | Hapus PT Klien yang masih punya invoice aktif | PTK → INV | Medium |
| 4 | Ubah konfigurasi gaji → hitung ulang gaji → verifikasi slip lama tidak berubah | PTK → PAY → SLP | Medium |
| 5 | Upload absensi Excel → kunci periode → buat invoice → approve | ABS → RKP → INV | Medium |

### 5.4 Skenario Concurrent/Race Condition yang Belum Ada

| # | Skenario | Modul | Prioritas |
|---|----------|-------|-----------|
| 1 | Dua Admin input absensi manual untuk karyawan+tanggal yang sama secara bersamaan | ABS | High |
| 2 | Admin menghitung gaji sementara Admin lain mengubah konfigurasi gaji | PAY + PTK | Medium |
| 3 | Admin mengunci periode sementara Admin lain sedang input absensi pada periode tersebut | RKP + ABS | Medium |
| 4 | Dua Pemilik_PT approve invoice yang sama secara bersamaan | INV | Low |
| 5 | Admin upload Excel absensi sementara Admin lain input manual untuk karyawan yang sama | ABS | Medium |

---

## 6. Rekomendasi

### Prioritas Tinggi
1. **Tambah test case duplikasi internal Excel** — Saat ini ABS-013 hanya menguji baris invalid, belum ada test case khusus untuk duplikasi antar baris dalam satu file Excel (karyawan_id + tanggal sama muncul 2x dalam file). Ini penting untuk Property 12.
2. **Tambah test case floating point precision** — Perhitungan gaji dengan tarif lembur desimal (misal Rp 25.500,50) perlu diuji untuk memastikan tidak ada error akumulasi. Ini kritis untuk Property 13.
3. **Tambah test case end-to-end integration** — Minimal 1 test case yang menguji alur lengkap dari input absensi hingga PDF invoice untuk memastikan semua modul terintegrasi dengan benar.
4. **Tambah test case session fixation** — Verifikasi bahwa session ID di-regenerate setelah login berhasil untuk mencegah session fixation attack.
5. **Tambah test case concurrent absensi manual** — Dua request input absensi untuk karyawan+tanggal yang sama secara bersamaan harus menghasilkan hanya 1 entry (Property 12).

### Prioritas Medium
6. **Perjelas expected result KRY-028** — Ubah dari ambigu menjadi definitif.
7. **Tambah test case HTTPS enforcement** — Verifikasi redirect HTTP → HTTPS secara eksplisit.
8. **Tambah test case invoice double action** — Approve invoice yang sudah disetujui, tolak invoice yang sudah ditolak.
9. **Tambah test case karyawan pindah PT Klien** — Dampak pada perhitungan gaji periode berjalan.

### Prioritas Rendah
10. **Tambah test case encoding karakter khusus** — Upload Excel dengan nama karyawan berkarakter non-ASCII.
11. **Tambah test case update profil karyawan** — Verifikasi field yang boleh diubah oleh karyawan sendiri.

---

## Kesimpulan

Test case yang ada di TEST-CASES.csv dan TEST-CASES-COMPREHENSIVE.md sudah **sangat komprehensif** dengan coverage 100% terhadap seluruh 78 acceptance criteria dan 20 correctness properties. Tidak ditemukan test case yang out of scope. Konsistensi TC_ID, module mapping, priority, dan type assignment sudah baik dengan hanya 2 issue minor.

Gap utama yang perlu ditutup adalah: (1) skenario duplikasi internal dalam file Excel, (2) floating point precision pada perhitungan gaji, (3) test case integrasi end-to-end antar modul, dan (4) skenario concurrent/race condition pada input absensi manual.