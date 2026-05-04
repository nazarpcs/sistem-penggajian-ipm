<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\PeriodePenggajian;
use App\Models\PtKlien;
use App\Models\SlipGaji;
use App\Traits\HasAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service invoice: buat invoice, list, detail, generate nomor.
 *
 * Mengorkestrasi pembuatan invoice dengan locking untuk nomor unik,
 * perhitungan subtotal dari slip gaji, dan audit log.
 *
 * @see Req 9.1-9.10
 * @see Property 15: Format dan Uniqueness Nomor Invoice
 * @see Property 16: Pencegahan Duplikasi Invoice
 * @see Property 19: Atomicity Generate Nomor Invoice
 */
class InvoiceService
{
    use HasAuditLog;

    /**
     * Buat invoice baru untuk PT Klien pada periode tertentu.
     *
     * Proses:
     * 1. Cek duplikasi invoice (PT Klien + Periode)
     * 2. Generate nomor invoice dengan DB lock (Property 19)
     * 3. Hitung subtotal gaji dari slip_gaji karyawan
     * 4. Hitung total tagihan = subtotal + fee_jasa + pajak
     * 5. Simpan invoice dengan status 'menunggu_approval'
     * 6. Catat ke audit log
     *
     * @param int $ptKlienId ID PT Klien
     * @param int $periodeId ID Periode Penggajian
     * @return array{success: bool, message: string, data?: Invoice, code?: int}
     */
    public function buatInvoice(int $ptKlienId, int $periodeId): array
    {
        $ptKlien = PtKlien::find($ptKlienId);
        if (!$ptKlien) {
            return ['success' => false, 'message' => 'PT Klien tidak ditemukan.', 'code' => 404];
        }

        $periode = PeriodePenggajian::find($periodeId);
        if (!$periode) {
            return ['success' => false, 'message' => 'Periode penggajian tidak ditemukan.', 'code' => 404];
        }

        // Property 16: Cek duplikasi invoice
        $existing = Invoice::where('pt_klien_id', $ptKlienId)
            ->where('periode_id', $periodeId)
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'Invoice untuk PT Klien dan periode ini sudah pernah dibuat.',
                'code' => 422,
            ];
        }

        // Hitung subtotal gaji dari slip_gaji karyawan PT Klien pada periode
        $subtotalGaji = SlipGaji::where('periode_id', $periodeId)
            ->whereHas('karyawan', function ($q) use ($ptKlienId) {
                $q->where('pt_klien_id', $ptKlienId);
            })
            ->sum('gaji_bersih');

        if ((float) $subtotalGaji <= 0) {
            return [
                'success' => false,
                'message' => 'Tidak ada slip gaji untuk PT Klien pada periode ini. Lakukan perhitungan gaji terlebih dahulu.',
                'code' => 422,
            ];
        }

        $feeJasa = (float) $ptKlien->fee_jasa;
        $pajak = 0.00; // Pajak opsional, default 0
        $totalTagihan = (float) $subtotalGaji + $feeJasa + $pajak;

        // Property 19: DB transaction + lockForUpdate untuk nomor invoice unik
        return DB::transaction(function () use ($ptKlien, $periode, $subtotalGaji, $feeJasa, $pajak, $totalTagihan): array {
            $nomorInvoice = $this->generateNomorInvoice($ptKlien, $periode);

            $invoice = Invoice::create([
                'pt_klien_id' => $ptKlien->id,
                'periode_id' => $periode->id,
                'nomor_invoice' => $nomorInvoice,
                'tanggal_pembuatan' => now()->toDateString(),
                'subtotal_gaji' => $subtotalGaji,
                'fee_jasa' => $feeJasa,
                'pajak' => $pajak,
                'total_tagihan' => $totalTagihan,
                'status' => 'menunggu_approval',
            ]);

            // Audit log
            $this->logActivity(
                'buat_invoice',
                [],
                $invoice->toArray(),
                'Invoice',
                $invoice->id,
            );

            return [
                'success' => true,
                'message' => "Invoice {$nomorInvoice} berhasil dibuat.",
                'data' => $invoice,
            ];
        });
    }

    /**
     * List invoice dengan filter dan paginasi.
     *
     * @param array<string, mixed> $filters Filter: pt_klien_id, periode_id, status
     * @param int $perPage Jumlah data per halaman
     * @return LengthAwarePaginator
     */
    public function listInvoice(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with(['ptKlien', 'periodePenggajian']);

        if (!empty($filters['pt_klien_id'])) {
            $query->where('pt_klien_id', $filters['pt_klien_id']);
        }

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Detail invoice dengan relasi lengkap.
     *
     * @param int $id ID invoice
     * @return array{success: bool, data?: Invoice, error?: string, code?: int}
     */
    public function detailInvoice(int $id): array
    {
        $invoice = Invoice::with([
            'ptKlien',
            'periodePenggajian',
            'approvedByUser',
            'rejectedByUser',
        ])->find($id);

        if (!$invoice) {
            return ['success' => false, 'error' => 'Invoice tidak ditemukan.', 'code' => 404];
        }

        return ['success' => true, 'data' => $invoice];
    }

    /**
     * Approve invoice oleh Pemilik PT.
     *
     * @param int $id ID invoice
     * @param int $userId ID user yang menyetujui
     * @return array{success: bool, message: string, data?: Invoice, code?: int}
     */
    public function approveInvoice(int $id, int $userId): array
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.', 'code' => 404];
        }

        if ($invoice->status !== 'menunggu_approval') {
            return ['success' => false, 'message' => 'Invoice tidak dalam status menunggu approval.', 'code' => 422];
        }

        $dataLama = $invoice->toArray();

        $invoice->update([
            'status' => 'disetujui',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        $this->logActivity(
            'approve_invoice',
            $dataLama,
            $invoice->fresh()->toArray(),
            'Invoice',
            $invoice->id,
        );

        return [
            'success' => true,
            'message' => "Invoice {$invoice->nomor_invoice} berhasil disetujui.",
            'data' => $invoice->fresh(),
        ];
    }

    /**
     * Reject invoice oleh Pemilik PT.
     *
     * @param int $id ID invoice
     * @param int $userId ID user yang menolak
     * @param string $alasan Alasan penolakan (wajib)
     * @return array{success: bool, message: string, data?: Invoice, code?: int}
     */
    public function rejectInvoice(int $id, int $userId, string $alasan): array
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice tidak ditemukan.', 'code' => 404];
        }

        if ($invoice->status !== 'menunggu_approval') {
            return ['success' => false, 'message' => 'Invoice tidak dalam status menunggu approval.', 'code' => 422];
        }

        $dataLama = $invoice->toArray();

        $invoice->update([
            'status' => 'ditolak',
            'rejected_by' => $userId,
            'rejected_at' => now(),
            'alasan_penolakan' => $alasan,
        ]);

        $this->logActivity(
            'reject_invoice',
            $dataLama,
            $invoice->fresh()->toArray(),
            'Invoice',
            $invoice->id,
        );

        return [
            'success' => true,
            'message' => "Invoice {$invoice->nomor_invoice} ditolak.",
            'data' => $invoice->fresh(),
        ];
    }

    /**
     * Generate nomor invoice unik dengan format IPM-{KODE_KLIEN}-{YYYY}-{MM}-{NNN}.
     *
     * Menggunakan lockForUpdate() untuk mencegah race condition (Property 19).
     * Kode klien = 3 huruf pertama nama PT uppercase.
     *
     * @param PtKlien $ptKlien
     * @param PeriodePenggajian $periode
     * @return string
     */
    private function generateNomorInvoice(PtKlien $ptKlien, PeriodePenggajian $periode): string
    {
        $kodeKlien = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $ptKlien->nama), 0, 3));
        $tahun = str_pad((string) $periode->tahun, 4, '0', STR_PAD_LEFT);
        $bulan = str_pad((string) $periode->bulan, 2, '0', STR_PAD_LEFT);

        $prefix = "IPM-{$kodeKlien}-{$tahun}-{$bulan}-";

        // Lock untuk mencegah race condition pada nomor urut
        $lastInvoice = Invoice::where('nomor_invoice', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('nomor_invoice', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->nomor_invoice, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
