<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel invoice menyimpan tagihan ke PT Klien per periode penggajian.
     * Unique constraint pada pt_klien_id + periode_id mencegah duplikasi invoice.
     * Nomor invoice di-enforce unique di level database untuk concurrency safety.
     */
    public function up(): void
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pt_klien_id')
                ->constrained('pt_klien')
                ->onDelete('restrict');
            $table->foreignId('periode_id')
                ->constrained('periode_penggajian')
                ->onDelete('restrict');
            $table->string('nomor_invoice')->unique();
            $table->date('tanggal_pembuatan');
            $table->decimal('subtotal_gaji', 15, 2);
            $table->decimal('fee_jasa', 15, 2);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('total_tagihan', 15, 2);
            $table->enum('status', ['menunggu_approval', 'disetujui', 'ditolak'])
                ->default('menunggu_approval');
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamp('rejected_at')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->unique(['pt_klien_id', 'periode_id']);
            $table->index('status');
            $table->index('tanggal_pembuatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
