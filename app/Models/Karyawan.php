<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Karyawan — data lengkap karyawan outsourcing.
 *
 * Berelasi ke User (akun login), PtKlien (penempatan),
 * Absensi (kehadiran), dan SlipGaji (penggajian).
 *
 * @property int $id
 * @property int $user_id
 * @property int $pt_klien_id
 * @property string $nik
 * @property string $nama_lengkap
 * @property \Illuminate\Support\Carbon $tanggal_lahir
 * @property string $alamat
 * @property string $telepon
 * @property string $jabatan
 * @property float $gaji_pokok
 * @property \Illuminate\Support\Carbon $tanggal_bergabung
 * @property bool $status_aktif
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Karyawan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'karyawan';

    /**
     * Email sementara untuk pembuatan akun User otomatis via Observer.
     * Tidak disimpan ke database — hanya digunakan saat proses create.
     */
    private ?string $temporaryEmail = null;

    /**
     * Password sementara untuk dikirim via notifikasi.
     * Tidak disimpan ke database — hanya digunakan saat proses create.
     */
    private ?string $temporaryPassword = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'pt_klien_id',
        'nik',
        'nama_lengkap',
        'tanggal_lahir',
        'alamat',
        'telepon',
        'jabatan',
        'gaji_pokok',
        'tanggal_bergabung',
        'status_aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
            'status_aktif' => 'boolean',
            'gaji_pokok' => 'decimal:2',
        ];
    }

    /**
     * Relasi: Karyawan milik satu User (akun login).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Karyawan milik satu PtKlien (penempatan).
     */
    public function ptKlien(): BelongsTo
    {
        return $this->belongsTo(PtKlien::class, 'pt_klien_id');
    }

    /**
     * Relasi: Karyawan memiliki banyak Absensi.
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Relasi: Karyawan memiliki banyak SlipGaji.
     */
    public function slipGaji(): HasMany
    {
        return $this->hasMany(SlipGaji::class, 'karyawan_id');
    }

    /**
     * Set email sementara untuk pembuatan akun User otomatis.
     */
    public function setTemporaryEmail(string $email): self
    {
        $this->temporaryEmail = $email;

        return $this;
    }

    /**
     * Ambil email sementara.
     */
    public function getTemporaryEmail(): ?string
    {
        return $this->temporaryEmail;
    }

    /**
     * Set password sementara untuk notifikasi kredensial.
     */
    public function setTemporaryPassword(string $password): self
    {
        $this->temporaryPassword = $password;

        return $this;
    }

    /**
     * Ambil password sementara.
     */
    public function getTemporaryPassword(): ?string
    {
        return $this->temporaryPassword;
    }

    /**
     * Hapus password sementara setelah digunakan.
     */
    public function clearTemporaryPassword(): void
    {
        $this->temporaryPassword = null;
    }
}
