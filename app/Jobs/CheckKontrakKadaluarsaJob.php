<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PtKlien;
use App\Traits\HasAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk cek kontrak PT Klien yang akan berakhir dalam 30 hari.
 *
 * Dijadwalkan berjalan harian via Laravel Scheduler.
 * Menyimpan daftar PT Klien dengan kontrak segera berakhir ke cache
 * agar dapat ditampilkan di dashboard Admin.
 *
 * @see Req 4.4
 */
class CheckKontrakKadaluarsaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasAuditLog;

    public function __construct()
    {
        //
    }

    /**
     * Jalankan pengecekan kontrak yang akan berakhir.
     *
     * Query PT Klien yang tgl_berakhir <= 30 hari dari sekarang dan masih aktif (belum lewat).
     * Simpan hasilnya ke cache selama 24 jam untuk ditampilkan di dashboard.
     */
    public function handle(): void
    {
        $today = now()->toDateString();
        $thirtyDaysFromNow = now()->addDays(30)->toDateString();

        $kontrakSegEraBerakhir = PtKlien::whereBetween('tgl_berakhir', [$today, $thirtyDaysFromNow])
            ->orderBy('tgl_berakhir')
            ->get(['id', 'nama', 'nomor_kontrak', 'tgl_berakhir']);

        // Simpan ke cache selama 24 jam — dashboard Admin membaca dari sini
        Cache::put('kontrak_akan_berakhir', $kontrakSegEraBerakhir->toArray(), now()->addHours(24));

        if ($kontrakSegEraBerakhir->isNotEmpty()) {
            Log::info('CheckKontrakKadaluarsaJob: Ditemukan ' . $kontrakSegEraBerakhir->count() . ' PT Klien dengan kontrak akan berakhir dalam 30 hari.', [
                'pt_klien_ids' => $kontrakSegEraBerakhir->pluck('id')->toArray(),
            ]);

            $this->logActivity(
                'check_kontrak_kadaluarsa',
                [],
                [
                    'jumlah' => $kontrakSegEraBerakhir->count(),
                    'pt_klien_ids' => $kontrakSegEraBerakhir->pluck('id')->toArray(),
                ],
            );
        }
    }
}
