<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SlipGaji;
use App\Models\User;

/**
 * Policy untuk otorisasi akses slip gaji.
 *
 * Admin: akses semua slip gaji.
 * Karyawan: hanya akses slip miliknya sendiri (isolasi data).
 *
 * @see Req 8.5, 8.6 (akses slip gaji per peran)
 * @see Req 12.4 (isolasi data karyawan)
 * @see Property 5: RBAC — Akses Sesuai Peran
 * @see Property 6: Isolasi Data Karyawan
 */
class SlipGajiPolicy
{
    /**
     * Admin dapat melihat daftar seluruh slip gaji.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dapat melihat semua slip gaji.
     * Karyawan hanya dapat melihat slip miliknya sendiri.
     */
    public function view(User $user, SlipGaji $slipGaji): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'karyawan' && $user->karyawan !== null) {
            return $user->karyawan->id === $slipGaji->karyawan_id;
        }

        return false;
    }

    /**
     * Download slip gaji — aturan sama dengan view.
     *
     * Admin: semua slip.
     * Karyawan: hanya slip miliknya sendiri.
     */
    public function download(User $user, SlipGaji $slipGaji): bool
    {
        return $this->view($user, $slipGaji);
    }
}
