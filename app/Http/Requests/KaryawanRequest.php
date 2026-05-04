<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request validasi input karyawan.
 *
 * Validasi field: nama_lengkap, nik (16 digit, unique), email (unique),
 * tanggal_lahir, alamat, telepon, jabatan, gaji_pokok, pt_klien_id, tanggal_bergabung.
 *
 * Pada mode update, unique constraint mengecualikan record yang sedang diedit.
 *
 * @see Req 3.1
 */
class KaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi via middleware CheckRole
    }

    public function rules(): array
    {
        $karyawanId = $this->route('karyawan');

        $nikUniqueRule = Rule::unique('karyawan', 'nik');
        $emailUniqueRule = Rule::unique('users', 'email');

        if ($karyawanId) {
            $nikUniqueRule = $nikUniqueRule->ignore($karyawanId);
            // Untuk email, ignore user_id dari karyawan yang sedang diedit
            $karyawan = \App\Models\Karyawan::find($karyawanId);
            if ($karyawan) {
                $emailUniqueRule = $emailUniqueRule->ignore($karyawan->user_id);
            }
        }

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/', $nikUniqueRule],
            'email' => ['required', 'email', 'max:255', $emailUniqueRule],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:20', 'regex:/^[0-9\+\-\(\)\s]+$/'],
            'jabatan' => ['required', 'string', 'max:255'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'pt_klien_id' => ['required', 'integer', 'exists:pt_klien,id'],
            'tanggal_bergabung' => ['required', 'date'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka (16 digit).',
            'nik.unique' => 'NIK sudah terdaftar di sistem.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'telepon.regex' => 'Format nomor telepon tidak valid.',
            'gaji_pokok.min' => 'Gaji pokok tidak boleh bernilai negatif.',
            'pt_klien_id.exists' => 'PT Klien yang dipilih tidak ditemukan.',
        ];
    }
}
