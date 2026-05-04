<?php

// Feature: employee-payroll-system
// Unit test untuk Model Invoice — tagihan ke PT Klien per periode penggajian.
// Validates: Req 9 (Pembuatan dan Pengelolaan Invoice)

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Invoice Model — Table & Fillable', function () {
    it('uses invoice table', function () {
        $invoice = new Invoice();
        expect($invoice->getTable())->toBe('invoice');
    });

    it('has correct fillable attributes', function () {
        $invoice = new Invoice();
        expect($invoice->getFillable())->toBe([
            'pt_klien_id',
            'periode_id',
            'nomor_invoice',
            'tanggal_pembuatan',
            'subtotal_gaji',
            'fee_jasa',
            'pajak',
            'total_tagihan',
            'status',
            'approved_by',
            'approved_at',
            'rejected_by',
            'rejected_at',
            'alasan_penolakan',
        ]);
    });
});

describe('Invoice Model — Casts', function () {
    it('casts all decimal fields correctly', function () {
        $invoice = new Invoice();
        $casts = $invoice->getCasts();

        expect($casts['subtotal_gaji'])->toBe('decimal:2');
        expect($casts['fee_jasa'])->toBe('decimal:2');
        expect($casts['pajak'])->toBe('decimal:2');
        expect($casts['total_tagihan'])->toBe('decimal:2');
    });

    it('casts date and datetime fields correctly', function () {
        $invoice = new Invoice();
        $casts = $invoice->getCasts();

        expect($casts['tanggal_pembuatan'])->toBe('date');
        expect($casts['approved_at'])->toBe('datetime');
        expect($casts['rejected_at'])->toBe('datetime');
    });
});

describe('Invoice Model — Relations', function () {
    it('belongs to PtKlien', function () {
        $invoice = new Invoice();
        expect($invoice->ptKlien())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('belongs to PeriodePenggajian', function () {
        $invoice = new Invoice();
        expect($invoice->periodePenggajian())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('belongs to User as approvedByUser', function () {
        $invoice = new Invoice();
        $relation = $invoice->approvedByUser();
        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        expect($relation->getForeignKeyName())->toBe('approved_by');
    });

    it('belongs to User as rejectedByUser', function () {
        $invoice = new Invoice();
        $relation = $invoice->rejectedByUser();
        expect($relation)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
        expect($relation->getForeignKeyName())->toBe('rejected_by');
    });
});
