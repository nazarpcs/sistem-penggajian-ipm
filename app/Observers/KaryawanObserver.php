<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Karyawan;
use App\Models\User;
use App\Notifications\KredensialKaryawanNotification;
use App\Traits\HasAuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * Observer untuk model Karyawan.
 *
 * Menangani:
 * - Pembuatan akun User otomatis saat karyawan baru ditambahkan
 * - Pengiriman notifikasi kredensial login ke email karyawan
 * - Sinkronisasi status aktif antara Karyawan dan User
 * - Pengecekan data terkait sebelum penghapusan
 *
 * @see Req 3.2, 3.3, 3.5, 3.7
 * @see Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat
 * @see Property 9: Sinkronisasi Status Karyawan dan Akun Login
 * @see Property 17: Invariant Audit Log
 */
class KaryawanObserver
{
    use HasAuditLog;

    /**
     * Handle the Karyawan "creating" event.
     *
     * Jika user_id belum di-set, buat akun User otomatis dengan:
     * - Email dari atribut sementara pada model
     * - Role 'karyawan'
     * - Password sementara acak (12 karakter)
     *
     * @see Req 3.2 (auto-create akun login)
     */
    public function creating(Karyawan $karyawan): void
    {
        if ($karyawan->user_id) {
            return;
        }

        $email = $karyawan->getTemporaryEmail();
        if (!$email) {
            return;
        }

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $karyawan->nama_lengkap,
            'email' => $email,
            'password' => Hash::make($temporaryPassword),
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        $karyawan->user_id = $user->id;
        $karyawan->setTemporaryPassword($temporaryPassword);
    }

    /**
     * Handle the Karyawan "created" event.
     *
     * Kirim notifikasi kredensial login ke email karyawan baru
     * dan catat pembuatan akun ke audit log.
     *
     * @see Req 3.3 (kirim notifikasi kredensial)
     * @see Property 17: Invariant Audit Log
     */
    public function created(Karyawan $karyawan): void
    {
        $temporaryPassword = $karyawan->getTemporaryPassword();
        if (!$temporaryPassword) {
            return;
        }

        $user = $karyawan->user;
        if ($user) {
            $user->notify(new KredensialKaryawanNotification(
                password: $temporaryPassword,
                namaKaryawan: $karyawan->nama_lengkap,
            ));

            $this->logActivity(
                jenisAktivitas: 'create_user_karyawan',
                dataBaru: [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => 'karyawan',
                    'karyawan_id' => $karyawan->id,
                    'nama_lengkap' => $karyawan->nama_lengkap,
                ],
                modelTipe: 'User',
                modelId: $user->id,
            );
        }

        $karyawan->clearTemporaryPassword();
    }

    /**
     * Handle the Karyawan "updated" event.
     *
     * Sinkronisasi status_aktif: nonaktifkan/aktifkan akun login
     * saat status karyawan berubah.
     *
     * @see Req 3.7 (sinkronisasi status karyawan dan akun)
     * @see Property 9: Sinkronisasi Status Karyawan dan Akun Login
     */
    public function updated(Karyawan $karyawan): void
    {
        if (!$karyawan->wasChanged('status_aktif')) {
            return;
        }

        $user = $karyawan->user;
        if (!$user) {
            return;
        }

        $statusLama = !$karyawan->status_aktif;
        $statusBaru = $karyawan->status_aktif;

        $user->update([
            'is_active' => $statusBaru,
        ]);

        $this->logActivity(
            jenisAktivitas: 'sync_status_karyawan',
            dataLama: [
                'karyawan_id' => $karyawan->id,
                'user_id' => $user->id,
                'status_aktif' => $statusLama,
                'is_active' => $statusLama,
            ],
            dataBaru: [
                'karyawan_id' => $karyawan->id,
                'user_id' => $user->id,
                'status_aktif' => $statusBaru,
                'is_active' => $statusBaru,
            ],
            modelTipe: 'Karyawan',
            modelId: $karyawan->id,
        );
    }

    /**
     * Handle the Karyawan "deleting" event.
     *
     * Cek apakah karyawan memiliki data terkait (absensi/slip gaji).
     * Jika ada dan penghapusan belum dikonfirmasi (force), batalkan.
     *
     * Pengecekan force dilakukan di KaryawanService sebelum memanggil delete().
     * Observer ini berfungsi sebagai safety net terakhir untuk mencatat
     * penghapusan ke audit log.
     *
     * @see Req 3.5 (peringatan sebelum hapus karyawan dengan data terkait)
     */
    public function deleting(Karyawan $karyawan): void
    {
        $hasAbsensi = $karyawan->absensi()->exists();
        $hasSlipGaji = $karyawan->slipGaji()->exists();

        $this->logActivity(
            jenisAktivitas: 'delete_karyawan',
            dataLama: array_merge($karyawan->toArray(), [
                'has_related_absensi' => $hasAbsensi,
                'has_related_slip_gaji' => $hasSlipGaji,
            ]),
            modelTipe: 'Karyawan',
            modelId: $karyawan->id,
        );
    }
}
