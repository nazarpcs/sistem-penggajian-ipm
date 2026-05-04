<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request validasi input PT Klien.
 *
 * Menangani validasi untuk create dan update:
 * - nomor_kontrak unique pada create, ignore current record pada update
 * - tgl_berakhir harus setelah tgl_mulai
 *
 * @see Req 4.1
 */
class PtKlienRequest extends FormRequest
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
        $ptKlienId = $this->route('pt_klien');

        return [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'telepon' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'nama_pic' => ['required', 'string', 'max:255'],
            'nomor_kontrak' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pt_klien', 'nomor_kontrak')->ignore($ptKlienId),
            ],
            'tgl_mulai' => ['required', 'date'],
            'tgl_berakhir' => ['required', 'date', 'after:tgl_mulai'],
            'fee_jasa' => ['required', 'numeric', 'min:0'],
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
            'nama.required' => 'Nama perusahaan wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'nama_pic.required' => 'Nama PIC wajib diisi.',
            'nomor_kontrak.required' => 'Nomor kontrak wajib diisi.',
            'nomor_kontrak.unique' => 'Nomor kontrak sudah digunakan.',
            'tgl_mulai.required' => 'Tanggal mulai kontrak wajib diisi.',
            'tgl_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tgl_berakhir.required' => 'Tanggal berakhir kontrak wajib diisi.',
            'tgl_berakhir.date' => 'Format tanggal berakhir tidak valid.',
            'tgl_berakhir.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'fee_jasa.required' => 'Fee jasa wajib diisi.',
            'fee_jasa.numeric' => 'Fee jasa harus berupa angka.',
            'fee_jasa.min' => 'Fee jasa tidak boleh negatif.',
        ];
    }
}
