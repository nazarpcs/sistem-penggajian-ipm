<?php

declare(strict_types=1);

namespace App\Domain\Validation;

/**
 * Implementasi AbsensiValidator — domain class murni tanpa dependensi Laravel.
 *
 * Memvalidasi data absensi dari input manual maupun file Excel.
 * Menggunakan callback untuk akses database (cek duplikasi) agar tetap pure PHP.
 *
 * @see AbsensiValidatorInterface
 * @see Property 11: Validasi Import Excel — Atomicity
 * @see Property 12: Uniqueness Absensi per Karyawan per Tanggal
 */
class AbsensiValidator implements AbsensiValidatorInterface
{
    /** @var list<string> Status kehadiran yang valid */
    private const VALID_STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpha'];

    /**
     * Callback untuk mengecek duplikasi di database.
     *
     * @var callable(int, string): bool|null
     */
    private $duplikasiChecker;

    /**
     * @param callable(int, string): bool|null $duplikasiChecker Callback cek duplikasi DB
     */
    public function __construct(?callable $duplikasiChecker = null)
    {
        $this->duplikasiChecker = $duplikasiChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function validasiSatuBaris(array $baris): array
    {
        $errors = [];

        // Validasi karyawan_id
        if (!isset($baris['karyawan_id']) || $baris['karyawan_id'] === '') {
            $errors['karyawan_id'] = 'karyawan_id wajib diisi.';
        } elseif (!is_numeric($baris['karyawan_id']) || (int) $baris['karyawan_id'] != $baris['karyawan_id'] || (int) $baris['karyawan_id'] <= 0) {
            $errors['karyawan_id'] = 'karyawan_id harus berupa integer positif.';
        }

        // Validasi tanggal
        if (!isset($baris['tanggal']) || $baris['tanggal'] === '') {
            $errors['tanggal'] = 'tanggal wajib diisi.';
        } elseif (!$this->isValidDate($baris['tanggal'])) {
            $errors['tanggal'] = 'tanggal harus format Y-m-d yang valid.';
        } elseif ($this->isFutureDate($baris['tanggal'])) {
            $errors['tanggal'] = 'tanggal tidak boleh di masa depan.';
        }

        // Validasi status_kehadiran
        if (!isset($baris['status_kehadiran']) || $baris['status_kehadiran'] === '') {
            $errors['status_kehadiran'] = 'status_kehadiran wajib diisi.';
        } elseif (!in_array($baris['status_kehadiran'], self::VALID_STATUS, true)) {
            $errors['status_kehadiran'] = 'status_kehadiran harus salah satu: Hadir, Izin, Sakit, Alpha.';
        }

        // Validasi jam_masuk dan jam_keluar jika status Hadir
        $statusKehadiran = $baris['status_kehadiran'] ?? null;
        if ($statusKehadiran === 'Hadir') {
            if (!isset($baris['jam_masuk']) || $baris['jam_masuk'] === '') {
                $errors['jam_masuk'] = 'jam_masuk wajib diisi jika status Hadir.';
            } elseif (!$this->isValidTime($baris['jam_masuk'])) {
                $errors['jam_masuk'] = 'jam_masuk harus format H:i yang valid.';
            }

            if (!isset($baris['jam_keluar']) || $baris['jam_keluar'] === '') {
                $errors['jam_keluar'] = 'jam_keluar wajib diisi jika status Hadir.';
            } elseif (!$this->isValidTime($baris['jam_keluar'])) {
                $errors['jam_keluar'] = 'jam_keluar harus format H:i yang valid.';
            }

            // Validasi jam_keluar > jam_masuk
            if (
                isset($baris['jam_masuk'], $baris['jam_keluar'])
                && $this->isValidTime((string) $baris['jam_masuk'])
                && $this->isValidTime((string) $baris['jam_keluar'])
                && !$this->isTimeAfter((string) $baris['jam_keluar'], (string) $baris['jam_masuk'])
            ) {
                $errors['jam_keluar'] = 'jam_keluar harus lebih besar dari jam_masuk.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Atomicity: jika ada 1 baris error, seluruh data ditolak.
     */
    public function validasiBulk(array $rows): array
    {
        $totalBaris = count($rows);
        $barisError = 0;
        $allErrors = [];
        $seenCombinations = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $rowErrors = [];

            // Validasi per baris
            $result = $this->validasiSatuBaris($row);
            if (!$result['valid']) {
                $rowErrors = $result['errors'];
            }

            // Cek duplikasi internal (antar baris dalam file)
            $karyawanId = $row['karyawan_id'] ?? null;
            $tanggal = $row['tanggal'] ?? null;

            if ($karyawanId !== null && $tanggal !== null && is_numeric($karyawanId) && $this->isValidDate((string) $tanggal)) {
                $key = (int) $karyawanId . '|' . $tanggal;
                if (isset($seenCombinations[$key])) {
                    $rowErrors['duplikasi_internal'] = "Duplikasi dengan baris {$seenCombinations[$key]} (karyawan_id={$karyawanId}, tanggal={$tanggal}).";
                } else {
                    $seenCombinations[$key] = $rowNumber;
                }

                // Cek duplikasi di database via callback
                if (empty($rowErrors) && $this->duplikasiChecker !== null) {
                    if ($this->cekDuplikasi((int) $karyawanId, (string) $tanggal)) {
                        $rowErrors['duplikasi_database'] = "Data absensi untuk karyawan_id={$karyawanId} pada tanggal={$tanggal} sudah ada di database.";
                    }
                }
            }

            if (!empty($rowErrors)) {
                $barisError++;
                $allErrors[] = [
                    'baris' => $rowNumber,
                    'errors' => $rowErrors,
                ];
            }
        }

        $barisValid = $totalBaris - $barisError;

        return [
            'valid' => $barisError === 0,
            'total_baris' => $totalBaris,
            'baris_valid' => $barisValid,
            'baris_error' => $barisError,
            'errors' => $allErrors,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function cekDuplikasi(int $karyawanId, string $tanggal): bool
    {
        if ($this->duplikasiChecker === null) {
            return false;
        }

        return ($this->duplikasiChecker)($karyawanId, $tanggal);
    }

    /**
     * Validasi format tanggal Y-m-d.
     */
    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parts = explode('-', $date);

        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }

    /**
     * Cek apakah tanggal di masa depan.
     */
    private function isFutureDate(string $date): bool
    {
        $today = date('Y-m-d');

        return $date > $today;
    }

    /**
     * Validasi format waktu H:i (00:00 - 23:59).
     */
    private function isValidTime(string $time): bool
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return false;
        }

        $parts = explode(':', $time);
        $hour = (int) $parts[0];
        $minute = (int) $parts[1];

        return $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59;
    }

    /**
     * Cek apakah time1 > time2 (format H:i).
     */
    private function isTimeAfter(string $time1, string $time2): bool
    {
        return $time1 > $time2;
    }
}
