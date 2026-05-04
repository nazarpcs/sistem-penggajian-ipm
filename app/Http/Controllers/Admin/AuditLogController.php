<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller audit log — list dan filter log aktivitas sistem.
 *
 * Hanya Admin yang dapat mengakses audit log.
 *
 * @see Req 11.1-11.5
 * @see Property 17: Invariant Audit Log
 */
class AuditLogController extends Controller
{
    /**
     * List audit log dengan filter.
     *
     * Filter: user_id, jenis_aktivitas, tanggal_dari, tanggal_sampai.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'user_id',
            'jenis_aktivitas',
            'tanggal_dari',
            'tanggal_sampai',
        ]);

        $query = AuditLog::query()->with('user');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['jenis_aktivitas'])) {
            $query->where('jenis_aktivitas', $filters['jenis_aktivitas']);
        }

        if (!empty($filters['tanggal_dari'])) {
            $query->where('created_at', '>=', $filters['tanggal_dari'] . ' 00:00:00');
        }

        if (!empty($filters['tanggal_sampai'])) {
            $query->where('created_at', '<=', $filters['tanggal_sampai'] . ' 23:59:59');
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Ambil daftar jenis aktivitas unik untuk dropdown filter
        $jenisAktivitasList = AuditLog::select('jenis_aktivitas')
            ->distinct()
            ->orderBy('jenis_aktivitas')
            ->pluck('jenis_aktivitas');

        return view('admin.audit-log.index', compact('auditLogs', 'filters', 'jenisAktivitasList'));
    }
}
