<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model AuditLog — catatan aktivitas kritis di sistem.
 *
 * user_id nullable untuk menangkap percobaan akses tanpa autentikasi.
 * Hanya memiliki created_at (tanpa updated_at).
 * Data disimpan minimal 1 tahun sesuai requirements.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $role_pengguna
 * @property string $jenis_aktivitas
 * @property string|null $model_tipe
 * @property int|null $model_id
 * @property array<string, mixed>|null $data_lama
 * @property array<string, mixed>|null $data_baru
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class AuditLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'audit_logs';

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
        'user_id',
        'role_pengguna',
        'jenis_aktivitas',
        'model_tipe',
        'model_id',
        'data_lama',
        'data_baru',
        'ip_address',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relasi: AuditLog milik satu User (nullable).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
