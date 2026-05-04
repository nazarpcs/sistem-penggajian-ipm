<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model KomponenSlipGaji — rincian komponen tunjangan dan potongan per slip gaji.
 *
 * Tipe: 'tunjangan' atau 'potongan'.
 * Hanya memiliki created_at (tanpa updated_at).
 *
 * @property int $id
 * @property int $slip_gaji_id
 * @property string $tipe
 * @property string $nama_komponen
 * @property float $nilai
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class KomponenSlipGaji extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'komponen_slip_gaji';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slip_gaji_id',
        'tipe',
        'nama_komponen',
        'nilai',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi: KomponenSlipGaji milik satu SlipGaji.
     */
    public function slipGaji(): BelongsTo
    {
        return $this->belongsTo(SlipGaji::class, 'slip_gaji_id');
    }
}
