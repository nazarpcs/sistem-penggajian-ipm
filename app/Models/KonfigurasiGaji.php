<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model KonfigurasiGaji — aturan perhitungan gaji per PT Klien.
 *
 * Menyimpan gaji pokok default, jam kerja normal, tarif lembur,
 * potongan per hari, dan komponen tunjangan (JSON).
 *
 * @property int $id
 * @property int $pt_klien_id
 * @property float $gaji_pokok_default
 * @property float $jam_kerja_normal
 * @property float $tarif_lembur_per_jam
 * @property float $potongan_per_hari
 * @property array<string, mixed> $komponen_tunjangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class KonfigurasiGaji extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'konfigurasi_gaji';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pt_klien_id',
        'gaji_pokok_default',
        'jam_kerja_normal',
        'tarif_lembur_per_jam',
        'potongan_per_hari',
        'komponen_tunjangan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'komponen_tunjangan' => 'array',
            'gaji_pokok_default' => 'decimal:2',
            'jam_kerja_normal' => 'decimal:2',
            'tarif_lembur_per_jam' => 'decimal:2',
            'potongan_per_hari' => 'decimal:2',
        ];
    }

    /**
     * Relasi: KonfigurasiGaji milik satu PtKlien.
     */
    public function ptKlien(): BelongsTo
    {
        return $this->belongsTo(PtKlien::class, 'pt_klien_id');
    }
}
