<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi pembuatan invoice.
 *
 * @see Req 9.1 (buat invoice: PT Klien + Periode)
 */
class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pt_klien_id' => ['required', 'integer', 'exists:pt_klien,id'],
            'periode_id' => ['required', 'integer', 'exists:periode_penggajian,id'],
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
            'pt_klien_id.required' => 'PT Klien wajib dipilih.',
            'pt_klien_id.exists' => 'PT Klien tidak ditemukan.',
            'periode_id.required' => 'Periode penggajian wajib dipilih.',
            'periode_id.exists' => 'Periode penggajian tidak ditemukan.',
        ];
    }
}
