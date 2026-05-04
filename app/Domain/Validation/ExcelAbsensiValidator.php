<?php

declare(strict_types=1);

namespace App\Domain\Validation;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Validator khusus untuk format file Excel absensi.
 *
 * Memvalidasi format file, header kolom, dan mem-parse data ke array
 * untuk diteruskan ke AbsensiValidator::validasiBulk().
 *
 * @see Property 11: Validasi Import Excel — Atomicity
 * @see Property 20: Validasi Sinkron Sebelum Import Async
 */
class ExcelAbsensiValidator
{
    /** @var list<string> Kolom wajib dalam file Excel */
    private const REQUIRED_COLUMNS = [
        'karyawan_id',
        'tanggal',
        'status_kehadiran',
    ];

    /** @var list<string> Kolom opsional yang dikenali */
    private const OPTIONAL_COLUMNS = [
        'jam_masuk',
        'jam_keluar',
        'keterangan',
    ];

    /** @var list<string> Ekstensi file yang diizinkan */
    private const ALLOWED_EXTENSIONS = ['xlsx', 'xls'];

    /**
     * Validasi file Excel dan parse data ke array.
     *
     * @param UploadedFile $file File Excel yang diupload
     * @return array{valid: bool, data: array<int, array<string, mixed>>, errors: array<string, string>}
     */
    public function validasiDanParse(UploadedFile $file): array
    {
        $errors = [];

        // Validasi ekstensi file
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $errors['file'] = 'Format file harus .xlsx atau .xls.';

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        // Validasi ukuran file (tidak kosong)
        if ($file->getSize() === 0) {
            $errors['file'] = 'File tidak boleh kosong.';

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        // Parse file Excel ke array
        try {
            $rawData = Excel::toArray(new class {}, $file);
        } catch (\Throwable $e) {
            $errors['file'] = 'Gagal membaca file Excel: ' . $e->getMessage();

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        // Ambil sheet pertama
        if (empty($rawData) || empty($rawData[0])) {
            $errors['file'] = 'File Excel tidak memiliki data.';

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        $sheet = $rawData[0];

        // Baris pertama adalah header
        if (count($sheet) < 2) {
            $errors['file'] = 'File Excel harus memiliki minimal 1 baris data selain header.';

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        $header = array_map(function ($col) {
            return strtolower(trim((string) $col));
        }, $sheet[0]);

        // Validasi kolom wajib
        $missingColumns = [];
        foreach (self::REQUIRED_COLUMNS as $requiredCol) {
            if (!in_array($requiredCol, $header, true)) {
                $missingColumns[] = $requiredCol;
            }
        }

        if (!empty($missingColumns)) {
            $errors['header'] = 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns);

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        // Parse data rows (skip header)
        $data = [];
        $allColumns = array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS);

        for ($i = 1; $i < count($sheet); $i++) {
            $row = $sheet[$i];

            // Skip baris kosong
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $parsedRow = [];
            foreach ($header as $colIndex => $colName) {
                if (in_array($colName, $allColumns, true)) {
                    $value = $row[$colIndex] ?? null;
                    $parsedRow[$colName] = $value !== null ? trim((string) $value) : '';
                }
            }

            // Konversi karyawan_id ke integer jika numerik
            if (isset($parsedRow['karyawan_id']) && is_numeric($parsedRow['karyawan_id'])) {
                $parsedRow['karyawan_id'] = (int) $parsedRow['karyawan_id'];
            }

            $data[] = $parsedRow;
        }

        if (empty($data)) {
            $errors['file'] = 'File Excel tidak memiliki baris data yang valid.';

            return ['valid' => false, 'data' => [], 'errors' => $errors];
        }

        return ['valid' => true, 'data' => $data, 'errors' => []];
    }

    /**
     * Cek apakah baris kosong (semua kolom null atau string kosong).
     *
     * @param array<int, mixed> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
