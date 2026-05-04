<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\HasAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controller reset password: forgot form, send link, reset form, reset password.
 *
 * @see Req 1.8 (reset password via email, token kedaluwarsa 60 menit)
 * @see Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt
 */
class PasswordResetController extends Controller
{
    use HasAuditLog;

    /**
     * Tampilkan form lupa password.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email pengguna.
     *
     * @see Req 1.8
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->logActivity(
                jenisAktivitas: 'password_reset_request',
                dataBaru: ['email' => $request->input('email')],
            );

            return back()->with('status', 'Link reset password telah dikirim ke email Anda.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email tidak ditemukan dalam sistem.']);
    }

    /**
     * Tampilkan form reset password dengan token.
     */
    public function showResetForm(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Proses reset password.
     *
     * @see Req 1.8 (reset password)
     * @see Property 4: Password Selalu Tersimpan Sebagai Hash Bcrypt
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $this->logActivity(
                    jenisAktivitas: 'password_reset',
                    dataBaru: ['email' => $user->email],
                    modelTipe: 'User',
                    modelId: $user->id,
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Password berhasil direset. Silakan login dengan password baru.');
        }

        return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kedaluwarsa.']);
    }
}
