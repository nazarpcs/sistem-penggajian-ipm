<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Traits\HasAuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RBAC — validasi peran pengguna pada level route group.
 *
 * Menerima satu atau lebih peran yang diizinkan (comma-separated).
 * Jika peran user tidak termasuk dalam daftar, catat ke audit log dan abort 403.
 *
 * Penggunaan di route:
 *   Route::middleware(['auth', 'role:admin'])->group(...)
 *   Route::middleware(['auth', 'role:admin,pemilik_pt'])->group(...)
 *
 * @see Req 2.5, 2.6 (validasi hak akses setiap request, 403 + audit log)
 * @see Property 5: RBAC — Akses Sesuai Peran
 */
class CheckRole
{
    use HasAuditLog;

    /**
     * Handle an incoming request.
     *
     * @param string ...$roles Peran yang diizinkan (admin, pemilik_pt, karyawan)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        // Flatten comma-separated roles (mendukung 'role:admin,pemilik_pt')
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $trimmed = trim($r);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        if (in_array($user->role, $allowedRoles, true)) {
            return $next($request);
        }

        // Akses tidak sah — catat ke audit log dengan IP address
        $this->logActivity(
            jenisAktivitas: 'akses_tidak_sah',
            dataBaru: [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'role_user' => $user->role,
                'role_dibutuhkan' => $allowedRoles,
                'ip_address' => $request->ip(),
            ],
            modelTipe: 'User',
            modelId: $user->id,
        );

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
