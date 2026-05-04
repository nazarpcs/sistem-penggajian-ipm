<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel pt_klien menyimpan data perusahaan klien yang menggunakan
     * jasa outsourcing PT IPM, termasuk informasi kontrak dan fee jasa.
     */
    public function up(): void
    {
        Schema::create('pt_klien', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat');
            $table->string('telepon', 20);
            $table->string('email');
            $table->string('nama_pic');
            $table->string('nomor_kontrak');
            $table->date('tgl_mulai');
            $table->date('tgl_berakhir');
            $table->decimal('fee_jasa', 15, 2);
            $table->timestamps();

            $table->index('tgl_berakhir');
            $table->index('nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_klien');
    }
};
