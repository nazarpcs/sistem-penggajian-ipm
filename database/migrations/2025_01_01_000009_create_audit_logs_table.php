<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel audit_logs mencatat seluruh aktivitas kritis di sistem.
     * user_id nullable untuk menangkap percobaan akses tanpa autentikasi.
     * Data disimpan minimal 1 tahun sesuai requirements.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->string('role_pengguna')->nullable();
            $table->string('jenis_aktivitas');
            $table->string('model_tipe')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('jenis_aktivitas');
            $table->index('created_at');
            $table->index(['model_tipe', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
