<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel komponen_slip_gaji menyimpan rincian komponen tunjangan dan potongan
     * per slip gaji. Cascade delete memastikan komponen terhapus saat slip dihapus.
     */
    public function up(): void
    {
        Schema::create('komponen_slip_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slip_gaji_id')
                ->constrained('slip_gaji')
                ->onDelete('cascade');
            $table->enum('tipe', ['tunjangan', 'potongan']);
            $table->string('nama_komponen');
            $table->decimal('nilai', 15, 2);
            $table->timestamp('created_at')->nullable();

            $table->index('slip_gaji_id');
            $table->index('tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_slip_gaji');
    }
};
