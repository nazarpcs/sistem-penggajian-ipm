<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PeriodePenggajian — rentang waktu satu bulan untuk perhitungan gaji.
 *
 * Status 'terkunci' mencegah perubahan data absensi pada periode tersebut.
 *
 * @property int $id
 * @property int $bulan
 * @property int $tahun
 * @property \Illuminate\Support\Carbon $tanggal_mulai
 * @property \Illuminate\Support\Carbon $tanggal_selesai
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PeriodePenggajian extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'periode_penggajian';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'bulan',
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
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
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'bulan' => 'integer',
            'tahun' => 'integer',
        ];
    }

    /**
     * Relasi: PeriodePenggajian memiliki banyak SlipGaji.
     */
    public function slipGaji(): HasMany
    {
        return $this->hasMany(SlipGaji::class, 'periode_id');
    }

    /**
     * Relasi: PeriodePenggajian memiliki banyak Invoice.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'periode_id');
    }
}
