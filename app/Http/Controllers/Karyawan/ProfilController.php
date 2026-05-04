<?php

declare(strict_types=1);

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Traits\HasAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller profil karyawan (self-service).
 *
 * Karyawan hanya dapat melihat dan mengupdate data diri terbatas.
 * Isolasi data via auth: hanya data milik karyawan yang login.
 *
 * @see Req 2.4 (akses karyawan: data diri sendiri)
 * @see Property 6: Isolasi Data Karyawan
 */
class ProfilController extends Controller
{
    use HasAuditLog;

    /**
     * Tampilkan profil karyawan yang sedang login.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            abort(404, 'Data karyawan tidak ditemukan.');
        }

        $karyawan->load('ptKlien');

        return view('karyawan.profil.show', compact('karyawan'));
    }

    /**
     * Update data diri karyawan (terbatas: alamat, telepon).
     *
     * Field yang boleh diubah oleh karyawan sendiri dibatasi
     * untuk mencegah perubahan data sensitif.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            abort(404, 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'alamat' => ['required', 'string', 'max:500'],
            'telepon' => ['required', 'string', 'max:20'],
        ]);

        $dataLama = $karyawan->only(['alamat', 'telepon']);

        $karyawan->update($validated);

        $this->logActivity(
            'update_profil',
            $dataLama,
            $validated,
            'Karyawan',
            $karyawan->id,
        );

        return redirect()->route('karyawan.profil.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
