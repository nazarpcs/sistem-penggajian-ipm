<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Traits\HasAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Service autentikasi — mengelola logika login, lockout, dan session.
 *
 * @see Req 1.2-1.7 (autentikasi, lockout, session)
 * @see Property 1: Autentikasi Kredensial Valid
 * @see Property 2: Penolakan Kredensial Tidak Valid
 * @see Property 17: Invariant Audit Log
 */
class AuthService
{
    use HasAuditLog;

    /**
     * Batas maksimal percobaan login gagal sebelum akun dikunci.
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Durasi penguncian akun dalam menit.
     */
    private const LOCKOUT_DURATION_MINUTES = 15;

    /**
     * Coba login dengan email dan password.
     *
     * @return array{success: bool, message: string, user: User|null}
     */
    public function attemptLogin(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Email atau password salah.',
                'user' => null,
            ];
        }

        if (! $this->isAccountActive($user)) {
            return [
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
                'user' => null,
            ];
        }

        if ($this->isAccountLocked($user)) {
            $minutesLeft = (int) now()->diffInMinutes($user->locked_until, false);

            return [
                'success' => false,
                'message' => "Akun Anda terkunci. Coba lagi dalam {$minutesLeft} menit.",
                'user' => null,
            ];
        }

        if (! Hash::check($password, $user->password)) {
            $this->handleFailedLogin($user);

            if ($this->isAccountLocked($user)) {
                return [
                    'success' => false,
                    'message' => 'Akun Anda terkunci selama ' . self::LOCKOUT_DURATION_MINUTES . ' menit karena terlalu banyak percobaan login gagal.',
                    'user' => null,
                ];
            }

            $attemptsLeft = self::MAX_LOGIN_ATTEMPTS - $user->login_attempts;

            return [
                'success' => false,
                'message' => "Email atau password salah. Sisa percobaan: {$attemptsLeft}.",
                'user' => null,
            ];
        }

        $this->handleSuccessfulLogin($user);

        return [
            'success' => true,
            'message' => 'Login berhasil.',
            'user' => $user,
        ];
    }

    /**
     * Tangani percobaan login gagal: increment counter, kunci jika >= 5x.
     *
     * @see Req 1.4 (account lockout setelah 5x gagal)
     */
    public function handleFailedLogin(User $user): void
    {
        $attempts = $user->login_attempts + 1;
        $data = ['login_attempts' => $attempts];

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $data['locked_until'] = now()->addMinutes(self::LOCKOUT_DURATION_MINUTES);
        }

        $user->update($data);

        $this->logActivity(
            jenisAktivitas: 'login_gagal',
            dataBaru: [
                'email' => $user->email,
                'login_attempts' => $attempts,
                'locked' => $attempts >= self::MAX_LOGIN_ATTEMPTS,
            ],
            modelTipe: 'User',
            modelId: $user->id,
        );
    }

    /**
     * Tangani login berhasil: reset counter, set last_login.
     *
     * @see Req 1.5 (session management)
     */
    public function handleSuccessfulLogin(User $user): void
    {
        $user->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login' => now(),
        ]);

        $this->logActivity(
            jenisAktivitas: 'login',
            dataBaru: [
                'email' => $user->email,
                'role' => $user->role,
            ],
            modelTipe: 'User',
            modelId: $user->id,
        );
    }

    /**
     * Cek apakah akun sedang terkunci.
     *
     * @see Req 1.4
     */
    public function isAccountLocked(User $user): bool
    {
        return $user->locked_until !== null && $user->locked_until->isFuture();
    }

    /**
     * Cek apakah akun aktif.
     *
     * @see Req 3.7 (sinkronisasi status karyawan dan akun)
     */
    public function isAccountActive(User $user): bool
    {
        return (bool) $user->is_active;
    }

    /**
     * Dapatkan route dashboard berdasarkan role user.
     *
     * @see Req 2.2-2.4 (redirect sesuai peran)
     */
    public function getDashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin' => 'admin.dashboard',
            'pemilik_pt' => 'owner.dashboard',
            'karyawan' => 'karyawan.dashboard',
            default => 'login',
        };
    }
}
