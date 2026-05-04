<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User — akun login untuk semua pengguna sistem.
 *
 * Mendukung tiga peran: admin, pemilik_pt, karyawan.
 * Dilengkapi fitur account lockout dan session tracking.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $locked_until
 * @property int $login_attempts
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'locked_until',
        'login_attempts',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
            'last_login' => 'datetime',
            'login_attempts' => 'integer',
        ];
    }

    /**
     * Relasi: User memiliki satu data Karyawan.
     */
    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class);
    }

    /**
     * Cek apakah user memiliki peran Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user memiliki peran Pemilik PT.
     */
    public function isPemilikPt(): bool
    {
        return $this->role === 'pemilik_pt';
    }

    /**
     * Cek apakah user memiliki peran Karyawan.
     */
    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    /**
     * Cek apakah akun sedang terkunci.
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}
