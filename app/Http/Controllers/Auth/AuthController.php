<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\HasAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller autentikasi: login, logout.
 *
 * Mendelegasikan logika bisnis ke AuthService.
 * Mencatat aktivitas login/logout ke audit log.
 *
 * @see Req 1.1-1.6 (autentikasi dan session)
 * @see Property 1, 2, 3 (autentikasi, penolakan, logout)
 * @see Property 17: Invariant Audit Log
 */
class AuthController extends Controller
{
    use HasAuditLog;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Tampilkan halaman login.
     *
     * @see Req 1.1
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login pengguna.
     *
     * Alur:
     * 1. Validasi input via LoginRequest
     * 2. Cek kredensial via AuthService
     * 3. Jika gagal: redirect kembali dengan pesan error
     * 4. Jika berhasil: login, regenerate session, redirect ke dashboard
     *
     * @see Req 1.2-1.5 (login, lockout, session)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->authService->attemptLogin(
            email: $validated['email'],
            password: $validated['password'],
        );

        if (! $result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $result['message']]);
        }

        /** @var \App\Models\User $user */
        $user = $result['user'];

        Auth::login($user);

        // Regenerate session ID untuk mencegah session fixation
        $request->session()->regenerate();

        $dashboardRoute = $this->authService->getDashboardRoute($user);

        return redirect()->intended(route($dashboardRoute));
    }

    /**
     * Proses logout pengguna.
     *
     * Alur:
     * 1. Catat logout ke audit log
     * 2. Logout dari Auth guard
     * 3. Invalidate session + regenerate CSRF token
     * 4. Redirect ke halaman login
     *
     * @see Req 1.6 (logout menghapus sesi)
     * @see Property 3: Logout Menghapus Sesi
     */
    public function logout(Request $request): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user !== null) {
            $this->logActivity(
                jenisAktivitas: 'logout',
                dataBaru: [
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                modelTipe: 'User',
                modelId: $user->id,
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
