<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel periode_penggajian mendefinisikan rentang waktu satu bulan
     * sebagai dasar perhitungan gaji. Status 'terkunci' mencegah
     * perubahan data absensi pada periode tersebut.
     */
    public function up(): void
    {
        Schema::create('periode_penggajian', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan');
            $table->smallInteger('tahun');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['aktif', 'terkunci'])->default('aktif');
            $table->timestamps();

            $table->unique(['bulan', 'tahun']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_penggajian');
    }
};
