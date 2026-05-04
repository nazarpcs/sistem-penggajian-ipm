<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel slip_gaji menyimpan hasil perhitungan gaji per karyawan per periode.
     * Data bersifat immutable setelah status 'final' — perubahan konfigurasi gaji
     * tidak mempengaruhi slip yang sudah tersimpan (Property 14).
     */
    public function up(): void
    {
        Schema::create('slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')
                ->constrained('karyawan')
                ->onDelete('restrict');
            $table->foreignId('periode_id')
                ->constrained('periode_penggajian')
                ->onDelete('restrict');
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('total_tunjangan', 15, 2);
            $table->decimal('total_lembur', 15, 2);
            $table->decimal('jam_lembur', 4, 2);
            $table->decimal('total_potongan', 15, 2);
            $table->decimal('gaji_bersih', 15, 2);
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();

            $table->index('karyawan_id');
            $table->index('periode_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_gaji');
    }
};
