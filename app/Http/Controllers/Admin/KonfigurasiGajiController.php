<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiGaji;
use App\Models\PtKlien;
use App\Traits\HasAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller konfigurasi gaji per PT Klien.
 *
 * Menampilkan dan memperbarui aturan perhitungan gaji:
 * gaji pokok default, jam kerja normal, tarif lembur, potongan, komponen tunjangan.
 *
 * @see Req 4.5, 4.6, 4.7
 * @see Property 17: Invariant Audit Log
 */
class KonfigurasiGajiController extends Controller
{
    use HasAuditLog;

    /**
     * Tampilkan konfigurasi gaji PT Klien.
     *
     * Jika belum ada konfigurasi, tampilkan form kosong untuk pembuatan baru.
     */
    public function show(int $ptKlienId): View
    {
        $ptKlien = PtKlien::findOrFail($ptKlienId);
        $konfigurasi = KonfigurasiGaji::where('pt_klien_id', $ptKlienId)->first();

        return view('admin.pt-klien.konfigurasi-gaji', compact('ptKlien', 'konfigurasi'));
    }

    /**
     * Update (atau buat baru) konfigurasi gaji PT Klien.
     *
     * Perubahan dicatat ke Audit Log. Perubahan hanya berlaku
     * untuk perhitungan gaji periode berikutnya, tidak mengubah data historis.
     *
     * @see Req 4.7
     */
    public function update(Request $request, int $ptKlienId): RedirectResponse
    {
        $ptKlien = PtKlien::findOrFail($ptKlienId);

        $validated = $request->validate([
            'gaji_pokok_default' => ['required', 'numeric', 'min:0'],
            'jam_kerja_normal' => ['required', 'numeric', 'min:1', 'max:24'],
            'tarif_lembur_per_jam' => ['required', 'numeric', 'min:0'],
            'potongan_per_hari' => ['required', 'numeric', 'min:0'],
            'komponen_tunjangan' => ['required', 'array'],
            'komponen_tunjangan.*.nama' => ['required', 'string', 'max:255'],
            'komponen_tunjangan.*.nilai' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($ptKlienId, $validated, $ptKlien): void {
            $existing = KonfigurasiGaji::where('pt_klien_id', $ptKlienId)->first();
            $dataLama = $existing ? $existing->toArray() : [];

            $konfigurasi = KonfigurasiGaji::updateOrCreate(
                ['pt_klien_id' => $ptKlienId],
                $validated,
            );

            $this->logActivity(
                empty($dataLama) ? 'create_konfigurasi_gaji' : 'update_konfigurasi_gaji',
                $dataLama,
                $konfigurasi->toArray(),
                'KonfigurasiGaji',
                $konfigurasi->id,
            );
        });

        return redirect()
            ->route('admin.pt-klien.konfigurasi-gaji.show', $ptKlien->id)
            ->with('success', 'Konfigurasi gaji berhasil diperbarui.');
    }
}
