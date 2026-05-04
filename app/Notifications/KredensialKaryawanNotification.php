<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi kredensial login untuk karyawan baru.
 *
 * Dikirim otomatis oleh KaryawanObserver saat Admin menambahkan karyawan baru.
 * Berisi nama, email, dan password sementara untuk login pertama kali,
 * beserta instruksi untuk segera mengganti password.
 *
 * @see Req 3.2, 3.3
 * @see Property 8: Pembuatan Akun Otomatis Saat Karyawan Baru Dibuat
 */
class KredensialKaryawanNotification extends Notification
{
    use Queueable;

    /**
     * @param string $password Password sementara yang di-generate
     * @param string $namaKaryawan Nama lengkap karyawan
     */
    public function __construct(
        private readonly string $password,
        private readonly string $namaKaryawan,
    ) {}

    /**
     * Channel pengiriman notifikasi.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Buat pesan email berisi kredensial login dan instruksi ganti password.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/login');

        return (new MailMessage())
            ->subject('Kredensial Login Sistem Penggajian PT IPM')
            ->greeting('Halo, ' . $this->namaKaryawan . '!')
            ->line('Akun Anda telah dibuat di Sistem Penggajian PT Indah Permata Mandiri.')
            ->line('Berikut adalah kredensial login Anda:')
            ->line('**Nama:** ' . $this->namaKaryawan)
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password Sementara:** ' . $this->password)
            ->action('Login ke Sistem', $loginUrl)
            ->line('⚠️ PENTING: Demi keamanan akun Anda, segera ubah password setelah login pertama kali.')
            ->line('Password sementara ini hanya untuk akses awal. Jangan bagikan kredensial ini kepada siapapun.')
            ->salutation('Salam, Tim PT Indah Permata Mandiri');
    }

    /**
     * Representasi array dari notifikasi (untuk database channel jika diperlukan).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'nama_karyawan' => $this->namaKaryawan,
            'email' => $notifiable->email,
            'message' => 'Kredensial login telah dikirim ke email karyawan.',
        ];
    }
}
