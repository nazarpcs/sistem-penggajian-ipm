<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model SlipGaji — hasil perhitungan gaji per karyawan per periode.
 *
 * Data bersifat immutable setelah status 'final'.
 * Perubahan konfigurasi gaji tidak mempengaruhi slip yang sudah tersimpan.
 *
 * @property int $id
 * @property int $karyawan_id
 * @property int $periode_id
 * @property float $gaji_pokok
 * @property float $total_tunjangan
 * @property float $total_lembur
 * @property float $jam_lembur
 * @property float $total_potongan
 * @property float $gaji_bersih
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SlipGaji extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'slip_gaji';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'karyawan_id',
        'periode_id',
        'gaji_pokok',
        'total_tunjangan',
        'total_lembur',
        'jam_lembur',
        'total_potongan',
        'gaji_bersih',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'total_tunjangan' => 'decimal:2',
            'total_lembur' => 'decimal:2',
            'jam_lembur' => 'decimal:2',
            'total_potongan' => 'decimal:2',
            'gaji_bersih' => 'decimal:2',
        ];
    }

    /**
     * Relasi: SlipGaji milik satu Karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Relasi: SlipGaji milik satu PeriodePenggajian.
     */
    public function periodePenggajian(): BelongsTo
    {
        return $this->belongsTo(PeriodePenggajian::class, 'periode_id');
    }

    /**
     * Relasi: SlipGaji memiliki banyak KomponenSlipGaji.
     */
    public function komponenSlipGaji(): HasMany
    {
        return $this->hasMany(KomponenSlipGaji::class, 'slip_gaji_id');
    }
}
