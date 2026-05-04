<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Document\GeneratorDokumen;
use App\Domain\Document\GeneratorDokumenInterface;
use App\Domain\Payroll\KalkulatorGaji;
use App\Domain\Payroll\KalkulatorGajiInterface;
use App\Domain\Validation\AbsensiValidator;
use App\Domain\Validation\AbsensiValidatorInterface;
use App\Models\Absensi;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\SlipGaji;
use App\Observers\KaryawanObserver;
use App\Policies\InvoicePolicy;
use App\Policies\KaryawanPolicy;
use App\Policies\SlipGajiPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AbsensiValidatorInterface → AbsensiValidator (Task 7.2)
        $this->app->bind(AbsensiValidatorInterface::class, function ($app) {
            $duplikasiChecker = function (int $karyawanId, string $tanggal): bool {
                return Absensi::where('karyawan_id', $karyawanId)
                    ->where('tanggal', $tanggal)
                    ->exists();
            };

            return new AbsensiValidator($duplikasiChecker);
        });

        // KalkulatorGajiInterface → KalkulatorGaji (Task 8.1)
        $this->app->bind(KalkulatorGajiInterface::class, KalkulatorGaji::class);

        // GeneratorDokumenInterface → GeneratorDokumen (Task 11)
        $this->app->bind(GeneratorDokumenInterface::class, GeneratorDokumen::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        $this->configureRateLimiting();

        // Register KaryawanObserver untuk pembuatan akun otomatis dan sinkronisasi status
        Karyawan::observe(KaryawanObserver::class);

        // @role Blade directive (Task 16.1)
        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->role === $role;
        });
    }

    /**
     * Daftarkan Laravel Policies untuk otorisasi resource-level.
     *
     * @see Req 2.5, 2.6 (RBAC enforcement)
     * @see Property 5: RBAC — Akses Sesuai Peran
     */
    private function registerPolicies(): void
    {
        Gate::policy(Karyawan::class, KaryawanPolicy::class);
        Gate::policy(SlipGaji::class, SlipGajiPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }

    /**
     * Konfigurasi rate limiting untuk endpoint login.
     *
     * @see Req 1.9 (max 10 percobaan login per menit per IP)
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
