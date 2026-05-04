# Dokumen Desain Teknis
# Sistem Penggajian Karyawan PT Indah Permata Mandiri (IPM)

---

## Ikhtisar (Overview)

Sistem Penggajian Karyawan PT IPM adalah aplikasi web berbasis Laravel yang mengelola siklus penggajian karyawan outsourcing secara end-to-end. Sistem ini mencakup manajemen data karyawan, pencatatan absensi manual/bulk, perhitungan gaji otomatis, pembuatan slip gaji PDF, penerbitan invoice ke PT Klien, serta dashboard monitoring.

Sistem dirancang menggunakan **Clean Architecture** dengan pola **MVC** di lapisan presentasi, memisahkan logika bisnis dari framework agar mudah diuji dan dipelihara.

### Tujuan Utama
- Otomatisasi proses penggajian dari absensi hingga invoice
- Keamanan data dengan RBAC tiga peran (Admin, Pemilik_PT, Karyawan)
- Audit trail lengkap untuk seluruh aktivitas kritis
- Dokumen PDF (slip gaji & invoice) yang dapat diunduh

### Teknologi Utama
- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL 8.0
- **Frontend**: Blade + Alpine.js + Tailwind CSS
- **PDF Generator**: Laravel DomPDF / Barryvdh
- **Excel Import**: Laravel Excel (Maatwebsite)
- **Autentikasi**: Laravel Sanctum (session-based)
- **Queue**: Laravel Queue (database driver) untuk proses berat

---

## Arsitektur

### Pola Arsitektur: Clean Architecture + MVC

Sistem menggunakan pendekatan berlapis yang memisahkan tanggung jawab secara tegas:

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│         Controllers │ Blade Views │ Form Requests           │
├─────────────────────────────────────────────────────────────┤
│                    APPLICATION LAYER                         │
│         Services │ Actions │ DTOs │ Jobs (Queue)            │
├─────────────────────────────────────────────────────────────┤
│                     DOMAIN LAYER                             │
│    Entities │ Kalkulator_Gaji │ Validator │ Policies        │
├─────────────────────────────────────────────────────────────┤
│                  INFRASTRUCTURE LAYER                        │
│    Eloquent Models │ Repositories │ Generator_Dokumen       │
│    Excel Importer │ Notification │ Audit Logger             │
└─────────────────────────────────────────────────────────────┘
```

### Alur Request HTTP

```
Browser
  │
  ▼
[Middleware Stack]
  ├── AuthenticateSession      ← validasi sesi aktif
  ├── CheckRole (RBAC)         ← validasi peran pengguna
  ├── VerifyCsrfToken          ← proteksi CSRF
  ├── SanitizeInput            ← bersihkan input XSS/SQLi
  └── ThrottleLoginAttempts    ← rate limiting endpoint login (khusus /login)
  │
  ▼
[Controller]
  │  menerima request, delegasi ke Service
  ▼
[Service / Action]
  │  logika bisnis, orkestrasi komponen
  ▼
[Domain Component]
  ├── Kalkulator_Gaji          ← hitung gaji
  ├── Validator                ← validasi data
  └── Generator_Dokumen        ← buat PDF/Excel
  │
  ▼
[Repository / Eloquent Model]
  │  akses database
  ▼
[MySQL Database]
```

---

## Komponen dan Antarmuka

### Modul Sistem

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── AuthController.php
│   │   ├── Admin/
│   │   │   ├── KaryawanController.php
│   │   │   ├── PtKlienController.php
│   │   │   ├── AbsensiController.php
│   │   │   ├── PenggajianController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── LaporanController.php
│   │   │   └── AuditLogController.php
│   │   ├── Owner/
│   │   │   ├── DashboardController.php
│   │   │   └── InvoiceApprovalController.php
│   │   └── Karyawan/
│   │       ├── ProfilController.php
│   │       └── SlipGajiController.php
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── SanitizeInput.php
│   └── Requests/
│       ├── KaryawanRequest.php
│       ├── AbsensiRequest.php
│       └── PtKlienRequest.php
├── Services/
│   ├── AuthService.php
│   ├── KaryawanService.php
│   ├── AbsensiService.php
│   ├── PenggajianService.php
│   ├── InvoiceService.php
│   └── LaporanService.php
├── Domain/
│   ├── Payroll/
│   │   ├── KalkulatorGaji.php
│   │   └── KomponenGaji.php
│   ├── Validation/
│   │   └── AbsensiValidator.php
│   └── Document/
│       └── GeneratorDokumen.php
├── Models/
│   ├── User.php
│   ├── Karyawan.php
│   ├── PtKlien.php
│   ├── Absensi.php
│   ├── PeriodePenggajian.php
│   ├── SlipGaji.php
│   ├── KomponenSlipGaji.php
│   ├── Invoice.php
│   ├── KonfigurasiGaji.php
│   └── AuditLog.php
├── Jobs/
│   ├── ProsesImportAbsensi.php
│   └── GeneratePdfSlipGaji.php
└── Policies/
    ├── KaryawanPolicy.php
    ├── SlipGajiPolicy.php
    └── InvoicePolicy.php
```

### Komponen Inti

#### 1. KalkulatorGaji

Komponen domain murni (pure class, tanpa dependensi framework) yang menghitung gaji berdasarkan konfigurasi PT Klien.

```php
// Antarmuka KalkulatorGaji
interface KalkulatorGajiInterface {
    public function hitung(
        KaryawanData $karyawan,
        RekapAbsensi $rekap,
        KonfigurasiGaji $config
    ): HasilPerhitunganGaji;
}

// Rumus utama:
// Gaji Bersih = Gaji_Pokok + Total_Tunjangan + Total_Lembur - Total_Potongan
// Total_Lembur = jam_lembur × tarif_lembur_per_jam
// Total_Potongan = hari_alpha × potongan_per_hari
```

#### 2. AbsensiValidator

Memvalidasi data absensi dari input manual maupun file Excel.

```php
interface AbsensiValidatorInterface {
    public function validasiSatuBaris(array $baris): ValidationResult;
    public function validasiBulk(array $rows): BulkValidationResult;
    public function cekDuplikasi(int $karyawanId, string $tanggal): bool;
}
```

#### 3. GeneratorDokumen

Menghasilkan dokumen PDF menggunakan template Blade.

```php
interface GeneratorDokumenInterface {
    public function buatSlipGajiPdf(SlipGaji $slip): string; // path file
    public function buatInvoicePdf(Invoice $invoice): string;
    public function buatLaporanExcel(string $tipe, array $filter): string;
}
```

---

## Model Data (ERD)

### Diagram Relasi Entitas

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│    users     │       │    karyawan      │       │  pt_klien    │
├──────────────┤       ├──────────────────┤       ├──────────────┤
│ id (PK)      │◄──────│ user_id (FK)     │──────►│ id (PK)      │
│ name         │       │ id (PK)          │       │ nama         │
│ email        │       │ pt_klien_id (FK) │       │ alamat       │
│ password     │       │ nik              │       │ telepon      │
│ role         │       │ nama_lengkap     │       │ email        │
│ is_active    │       │ tanggal_lahir    │       │ nama_pic     │
│ locked_until │       │ alamat           │       │ nomor_kontrak│
│ login_attempts│      │ telepon          │       │ tgl_mulai    │
│ last_login   │       │ jabatan          │       │ tgl_berakhir │
│ created_at   │       │ gaji_pokok       │       │ fee_jasa     │
│ updated_at   │       │ tanggal_bergabung│       │ created_at   │
└──────────────┘       │ status_aktif     │       │ updated_at   │
                       │ created_at       │       └──────┬───────┘
                       │ updated_at       │
                       └────────┬─────────┘              │
                                │                         │
                    ┌───────────▼──────────┐             │
                    │      absensi         │             │
                    ├──────────────────────┤             │
                    │ id (PK)              │             │
                    │ karyawan_id (FK)     │             │
                    │ tanggal              │             │
                    │ status_kehadiran     │             │
                    │ jam_masuk            │             │
                    │ jam_keluar           │             │
                    │ jam_lembur           │             │
                    │ keterangan           │             │
                    │ created_at           │             │
                    │ updated_at           │             │
                    └──────────────────────┘             │
                                                         │
                    ┌────────────────────────────────────┘
                    │
                    ▼
        ┌───────────────────────┐       ┌──────────────────────┐
        │  konfigurasi_gaji     │       │  periode_penggajian  │
        ├───────────────────────┤       ├──────────────────────┤
        │ id (PK)               │       │ id (PK)              │
        │ pt_klien_id (FK)      │       │ bulan                │
        │ gaji_pokok_default    │       │ tahun                │
        │ jam_kerja_normal      │       │ tanggal_mulai        │
        │ tarif_lembur_per_jam  │       │ tanggal_selesai      │
        │ potongan_per_hari     │       │ status               │
        │ komponen_tunjangan    │       │ created_at           │
        │   (JSON)              │       │ updated_at           │
        │ created_at            │       └──────────┬───────────┘
        │ updated_at            │                  │
        └───────────────────────┘                  │
                                                   │
        ┌──────────────────────────────────────────┘
        │
        ▼
┌───────────────────────┐       ┌──────────────────────────┐
│      slip_gaji        │       │   komponen_slip_gaji     │
├───────────────────────┤       ├──────────────────────────┤
│ id (PK)               │◄──────│ slip_gaji_id (FK)        │
│ karyawan_id (FK)      │       │ id (PK)                  │
│ periode_id (FK)       │       │ tipe (tunjangan/potongan)│
│ gaji_pokok            │       │ nama_komponen            │
│ total_tunjangan       │       │ nilai                    │
│ total_lembur          │       │ created_at               │
│ jam_lembur            │       └──────────────────────────┘
│ total_potongan        │
│ gaji_bersih           │       ┌──────────────────────────┐
│ status                │       │        invoice           │
│ created_at            │       ├──────────────────────────┤
│ updated_at            │       │ id (PK)                  │
└───────────────────────┘       │ pt_klien_id (FK)         │
                                │ periode_id (FK)          │
                                │ nomor_invoice            │
                                │ tanggal_pembuatan        │
                                │ subtotal_gaji            │
                                │ fee_jasa                 │
                                │ pajak                    │
                                │ total_tagihan            │
                                │ status                   │
                                │ approved_by (FK→users)   │
                                │ approved_at              │
                                │ rejected_by (FK→users)   │
                                │ rejected_at              │
                                │ alasan_penolakan         │
                                │ created_at               │
                                │ updated_at               │
                                └──────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                      audit_logs                          │
├──────────────────────────────────────────────────────────┤
│ id (PK)                                                  │
│ user_id (FK)                                             │
│ role_pengguna                                            │
│ jenis_aktivitas                                          │
│ model_tipe                                               │
│ model_id                                                 │
│ data_lama (JSON)                                         │
│ data_baru (JSON)                                         │
│ ip_address                                               │
│ created_at                                               │
└──────────────────────────────────────────────────────────┘
```

### Definisi Enum & Konstanta

```
status_kehadiran : Hadir | Izin | Sakit | Alpha
role             : admin | pemilik_pt | karyawan
status_invoice   : menunggu_approval | disetujui | ditolak
status_slip_gaji : draft | final
status_periode   : aktif | terkunci
```

---

## Strategi RBAC (Role-Based Access Control)

### Matriks Hak Akses

```
┌─────────────────────────────────┬───────┬────────────┬──────────┐
│ Fitur / Aksi                    │ Admin │ Pemilik_PT │ Karyawan │
├─────────────────────────────────┼───────┼────────────┼──────────┤
│ Manajemen Karyawan (CRUD)       │  ✓    │     ✗      │    ✗     │
│ Manajemen PT Klien (CRUD)       │  ✓    │     ✗      │    ✗     │
│ Input Absensi Manual            │  ✓    │     ✗      │    ✗     │
│ Upload Absensi Excel            │  ✓    │     ✗      │    ✗     │
│ Rekap Absensi (lihat)           │  ✓    │     ✓      │    ✗     │
│ Proses Perhitungan Gaji         │  ✓    │     ✗      │    ✗     │
│ Lihat Slip Gaji (semua)         │  ✓    │     ✗      │    ✗     │
│ Lihat Slip Gaji (milik sendiri) │  ✗    │     ✗      │    ✓     │
│ Unduh Slip Gaji (milik sendiri) │  ✗    │     ✗      │    ✓     │
│ Buat Invoice                    │  ✓    │     ✗      │    ✗     │
│ Approval Invoice                │  ✗    │     ✓      │    ✗     │
│ Unduh Invoice PDF               │  ✓    │     ✓      │    ✗     │
│ Dashboard Admin                 │  ✓    │     ✗      │    ✗     │
│ Dashboard Pemilik PT            │  ✗    │     ✓      │    ✗     │
│ Laporan (semua)                 │  ✓    │     ✓      │    ✗     │
│ Audit Log                       │  ✓    │     ✗      │    ✗     │
│ Pengaturan Sistem               │  ✓    │     ✗      │    ✗     │
└─────────────────────────────────┴───────┴────────────┴──────────┘
```

### Implementasi RBAC di Laravel

RBAC diimplementasikan melalui tiga lapisan:

**1. Middleware `CheckRole`** — validasi peran pada level route group:
```php
// routes/web.php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(...);
Route::middleware(['auth', 'role:pemilik_pt'])->prefix('owner')->group(...);
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->group(...);
```

**2. Laravel Policies** — validasi kepemilikan data (resource-level):
```php
// SlipGajiPolicy: karyawan hanya bisa akses slip miliknya
public function view(User $user, SlipGaji $slip): bool {
    if ($user->role === 'admin') return true;
    return $user->karyawan->id === $slip->karyawan_id;
}
```

**3. Blade Directives** — sembunyikan elemen UI berdasarkan peran:
```blade
@role('admin')
    <a href="{{ route('admin.karyawan.create') }}">Tambah Karyawan</a>
@endrole
```

---

## Desain API (Endpoint Utama)

### Autentikasi

```
POST   /login                    → AuthController@login
POST   /logout                   → AuthController@logout
POST   /password/forgot          → kirim email reset password
POST   /password/reset           → proses reset password dengan token
```

### Admin — Karyawan

```
GET    /admin/karyawan           → index (list + filter)
POST   /admin/karyawan           → store
GET    /admin/karyawan/{id}      → show
PUT    /admin/karyawan/{id}      → update
DELETE /admin/karyawan/{id}      → destroy
```

### Admin — PT Klien

```
GET    /admin/pt-klien           → index
POST   /admin/pt-klien           → store
GET    /admin/pt-klien/{id}      → show
PUT    /admin/pt-klien/{id}      → update
GET    /admin/pt-klien/{id}/karyawan → daftar karyawan per klien
GET    /admin/pt-klien/{id}/konfigurasi-gaji → tampilkan konfigurasi gaji
PUT    /admin/pt-klien/{id}/konfigurasi-gaji → update konfigurasi gaji
```

### Admin — Absensi

```
GET    /admin/absensi            → index (filter: karyawan, periode, pt_klien)
POST   /admin/absensi            → store (input manual)
PUT    /admin/absensi/{id}       → update
POST   /admin/absensi/import     → upload Excel (multipart/form-data)
GET    /admin/absensi/rekap      → rekap per periode & pt_klien
POST   /admin/absensi/kunci      → kunci periode absensi
```

### Admin — Penggajian

```
POST   /admin/penggajian/hitung  → jalankan perhitungan gaji
GET    /admin/penggajian         → list slip gaji (filter: pt_klien, periode)
GET    /admin/penggajian/{id}    → detail slip gaji
GET    /admin/penggajian/{id}/pdf → unduh PDF slip gaji
```

### Admin — Invoice

```
POST   /admin/invoice            → buat invoice baru
GET    /admin/invoice            → list invoice (filter: pt_klien, periode, status)
GET    /admin/invoice/{id}       → detail invoice
GET    /admin/invoice/{id}/pdf   → unduh PDF invoice
```

### Pemilik PT — Approval

```
GET    /owner/invoice            → list invoice menunggu approval
POST   /owner/invoice/{id}/approve → setujui invoice
POST   /owner/invoice/{id}/reject  → tolak invoice (wajib alasan)
GET    /owner/dashboard          → data dashboard
```

### Karyawan — Self Service

```
GET    /karyawan/profil          → data diri
PUT    /karyawan/profil          → update data diri (terbatas)
GET    /karyawan/absensi         → riwayat absensi pribadi
GET    /karyawan/slip-gaji       → list slip gaji milik sendiri
GET    /karyawan/slip-gaji/{id}/pdf → unduh PDF slip gaji
```

### Laporan

```
GET    /laporan/absensi          → laporan absensi (PDF/Excel)
GET    /laporan/penggajian       → laporan penggajian (PDF/Excel)
GET    /laporan/invoice          → laporan invoice (PDF/Excel)
```

### Admin — Audit Log

```
GET    /admin/audit-log          → index (filter: user, aktivitas, tanggal)
```

---

## Struktur Folder Project (Laravel)

```
project-root/
├── app/
│   ├── Domain/
│   │   ├── Payroll/
│   │   │   ├── KalkulatorGaji.php
│   │   │   ├── KomponenGaji.php
│   │   │   └── HasilPerhitunganGaji.php
│   │   ├── Validation/
│   │   │   ├── AbsensiValidator.php
│   │   │   └── ExcelAbsensiValidator.php
│   │   └── Document/
│   │       ├── GeneratorDokumen.php
│   │       ├── SlipGajiPdfGenerator.php
│   │       └── InvoicePdfGenerator.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── AuthController.php
│   │   │   │   └── PasswordResetController.php
│   │   │   ├── Admin/
│   │   │   │   └── KonfigurasiGajiController.php
│   │   │   ├── Owner/
│   │   │   └── Karyawan/
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   ├── SanitizeInput.php
│   │   │   └── ThrottleLoginAttempts.php
│   │   └── Requests/
│   ├── Jobs/
│   │   ├── ProsesImportAbsensi.php
│   │   ├── GeneratePdfBulk.php
│   │   └── CheckKontrakKadaluarsaJob.php
│   ├── Models/
│   ├── Notifications/
│   │   └── KredensialKaryawanNotification.php
│   ├── Observers/
│   │   └── KaryawanObserver.php
│   ├── Policies/
│   ├── Services/
│   └── Traits/
│       └── HasAuditLog.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── admin/
│   │   ├── owner/
│   │   ├── karyawan/
│   │   └── pdf/
│   │       ├── slip-gaji.blade.php
│   │       └── invoice.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   └── app/
│       ├── slip-gaji/
│       └── invoice/
└── tests/
    ├── Unit/
    │   ├── KalkulatorGajiTest.php
    │   └── AbsensiValidatorTest.php
    └── Feature/
        ├── AuthTest.php
        ├── AbsensiImportTest.php
        └── InvoiceApprovalTest.php
```

---

## Penanganan Error

### Strategi Penanganan Error

```
┌─────────────────────────────────────────────────────────────┐
│                    Error Handling Strategy                   │
├──────────────────┬──────────────────────────────────────────┤
│ Tipe Error       │ Penanganan                               │
├──────────────────┼──────────────────────────────────────────┤
│ Validasi Input   │ FormRequest → 422 Unprocessable Entity   │
│                  │ + pesan error per field                  │
├──────────────────┼──────────────────────────────────────────┤
│ Autentikasi      │ Redirect ke /login + flash message       │
│ Gagal            │ Increment login_attempts, kunci 5x       │
├──────────────────┼──────────────────────────────────────────┤
│ Akses Ditolak    │ 403 Forbidden + catat ke audit_log       │
│ (RBAC)           │                                          │
├──────────────────┼──────────────────────────────────────────┤
│ Data Tidak       │ 404 Not Found + pesan ramah              │
│ Ditemukan        │                                          │
├──────────────────┼──────────────────────────────────────────┤
│ Import Excel     │ Rollback seluruh data jika ada error     │
│ Gagal            │ + tampilkan daftar baris bermasalah      │
├──────────────────┼──────────────────────────────────────────┤
│ Duplikasi Data   │ Peringatan + konfirmasi overwrite        │
│ Absensi          │                                          │
├──────────────────┼──────────────────────────────────────────┤
│ Sesi Kedaluwarsa │ Redirect ke /login tanpa kehilangan      │
│                  │ data form (flash session)                │
├──────────────────┼──────────────────────────────────────────┤
│ Error Server     │ 500 + log ke Laravel log + notifikasi   │
│ (500)            │ admin via email (opsional)               │
└──────────────────┴──────────────────────────────────────────┘
```

### Transaksi Database

Operasi kritis menggunakan database transaction untuk menjaga konsistensi:

```php
// Contoh: proses perhitungan gaji
DB::transaction(function () use ($periodeId, $ptKlienId) {
    $rekap = $this->absensiService->buatRekap($periodeId, $ptKlienId);
    foreach ($rekap as $dataKaryawan) {
        $hasil = $this->kalkulatorGaji->hitung(...);
        SlipGaji::create($hasil->toArray());
    }
    AuditLog::catat('hitung_gaji', ...);
});
```

---


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

Setelah analisis prework, beberapa properti yang redundan digabungkan:
- Property perhitungan lembur dan potongan digabung ke dalam satu property rumus gaji (7.1 sudah mencakup 7.2 dan 7.3)
- Property akses RBAC (2.2-2.4) dan akses data karyawan (8.5, 12.4) digabung menjadi satu property akses berbasis peran
- Property audit log untuk berbagai operasi digabung menjadi satu property invariant audit

---

### Property 1: Autentikasi Kredensial Valid

*For any* kombinasi email dan password yang terdaftar dan aktif di sistem, proses autentikasi SHALL berhasil dan menghasilkan sesi yang valid dengan peran yang sesuai.

**Validates: Requirements 1.2**

---

### Property 2: Penolakan Kredensial Tidak Valid

*For any* kombinasi email atau password yang tidak terdaftar, salah, atau akun yang terkunci, proses autentikasi SHALL ditolak dan tidak menghasilkan sesi aktif.

**Validates: Requirements 1.3, 1.4**

---

### Property 3: Logout Menghapus Sesi (Round-Trip)

*For any* pengguna yang berhasil login, melakukan logout SHALL menghapus sesi aktif sehingga token sesi tersebut tidak lagi valid untuk permintaan berikutnya.

**Validates: Requirements 1.6**

---

### Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt

*For any* password yang diberikan saat pembuatan atau perubahan akun, nilai yang tersimpan di database SHALL merupakan hash bcrypt yang valid dan tidak sama dengan plaintext aslinya.

**Validates: Requirements 1.7**

---

### Property 5: RBAC — Akses Sesuai Peran

*For any* pengguna dengan peran tertentu yang mengakses endpoint yang dilindungi, sistem SHALL mengizinkan akses hanya jika peran pengguna tersebut memiliki izin sesuai matriks hak akses, dan menolak dengan HTTP 403 untuk semua akses yang tidak diizinkan.

**Validates: Requirements 2.2, 2.3, 2.4, 2.5**

---

### Property 6: Isolasi Data Karyawan

*For any* karyawan yang mengakses endpoint self-service (slip gaji, absensi, profil), respons SHALL hanya mengandung data yang dimiliki oleh karyawan yang sedang login, tidak pernah data milik karyawan lain.

**Validates: Requirements 8.5, 12.4**

---

### Property 7: Penyimpanan Data Karyawan (Round-Trip)

*For any* data karyawan valid yang dikirimkan melalui form tambah/edit, seluruh field yang dikirimkan SHALL tersimpan di database dan dapat dibaca kembali dengan nilai yang identik.

**Validates: Requirements 3.1**

---

### Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat

*For any* karyawan baru yang berhasil ditambahkan oleh Admin, sistem SHALL membuat entri user dengan email yang sama dan peran karyawan secara otomatis.

**Validates: Requirements 3.2**

---

### Property 9: Sinkronisasi Status Karyawan dan Akun Login

*For any* karyawan yang diubah statusnya menjadi tidak aktif, akun login yang terhubung SHALL dinonaktifkan sehingga karyawan tersebut tidak dapat login.

**Validates: Requirements 3.7**

---

### Property 10: Filter Karyawan Konsisten

*For any* kombinasi kriteria filter (nama, PT_Klien, jabatan, status), semua hasil yang dikembalikan SHALL memenuhi seluruh kriteria filter yang diberikan, dan tidak ada karyawan yang memenuhi kriteria tersebut yang tidak muncul dalam hasil.

**Validates: Requirements 3.6**

---

### Property 11: Validasi Import Excel — Atomicity

*For any* file Excel absensi yang mengandung minimal satu baris data tidak valid, sistem SHALL menolak seluruh file dan tidak menyimpan data apapun ke database (all-or-nothing).

**Validates: Requirements 5.3, 5.4**

---

### Property 12: Uniqueness Absensi per Karyawan per Tanggal

*For any* kombinasi karyawan_id dan tanggal, database SHALL tidak pernah mengandung lebih dari satu entri absensi untuk kombinasi tersebut.

**Validates: Requirements 5.6**

---

### Property 13: Kebenaran Rumus Perhitungan Gaji

*For any* kombinasi nilai Gaji_Pokok, komponen tunjangan, jam lembur, tarif lembur, hari alpha, dan potongan per hari yang valid, hasil perhitungan Kalkulator_Gaji SHALL memenuhi:
- `Gaji_Bersih = Gaji_Pokok + Σ(Tunjangan) + (jam_lembur × tarif_lembur) - (hari_alpha × potongan_per_hari)`

**Validates: Requirements 7.1, 7.2, 7.3**

---

### Property 14: Immutability Data Gaji Historis

*For any* slip gaji yang sudah dihitung dan disimpan, perubahan konfigurasi gaji PT_Klien setelahnya SHALL tidak mengubah nilai-nilai yang tersimpan di slip gaji tersebut.

**Validates: Requirements 7.6**

---

### Property 15: Format dan Uniqueness Nomor Invoice

*For any* invoice yang dibuat, Nomor_Invoice SHALL mengikuti format `IPM-{KODE_KLIEN}-{TAHUN}-{BULAN}-{NOMOR_URUT}` dan SHALL unik di seluruh database.

**Validates: Requirements 9.2**

---

### Property 16: Pencegahan Duplikasi Invoice

*For any* kombinasi PT_Klien dan Periode_Penggajian, sistem SHALL tidak pernah mengizinkan lebih dari satu invoice aktif untuk kombinasi tersebut.

**Validates: Requirements 9.3**

---

### Property 17: Invariant Audit Log

*For any* operasi kritis yang berhasil dieksekusi (login/logout, CRUD karyawan, CRUD PT_Klien, import absensi, hitung gaji, buat/approve/tolak invoice), sistem SHALL membuat entri audit log yang mengandung: user_id, role, jenis_aktivitas, waktu, dan data sebelum/sesudah perubahan.

**Validates: Requirements 3.4, 9.5, 11.1, 11.2**

---

### Property 18: Batas Minimum Gaji Bersih

*For any* kombinasi input perhitungan gaji yang menghasilkan nilai negatif, Kalkulator_Gaji SHALL mengembalikan nilai 0 (nol) sebagai Gaji Bersih, tidak pernah nilai negatif.

**Validates: Requirements 7.8**

---

### Property 19: Atomicity Generate Nomor Invoice

*For any* dua proses pembuatan invoice yang terjadi secara bersamaan untuk PT_Klien yang sama, sistem SHALL menghasilkan dua Nomor_Invoice yang berbeda dan keduanya unik.

**Validates: Requirements 9.10**

---

### Property 20: Validasi Sinkron Sebelum Import Async

*For any* file Excel absensi yang diupload, proses validasi SHALL selalu selesai dan menghasilkan hasil valid/invalid sebelum background job penyimpanan dimulai.

**Validates: Requirements 5.3, 5.5**

---

## Strategi Pengujian

### Pendekatan Dual Testing

Sistem menggunakan dua pendekatan pengujian yang saling melengkapi:

1. **Unit Test / Example Test** — memverifikasi perilaku spesifik dengan contoh konkret
2. **Property-Based Test** — memverifikasi properti universal di seluruh ruang input

### Library Property-Based Testing

Menggunakan **[eris/eris](https://github.com/giorgiosironi/eris)** (PHP) atau **[PHPUnit + custom generators]** untuk property-based testing di Laravel.

Alternatif yang direkomendasikan: **[Pest PHP](https://pestphp.com/)** dengan plugin `pest-plugin-faker` untuk generate data acak.

Setiap property test dikonfigurasi untuk berjalan minimal **100 iterasi**.

### Unit Tests

Fokus pada:
- Kasus spesifik dan contoh konkret
- Titik integrasi antar komponen
- Edge case dan kondisi error

```
tests/Unit/
├── KalkulatorGajiTest.php          ← rumus gaji, edge case nilai 0
├── AbsensiValidatorTest.php        ← validasi format, duplikasi
├── GeneratorNomorInvoiceTest.php   ← format nomor invoice
└── RbacPolicyTest.php              ← matriks akses per peran
```

### Property-Based Tests

Setiap property test harus memiliki tag komentar referensi:
`// Feature: employee-payroll-system, Property {N}: {deskripsi}`

```
tests/Property/
├── AuthPropertyTest.php
│   ├── Property 1: kredensial valid selalu berhasil autentikasi
│   ├── Property 2: kredensial tidak valid selalu ditolak
│   ├── Property 3: logout menghapus sesi (round-trip)
│   └── Property 4: password tersimpan sebagai hash bcrypt
├── RbacPropertyTest.php
│   ├── Property 5: akses sesuai peran (matriks RBAC)
│   └── Property 6: isolasi data karyawan
├── KaryawanPropertyTest.php
│   ├── Property 7: round-trip penyimpanan data karyawan
│   ├── Property 8: akun otomatis saat karyawan baru
│   ├── Property 9: sinkronisasi status karyawan-akun
│   └── Property 10: filter karyawan konsisten
├── AbsensiPropertyTest.php
│   ├── Property 11: atomicity import Excel
│   ├── Property 12: uniqueness absensi per karyawan per tanggal
│   └── Property 20: validasi sinkron sebelum import async
├── PayrollPropertyTest.php
│   ├── Property 13: kebenaran rumus perhitungan gaji
│   ├── Property 14: immutability data gaji historis
│   └── Property 18: batas minimum gaji bersih (tidak pernah negatif)
└── InvoicePropertyTest.php
    ├── Property 15: format dan uniqueness nomor invoice
    ├── Property 16: pencegahan duplikasi invoice
    ├── Property 17: invariant audit log
    └── Property 19: atomicity generate nomor invoice concurrent
```

### Integration Tests

Fokus pada alur end-to-end:

```
tests/Feature/
├── AuthTest.php                    ← login, logout, lockout
├── AbsensiImportTest.php           ← upload Excel valid/invalid
├── PenggajianFlowTest.php          ← rekap → hitung → slip gaji
├── InvoiceApprovalTest.php         ← buat → approve/tolak → PDF
└── DashboardTest.php               ← data dashboard per peran
```

### Konfigurasi Property Test

```php
// Contoh property test untuk Property 13 (Rumus Gaji)
// Feature: employee-payroll-system, Property 13: kebenaran rumus perhitungan gaji

it('menghitung gaji bersih sesuai rumus untuk semua kombinasi input valid', function () {
    repeat(100, function () {
        $gajiPokok = fake()->numberBetween(2000000, 10000000);
        $tunjangan = fake()->numberBetween(0, 2000000);
        $jamLembur = fake()->numberBetween(0, 40);
        $tarifLembur = fake()->numberBetween(10000, 50000);
        $hariAlpha = fake()->numberBetween(0, 5);
        $potonganPerHari = fake()->numberBetween(50000, 200000);

        $hasil = app(KalkulatorGaji::class)->hitung(
            gajiPokok: $gajiPokok,
            tunjangan: [$tunjangan],
            jamLembur: $jamLembur,
            tarifLembur: $tarifLembur,
            hariAlpha: $hariAlpha,
            potonganPerHari: $potonganPerHari,
        );

        $expected = $gajiPokok + $tunjangan + ($jamLembur * $tarifLembur) - ($hariAlpha * $potonganPerHari);
        expect($hasil->gajiBersih)->toBe($expected);
    });
});
```

### Smoke Tests

```
tests/Smoke/
├── HalamanLoginTest.php            ← halaman login dapat diakses
├── PeranSistemTest.php             ← tiga peran terdaftar
└── MiddlewareRbacTest.php          ← middleware terpasang di semua route
```
