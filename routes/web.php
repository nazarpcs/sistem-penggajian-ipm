<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routing untuk Sistem Penggajian PT Indah Permata Mandiri.
| Struktur route group:
|   - /login, /logout          → AuthController (public)
|   - /admin/*                 → Admin controllers (role:admin)
|   - /owner/*                 → Owner controllers (role:pemilik_pt)
|   - /karyawan/*              → Karyawan controllers (role:karyawan)
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'pemilik_pt' => redirect()->route('owner.dashboard'),
            'karyawan' => redirect()->route('karyawan.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// Auth routes
Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

// Password Reset routes
Route::get('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Task 15.1: Dashboard Admin
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('dashboard');

    // Task 15.3: Laporan (Admin)
    Route::get('laporan/absensi', [\App\Http\Controllers\Admin\LaporanController::class, 'absensi'])
        ->name('laporan.absensi');
    Route::get('laporan/penggajian', [\App\Http\Controllers\Admin\LaporanController::class, 'penggajian'])
        ->name('laporan.penggajian');
    Route::get('laporan/invoice', [\App\Http\Controllers\Admin\LaporanController::class, 'invoice'])
        ->name('laporan.invoice');

    // Task 4.1: Karyawan CRUD
    Route::get('karyawan/{karyawan}/check-delete', [\App\Http\Controllers\Admin\KaryawanController::class, 'checkDelete'])
        ->name('karyawan.check-delete');
    Route::resource('karyawan', \App\Http\Controllers\Admin\KaryawanController::class);

    // Task 5.1: PT Klien CRUD + karyawan per klien
    Route::get('pt-klien/{pt_klien}/karyawan', [\App\Http\Controllers\Admin\PtKlienController::class, 'karyawan'])
        ->name('pt-klien.karyawan');
    Route::resource('pt-klien', \App\Http\Controllers\Admin\PtKlienController::class)->except(['destroy']);

    // Task 5.2: Konfigurasi Gaji per PT Klien
    Route::get('pt-klien/{pt_klien}/konfigurasi-gaji', [\App\Http\Controllers\Admin\KonfigurasiGajiController::class, 'show'])
        ->name('pt-klien.konfigurasi-gaji.show');
    Route::put('pt-klien/{pt_klien}/konfigurasi-gaji', [\App\Http\Controllers\Admin\KonfigurasiGajiController::class, 'update'])
        ->name('pt-klien.konfigurasi-gaji.update');

    // Task 8.2: Penggajian — hitung, list, detail, PDF
    Route::post('penggajian/hitung', [\App\Http\Controllers\Admin\PenggajianController::class, 'hitung'])
        ->name('penggajian.hitung');
    Route::get('penggajian', [\App\Http\Controllers\Admin\PenggajianController::class, 'index'])
        ->name('penggajian.index');
    Route::get('penggajian/{id}', [\App\Http\Controllers\Admin\PenggajianController::class, 'show'])
        ->name('penggajian.show');
    Route::get('penggajian/{id}/pdf', [\App\Http\Controllers\Admin\PenggajianController::class, 'downloadPdf'])
        ->name('penggajian.pdf');

    // Task 7.1-7.5: Absensi CRUD, Import, Rekap, Kunci/Buka Kunci
    Route::post('absensi/import', [\App\Http\Controllers\Admin\AbsensiController::class, 'import'])
        ->name('absensi.import');
    Route::get('absensi/rekap', [\App\Http\Controllers\Admin\AbsensiController::class, 'rekap'])
        ->name('absensi.rekap');
    Route::post('absensi/kunci', [\App\Http\Controllers\Admin\AbsensiController::class, 'kunci'])
        ->name('absensi.kunci');
    Route::post('absensi/buka-kunci', [\App\Http\Controllers\Admin\AbsensiController::class, 'bukaKunci'])
        ->name('absensi.buka-kunci');
    Route::resource('absensi', \App\Http\Controllers\Admin\AbsensiController::class)
        ->except(['destroy', 'create', 'show', 'edit']);

    // Task 10.1: Invoice CRUD
    Route::post('invoice', [\App\Http\Controllers\Admin\InvoiceController::class, 'store'])
        ->name('invoice.store');
    Route::get('invoice', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])
        ->name('invoice.index');
    Route::get('invoice/{id}', [\App\Http\Controllers\Admin\InvoiceController::class, 'show'])
        ->name('invoice.show');
    Route::get('invoice/{id}/pdf', [\App\Http\Controllers\Admin\InvoiceController::class, 'downloadPdf'])
        ->name('invoice.pdf');

    // Task 13.2: Audit Log
    Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
        ->name('audit-log.index');
});

// Owner routes
Route::middleware(['auth', 'role:pemilik_pt'])->prefix('owner')->name('owner.')->group(function () {
    // Task 10.2: Dashboard
    Route::get('dashboard', [\App\Http\Controllers\Owner\DashboardController::class, 'index'])
        ->name('dashboard');

    // Task 10.2: Invoice Approval
    Route::get('invoice', [\App\Http\Controllers\Owner\InvoiceApprovalController::class, 'index'])
        ->name('invoice.index');
    Route::get('invoice/{id}', [\App\Http\Controllers\Owner\InvoiceApprovalController::class, 'show'])
        ->name('invoice.show');
    Route::post('invoice/{id}/approve', [\App\Http\Controllers\Owner\InvoiceApprovalController::class, 'approve'])
        ->name('invoice.approve');
    Route::post('invoice/{id}/reject', [\App\Http\Controllers\Owner\InvoiceApprovalController::class, 'reject'])
        ->name('invoice.reject');

    // Task 15.3: Laporan (Pemilik PT)
    Route::get('laporan/absensi', [\App\Http\Controllers\Admin\LaporanController::class, 'absensi'])
        ->name('laporan.absensi');
    Route::get('laporan/penggajian', [\App\Http\Controllers\Admin\LaporanController::class, 'penggajian'])
        ->name('laporan.penggajian');
    Route::get('laporan/invoice', [\App\Http\Controllers\Admin\LaporanController::class, 'invoice'])
        ->name('laporan.invoice');
});

// Karyawan routes
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
    // Dashboard Karyawan (redirect to profil)
    Route::get('dashboard', [\App\Http\Controllers\Karyawan\ProfilController::class, 'show'])
        ->name('dashboard');

    // Task 12.1: Profil
    Route::get('profil', [\App\Http\Controllers\Karyawan\ProfilController::class, 'show'])
        ->name('profil.show');
    Route::put('profil', [\App\Http\Controllers\Karyawan\ProfilController::class, 'update'])
        ->name('profil.update');

    // Task 12.1: Riwayat Absensi
    Route::get('absensi', [\App\Http\Controllers\Karyawan\AbsensiController::class, 'index'])
        ->name('absensi.index');

    // Task 12.1: Slip Gaji
    Route::get('slip-gaji', [\App\Http\Controllers\Karyawan\SlipGajiController::class, 'index'])
        ->name('slip-gaji.index');
    Route::get('slip-gaji/{id}/pdf', [\App\Http\Controllers\Karyawan\SlipGajiController::class, 'downloadPdf'])
        ->name('slip-gaji.pdf');
});
