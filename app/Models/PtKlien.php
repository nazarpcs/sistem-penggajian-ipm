<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model PtKlien — data perusahaan klien pengguna jasa outsourcing PT IPM.
 *
 * Menyimpan informasi kontrak, fee jasa, dan berelasi ke
 * Karyawan, KonfigurasiGaji, dan Invoice.
 *
 * @property int $id
 * @property string $nama
 * @property string $alamat
 * @property string $telepon
 * @property string $email
 * @property string $nama_pic
 * @property string $nomor_kontrak
 * @property \Illuminate\Support\Carbon $tgl_mulai
 * @property \Illuminate\Support\Carbon $tgl_berakhir
 * @property float $fee_jasa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PtKlien extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pt_klien';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'alamat',
        'telepon',
        'email',
        'nama_pic',
        'nomor_kontrak',
        'tgl_mulai',
        'tgl_berakhir',
        'fee_jasa',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_berakhir' => 'date',
            'fee_jasa' => 'decimal:2',
        ];
    }

    /**
     * Relasi: PtKlien memiliki banyak Karyawan.
     */
    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class, 'pt_klien_id');
    }

    /**
     * Relasi: PtKlien memiliki satu KonfigurasiGaji.
     */
    public function konfigurasiGaji(): HasOne
    {
        return $this->hasOne(KonfigurasiGaji::class, 'pt_klien_id');
    }

    /**
     * Relasi: PtKlien memiliki banyak Invoice.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'pt_klien_id');
    }
}
