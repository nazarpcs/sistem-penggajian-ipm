<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Absensi — catatan kehadiran harian karyawan.
 *
 * Unique constraint pada karyawan_id + tanggal mencegah duplikasi.
 * Status kehadiran: Hadir, Izin, Sakit, Alpha.
 *
 * @property int $id
 * @property int $karyawan_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $status_kehadiran
 * @property string|null $jam_masuk
 * @property string|null $jam_keluar
 * @property float $jam_lembur
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Absensi extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'absensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'status_kehadiran',
        'jam_masuk',
        'jam_keluar',
        'jam_lembur',
        'keterangan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_lembur' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Absensi milik satu Karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
