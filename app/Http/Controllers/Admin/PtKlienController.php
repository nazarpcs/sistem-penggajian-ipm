<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PtKlienRequest;
use App\Services\PtKlienService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller CRUD PT Klien untuk Admin.
 *
 * Mendelegasikan logika bisnis ke PtKlienService.
 * Menggunakan PtKlienRequest untuk validasi input.
 *
 * @see Req 4.1, 4.2, 4.3
 */
class PtKlienController extends Controller
{
    public function __construct(
        private readonly PtKlienService $ptKlienService,
    ) {}

    /**
     * Tampilkan daftar PT Klien dengan filter.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['nama', 'status_kontrak']);
        $ptKliens = $this->ptKlienService->index($filters);

        return view('admin.pt-klien.index', compact('ptKliens', 'filters'));
    }

    /**
     * Tampilkan form tambah PT Klien baru.
     */
    public function create(): View
    {
        return view('admin.pt-klien.create');
    }

    /**
     * Simpan PT Klien baru.
     */
    public function store(PtKlienRequest $request): RedirectResponse
    {
        $this->ptKlienService->store($request->validated());

        return redirect()
            ->route('admin.pt-klien.index')
            ->with('success', 'PT Klien berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail PT Klien beserta relasi karyawan dan konfigurasi gaji.
     */
    public function show(int $ptKlien): View
    {
        $data = $this->ptKlienService->show($ptKlien);

        return view('admin.pt-klien.show', ['ptKlien' => $data]);
    }

    /**
     * Tampilkan form edit PT Klien.
     */
    public function edit(int $ptKlien): View
    {
        $data = $this->ptKlienService->show($ptKlien);

        return view('admin.pt-klien.edit', ['ptKlien' => $data]);
    }

    /**
     * Update data PT Klien.
     */
    public function update(PtKlienRequest $request, int $ptKlien): RedirectResponse
    {
        $this->ptKlienService->update($ptKlien, $request->validated());

        return redirect()
            ->route('admin.pt-klien.index')
            ->with('success', 'Data PT Klien berhasil diperbarui.');
    }

    /**
     * Tampilkan daftar karyawan yang terdaftar di bawah PT Klien.
     *
     * @see Req 4.3
     */
    public function karyawan(int $ptKlien): View
    {
        $data = $this->ptKlienService->show($ptKlien);
        $karyawans = $this->ptKlienService->karyawanPerKlien($ptKlien);

        return view('admin.pt-klien.karyawan', [
            'ptKlien' => $data,
            'karyawans' => $karyawans,
        ]);
    }
}
