<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Invoice — tagihan ke PT Klien per periode penggajian.
 *
 * Unique constraint pada pt_klien_id + periode_id mencegah duplikasi.
 * Nomor invoice di-enforce unique di level database.
 *
 * @property int $id
 * @property int $pt_klien_id
 * @property int $periode_id
 * @property string $nomor_invoice
 * @property \Illuminate\Support\Carbon $tanggal_pembuatan
 * @property float $subtotal_gaji
 * @property float $fee_jasa
 * @property float $pajak
 * @property float $total_tagihan
 * @property string $status
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $rejected_by
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property string|null $alasan_penolakan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Invoice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pt_klien_id',
        'periode_id',
        'nomor_invoice',
        'tanggal_pembuatan',
        'subtotal_gaji',
        'fee_jasa',
        'pajak',
        'total_tagihan',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'alasan_penolakan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pembuatan' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'subtotal_gaji' => 'decimal:2',
            'fee_jasa' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Invoice milik satu PtKlien.
     */
    public function ptKlien(): BelongsTo
    {
        return $this->belongsTo(PtKlien::class, 'pt_klien_id');
    }

    /**
     * Relasi: Invoice milik satu PeriodePenggajian.
     */
    public function periodePenggajian(): BelongsTo
    {
        return $this->belongsTo(PeriodePenggajian::class, 'periode_id');
    }

    /**
     * Relasi: Invoice disetujui oleh satu User.
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi: Invoice ditolak oleh satu User.
     */
    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
