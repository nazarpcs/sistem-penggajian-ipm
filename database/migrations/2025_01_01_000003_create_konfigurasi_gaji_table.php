<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel konfigurasi_gaji menyimpan aturan perhitungan gaji per PT Klien,
     * termasuk komponen tunjangan dalam format JSON untuk fleksibilitas.
     */
    public function up(): void
    {
        Schema::create('konfigurasi_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pt_klien_id')
                ->constrained('pt_klien')
                ->onDelete('cascade');
            $table->decimal('gaji_pokok_default', 15, 2);
            $table->decimal('jam_kerja_normal', 4, 2)->default(8.00);
            $table->decimal('tarif_lembur_per_jam', 15, 2);
            $table->decimal('potongan_per_hari', 15, 2);
            $table->json('komponen_tunjangan');
            $table->timestamps();

            $table->index('pt_klien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_gaji');
    }
};
