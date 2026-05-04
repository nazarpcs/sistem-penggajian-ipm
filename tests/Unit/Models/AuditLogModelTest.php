<?php

// Feature: employee-payroll-system
// Unit test untuk Model AuditLog — catatan aktivitas kritis di sistem.
// Validates: Req 11 (Audit Log Aktivitas), Property 17: Invariant Audit Log

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('AuditLog Model — Table & Config', function () {
    it('uses audit_logs table', function () {
        $log = new AuditLog();
        expect($log->getTable())->toBe('audit_logs');
    });

    it('has timestamps disabled', function () {
        $log = new AuditLog();
        expect($log->timestamps)->toBeFalse();
    });

    it('has correct fillable attributes', function () {
        $log = new AuditLog();
        expect($log->getFillable())->toBe([
            'user_id',
            'role_pengguna',
            'jenis_aktivitas',
            'model_tipe',
            'model_id',
            'data_lama',
            'data_baru',
            'ip_address',
            'created_at',
        ]);
    });
});

describe('AuditLog Model — Casts', function () {
    it('casts data_lama to array', function () {
        $log = AuditLog::create([
            'jenis_aktivitas' => 'test_cast',
            'data_lama' => ['nama' => 'Lama'],
            'data_baru' => ['nama' => 'Baru'],
            'created_at' => now(),
        ]);

        $log->refresh();

        expect($log->data_lama)->toBeArray();
        expect($log->data_lama['nama'])->toBe('Lama');
    });

    it('casts data_baru to array', function () {
        $log = AuditLog::create([
            'jenis_aktivitas' => 'test_cast_baru',
            'data_baru' => ['status' => 'aktif'],
            'created_at' => now(),
        ]);

        $log->refresh();

        expect($log->data_baru)->toBeArray();
        expect($log->data_baru['status'])->toBe('aktif');
    });

    it('casts created_at to datetime', function () {
        $log = new AuditLog();
        $casts = $log->getCasts();
        expect($casts['created_at'])->toBe('datetime');
    });
});

describe('AuditLog Model — Relations', function () {
    it('belongs to User (nullable)', function () {
        $log = new AuditLog();
        expect($log->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
    });

    it('can be created without user (guest)', function () {
        $log = AuditLog::create([
            'user_id' => null,
            'role_pengguna' => 'guest',
            'jenis_aktivitas' => 'unauthorized_access',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        expect($log->user_id)->toBeNull();
        expect($log->role_pengguna)->toBe('guest');
    });
});
