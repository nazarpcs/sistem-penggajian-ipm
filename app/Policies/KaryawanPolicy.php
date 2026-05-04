<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Karyawan;
use App\Models\User;

/**
 * Policy untuk otorisasi akses data karyawan.
 *
 * Admin: akses penuh ke CRUD karyawan.
 * Karyawan: hanya dapat melihat dan mengupdate profil miliknya sendiri (terbatas).
 *
 * @see Req 2.2 (Admin: akses penuh manajemen data)
 * @see Req 2.4 (Karyawan: akses data diri sendiri)
 * @see Req 8.5, 12.4 (Isolasi data karyawan)
 * @see Property 5: RBAC — Akses Sesuai Peran
 * @see Property 6: Isolasi Data Karyawan
 */
class KaryawanPolicy
{
    /**
     * Admin dapat melihat daftar seluruh karyawan.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dapat melihat detail karyawan manapun.
     * Karyawan hanya dapat melihat data miliknya sendiri.
     *
     * @see Req 2.4, 12.4
     */
    public function view(User $user, Karyawan $karyawan): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'karyawan' && $user->karyawan !== null) {
            return $user->karyawan->id === $karyawan->id;
        }

        return false;
    }

    /**
     * Admin dapat menambahkan karyawan baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dapat memperbarui data karyawan manapun.
     * Karyawan dapat mengupdate profil miliknya sendiri (terbatas).
     *
     * @see Req 2.4 (Karyawan: akses data diri sendiri)
     */
    public function update(User $user, Karyawan $karyawan): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'karyawan' && $user->karyawan !== null) {
            return $user->karyawan->id === $karyawan->id;
        }

        return false;
    }

    /**
     * Admin dapat menghapus karyawan.
     */
    public function delete(User $user, Karyawan $karyawan): bool
    {
        return $user->role === 'admin';
    }
}
