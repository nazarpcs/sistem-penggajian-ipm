# Sistem Penggajian PT Indah Permata Mandiri (IPM)

Aplikasi web penggajian karyawan outsourcing berbasis Laravel 11. Mengelola data karyawan, absensi, perhitungan gaji otomatis, slip gaji PDF, invoice ke PT Klien, dan dashboard monitoring.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL 8.0+
- **Frontend:** Blade + Alpine.js + Tailwind CSS
- **PDF:** Laravel DomPDF (barryvdh/laravel-dompdf)
- **Excel:** Maatwebsite Excel
- **Testing:** Pest PHP + Playwright (E2E)

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & npm

## Setup

```bash
# 1. Clone repository
git clone <repo-url>
cd sistem-penggajian-pt-ipm

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi database
# Edit .env → sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. Jalankan migration & seeder
php artisan migrate
php artisan db:seed

# 7. Build frontend assets
npm run build

# 8. Jalankan server
php artisan serve
```

## Akun Default (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ipm.test | password |
| Pemilik PT | owner@ptabc.co.id | password |
| Karyawan | andi.pratama@ipm.test | password |

> ⚠️ Ganti password default setelah setup production.

## Testing

```bash
# Unit & Feature tests (Pest PHP)
php artisan test

# E2E tests (Playwright) — pastikan server running
php artisan serve &
npx playwright test

# E2E dengan browser visible
npx playwright test --headed
```

## Struktur Peran (RBAC)

- **Admin** — Akses penuh: CRUD karyawan, PT Klien, absensi, penggajian, invoice, audit log
- **Pemilik PT** — Dashboard monitoring, approval/reject invoice, laporan
- **Karyawan** — Profil, riwayat absensi, slip gaji (self-service)

## Lisensi

Proprietary — Nazar Syah Dumyati.
