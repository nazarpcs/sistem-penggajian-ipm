<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request validasi input absensi manual.
 *
 * Validasi mencakup: karyawan_id, tanggal, status_kehadiran,
 * jam_masuk/jam_keluar (wajib jika Hadir), dan keterangan.
 *
 * @see Req 5.1 (form input absensi manual)
 * @see Property 12: Uniqueness Absensi per Karyawan per Tanggal
 */
class AbsensiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'karyawan_id' => ['required', 'integer', 'exists:karyawan,id'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'status_kehadiran' => ['required', 'in:Hadir,Izin,Sakit,Alpha'],
            'jam_masuk' => ['nullable', 'date_format:H:i', 'required_if:status_kehadiran,Hadir'],
            'jam_keluar' => ['nullable', 'date_format:H:i', 'after:jam_masuk', 'required_if:status_kehadiran,Hadir'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'karyawan_id.required' => 'Karyawan wajib dipilih.',
            'karyawan_id.exists' => 'Karyawan tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Format tanggal harus Y-m-d.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'status_kehadiran.required' => 'Status kehadiran wajib dipilih.',
            'status_kehadiran.in' => 'Status kehadiran harus salah satu: Hadir, Izin, Sakit, Alpha.',
            'jam_masuk.required_if' => 'Jam masuk wajib diisi jika status Hadir.',
            'jam_masuk.date_format' => 'Format jam masuk harus H:i.',
            'jam_keluar.required_if' => 'Jam keluar wajib diisi jika status Hadir.',
            'jam_keluar.date_format' => 'Format jam keluar harus H:i.',
            'jam_keluar.after' => 'Jam keluar harus lebih besar dari jam masuk.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
