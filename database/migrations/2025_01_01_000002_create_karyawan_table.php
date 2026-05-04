<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel karyawan menyimpan data lengkap karyawan outsourcing
     * dengan relasi ke users (akun login) dan pt_klien (penempatan).
     */
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('pt_klien_id')
                ->constrained('pt_klien')
                ->onDelete('restrict');
            $table->string('nik', 20)->unique();
            $table->string('nama_lengkap');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('telepon', 20);
            $table->string('jabatan');
            $table->decimal('gaji_pokok', 15, 2);
            $table->date('tanggal_bergabung');
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->index('pt_klien_id');
            $table->index('status_aktif');
            $table->index('nama_lengkap');
            $table->index('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
