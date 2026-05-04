<?php

// Feature: employee-payroll-system, Task 3: Autentikasi & Keamanan
// Unit test untuk InvoicePolicy — otorisasi akses invoice dan approval workflow
// @see Property 5: RBAC — Akses Sesuai Peran
// @see Req 9.4-9.8 (invoice workflow: buat, approval, reject, download)

use App\Models\Invoice;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Models\User;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new InvoicePolicy();

    $this->admin = User::factory()->admin()->create();
    $this->pemilikPt = User::factory()->pemilikPt()->create();
    $this->karyawanUser = User::factory()->karyawan()->create();

    $this->ptKlien = PtKlien::create([
        'nama' => 'PT Invoice Test',
        'alamat' => 'Jl. Invoice',
        'telepon' => '021-999',
        'email' => 'invoice@pt.com',
        'nama_pic' => 'PIC Invoice',
        'nomor_kontrak' => 'KTR-INV-001',
        'tgl_mulai' => now()->subYear(),
        'tgl_berakhir' => now()->addYear(),
        'fee_jasa' => 3000000,
    ]);

    $this->periode = PeriodePenggajian::create([
        'bulan' => now()->month,
        'tahun' => now()->year,
        'tanggal_mulai' => now()->startOfMonth(),
        'tanggal_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    // Invoice status: menunggu_approval
    $this->invoiceMenunggu = Invoice::create([
        'pt_klien_id' => $this->ptKlien->id,
        'periode_id' => $this->periode->id,
        'nomor_invoice' => 'IPM-INV-2025-06-001',
        'tanggal_pembuatan' => now(),
        'subtotal_gaji' => 50000000,
        'fee_jasa' => 3000000,
        'pajak' => 0,
        'total_tagihan' => 53000000,
        'status' => 'menunggu_approval',
    ]);

    // Invoice status: disetujui (perlu PT Klien berbeda untuk unique constraint)
    $ptKlien2 = PtKlien::create([
        'nama' => 'PT Klien 2',
        'alamat' => 'Jl. Klien 2',
        'telepon' => '021-888',
        'email' => 'klien2@pt.com',
        'nama_pic' => 'PIC 2',
        'nomor_kontrak' => 'KTR-002',
        'tgl_mulai' => now()->subYear(),
        'tgl_berakhir' => now()->addYear(),
        'fee_jasa' => 2000000,
    ]);

    $this->invoiceDisetujui = Invoice::create([
        'pt_klien_id' => $ptKlien2->id,
        'periode_id' => $this->periode->id,
        'nomor_invoice' => 'IPM-INV-2025-06-002',
        'tanggal_pembuatan' => now(),
        'subtotal_gaji' => 30000000,
        'fee_jasa' => 2000000,
        'pajak' => 0,
        'total_tagihan' => 32000000,
        'status' => 'disetujui',
        'approved_by' => $this->pemilikPt->id,
        'approved_at' => now(),
    ]);

    // Invoice status: ditolak
    $ptKlien3 = PtKlien::create([
        'nama' => 'PT Klien 3',
        'alamat' => 'Jl. Klien 3',
        'telepon' => '021-777',
        'email' => 'klien3@pt.com',
        'nama_pic' => 'PIC 3',
        'nomor_kontrak' => 'KTR-003',
        'tgl_mulai' => now()->subYear(),
        'tgl_berakhir' => now()->addYear(),
        'fee_jasa' => 1500000,
    ]);

    $this->invoiceDitolak = Invoice::create([
        'pt_klien_id' => $ptKlien3->id,
        'periode_id' => $this->periode->id,
        'nomor_invoice' => 'IPM-INV-2025-06-003',
        'tanggal_pembuatan' => now(),
        'subtotal_gaji' => 20000000,
        'fee_jasa' => 1500000,
        'pajak' => 0,
        'total_tagihan' => 21500000,
        'status' => 'ditolak',
        'rejected_by' => $this->pemilikPt->id,
        'rejected_at' => now(),
        'alasan_penolakan' => 'Data tidak sesuai',
    ]);
});

// ============================================================
// Admin — viewAny=true, view=true, create=true, approve=false, reject=false
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Admin — create and view, no approve/reject', function () {
    it('can viewAny invoices', function () {
        expect($this->policy->viewAny($this->admin))->toBeTrue();
    });

    it('can view any invoice regardless of status', function () {
        expect($this->policy->view($this->admin, $this->invoiceMenunggu))->toBeTrue()
            ->and($this->policy->view($this->admin, $this->invoiceDisetujui))->toBeTrue()
            ->and($this->policy->view($this->admin, $this->invoiceDitolak))->toBeTrue();
    });

    it('can create invoice', function () {
        expect($this->policy->create($this->admin))->toBeTrue();
    });

    it('cannot approve invoice (even menunggu_approval)', function () {
        expect($this->policy->approve($this->admin, $this->invoiceMenunggu))->toBeFalse();
    });

    it('cannot reject invoice (even menunggu_approval)', function () {
        expect($this->policy->reject($this->admin, $this->invoiceMenunggu))->toBeFalse();
    });
});

// ============================================================
// Pemilik_PT — viewAny=true, view=true, create=false,
//              approve=true (menunggu_approval), reject=true (menunggu_approval)
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Pemilik_PT — view and approve/reject workflow', function () {
    it('can viewAny invoices', function () {
        expect($this->policy->viewAny($this->pemilikPt))->toBeTrue();
    });

    it('can view any invoice', function () {
        expect($this->policy->view($this->pemilikPt, $this->invoiceMenunggu))->toBeTrue()
            ->and($this->policy->view($this->pemilikPt, $this->invoiceDisetujui))->toBeTrue()
            ->and($this->policy->view($this->pemilikPt, $this->invoiceDitolak))->toBeTrue();
    });

    it('cannot create invoice', function () {
        expect($this->policy->create($this->pemilikPt))->toBeFalse();
    });

    it('can approve invoice with status menunggu_approval', function () {
        expect($this->policy->approve($this->pemilikPt, $this->invoiceMenunggu))->toBeTrue();
    });

    it('can reject invoice with status menunggu_approval', function () {
        expect($this->policy->reject($this->pemilikPt, $this->invoiceMenunggu))->toBeTrue();
    });

    // Pemilik_PT: approve=false (status bukan menunggu_approval)
    it('cannot approve invoice with status disetujui', function () {
        expect($this->policy->approve($this->pemilikPt, $this->invoiceDisetujui))->toBeFalse();
    });

    it('cannot reject invoice with status disetujui', function () {
        expect($this->policy->reject($this->pemilikPt, $this->invoiceDisetujui))->toBeFalse();
    });

    it('cannot approve invoice with status ditolak', function () {
        expect($this->policy->approve($this->pemilikPt, $this->invoiceDitolak))->toBeFalse();
    });

    it('cannot reject invoice with status ditolak', function () {
        expect($this->policy->reject($this->pemilikPt, $this->invoiceDitolak))->toBeFalse();
    });
});

// ============================================================
// Download — true hanya jika status disetujui
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('download — only disetujui status', function () {
    it('allows admin to download invoice with status disetujui', function () {
        expect($this->policy->download($this->admin, $this->invoiceDisetujui))->toBeTrue();
    });

    it('allows pemilik_pt to download invoice with status disetujui', function () {
        expect($this->policy->download($this->pemilikPt, $this->invoiceDisetujui))->toBeTrue();
    });

    it('denies admin from downloading invoice with status menunggu_approval', function () {
        expect($this->policy->download($this->admin, $this->invoiceMenunggu))->toBeFalse();
    });

    it('denies pemilik_pt from downloading invoice with status menunggu_approval', function () {
        expect($this->policy->download($this->pemilikPt, $this->invoiceMenunggu))->toBeFalse();
    });

    it('denies admin from downloading invoice with status ditolak', function () {
        expect($this->policy->download($this->admin, $this->invoiceDitolak))->toBeFalse();
    });

    it('denies karyawan from downloading any invoice', function () {
        expect($this->policy->download($this->karyawanUser, $this->invoiceDisetujui))->toBeFalse();
    });
});

// ============================================================
// Karyawan — semua false
// ============================================================

// Feature: employee-payroll-system, Property 5: RBAC — Akses Sesuai Peran
describe('Karyawan — no access to invoices', function () {
    it('cannot viewAny invoices', function () {
        expect($this->policy->viewAny($this->karyawanUser))->toBeFalse();
    });

    it('cannot view any invoice', function () {
        expect($this->policy->view($this->karyawanUser, $this->invoiceMenunggu))->toBeFalse()
            ->and($this->policy->view($this->karyawanUser, $this->invoiceDisetujui))->toBeFalse()
            ->and($this->policy->view($this->karyawanUser, $this->invoiceDitolak))->toBeFalse();
    });

    it('cannot create invoice', function () {
        expect($this->policy->create($this->karyawanUser))->toBeFalse();
    });

    it('cannot approve invoice', function () {
        expect($this->policy->approve($this->karyawanUser, $this->invoiceMenunggu))->toBeFalse();
    });

    it('cannot reject invoice', function () {
        expect($this->policy->reject($this->karyawanUser, $this->invoiceMenunggu))->toBeFalse();
    });

    it('cannot download any invoice', function () {
        expect($this->policy->download($this->karyawanUser, $this->invoiceDisetujui))->toBeFalse();
    });
});
