<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

/**
 * Policy untuk otorisasi akses invoice.
 *
 * Admin: buat dan lihat invoice.
 * Pemilik_PT: lihat, approve, reject invoice.
 * Download hanya untuk invoice berstatus 'disetujui'.
 *
 * @see Req 9.4-9.8 (invoice workflow: buat, approval, reject, download)
 * @see Property 5: RBAC — Akses Sesuai Peran
 */
class InvoicePolicy
{
    /**
     * Admin dan Pemilik_PT dapat melihat daftar invoice.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'pemilik_pt'], true);
    }

    /**
     * Admin dan Pemilik_PT dapat melihat detail invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, ['admin', 'pemilik_pt'], true);
    }

    /**
     * Hanya Admin yang dapat membuat invoice baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Pemilik_PT yang dapat menyetujui invoice.
     *
     * @see Req 9.5
     */
    public function approve(User $user, Invoice $invoice): bool
    {
        return $user->role === 'pemilik_pt'
            && $invoice->status === 'menunggu_approval';
    }

    /**
     * Hanya Pemilik_PT yang dapat menolak invoice.
     *
     * @see Req 9.6, 9.7
     */
    public function reject(User $user, Invoice $invoice): bool
    {
        return $user->role === 'pemilik_pt'
            && $invoice->status === 'menunggu_approval';
    }

    /**
     * Admin dan Pemilik_PT dapat mengunduh invoice PDF,
     * hanya jika status invoice 'disetujui'.
     *
     * @see Req 9.8
     */
    public function download(User $user, Invoice $invoice): bool
    {
        if (! in_array($user->role, ['admin', 'pemilik_pt'], true)) {
            return false;
        }

        return $invoice->status === 'disetujui';
    }
}
