import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Manajemen Karyawan (Admin)
 * @see Req 3.1, 3.5, 3.6
 */
export class KaryawanPage {
  readonly page: Page;
  readonly addButton: Locator;
  readonly searchInput: Locator;
  readonly filterPtKlien: Locator;
  readonly filterStatus: Locator;
  readonly dataTable: Locator;

  // Form fields
  readonly namaLengkapInput: Locator;
  readonly nikInput: Locator;
  readonly emailInput: Locator;
  readonly teleponInput: Locator;
  readonly alamatInput: Locator;
  readonly ptKlienSelect: Locator;
  readonly jabatanInput: Locator;
  readonly gajiPokokInput: Locator;
  readonly tanggalBergabungInput: Locator;
  readonly statusSelect: Locator;
  readonly submitButton: Locator;
  readonly cancelButton: Locator;

  constructor(page: Page) {
    this.page = page;
    this.addButton = page.locator('a:has-text("Tambah Karyawan")');
    this.searchInput = page.locator('input[name="search"]');
    this.filterPtKlien = page.locator('select[name="pt_klien_id"]');
    this.filterStatus = page.locator('select[name="status_aktif"]');
    this.dataTable = page.locator('.bg-white table').first();

    // Form
    this.namaLengkapInput = page.locator('#nama_lengkap');
    this.nikInput = page.locator('#nik');
    this.emailInput = page.locator('#email');
    this.teleponInput = page.locator('#telepon');
    this.alamatInput = page.locator('#alamat');
    this.ptKlienSelect = page.locator('#pt_klien_id');
    this.jabatanInput = page.locator('#jabatan');
    this.gajiPokokInput = page.locator('#gaji_pokok');
    this.tanggalBergabungInput = page.locator('#tanggal_bergabung');
    this.statusSelect = page.locator('#status_aktif');
    this.submitButton = page.locator('button[type="submit"]:has-text("Simpan")');
    this.cancelButton = page.locator('a:has-text("Batal")');
  }

  async gotoIndex(): Promise<void> {
    await this.page.goto('/admin/karyawan');
    await waitForPageLoad(this.page);
  }

  async gotoCreate(): Promise<void> {
    await this.page.goto('/admin/karyawan/create');
    await waitForPageLoad(this.page);
  }

  async fillForm(data: {
    nama_lengkap?: string;
    nik?: string;
    email?: string;
    telepon?: string;
    alamat?: string;
    jabatan?: string;
    gaji_pokok?: string;
    tanggal_bergabung?: string;
  }): Promise<void> {
    if (data.nama_lengkap) await this.namaLengkapInput.fill(data.nama_lengkap);
    if (data.nik) await this.nikInput.fill(data.nik);
    if (data.email) await this.emailInput.fill(data.email);
    if (data.telepon) await this.teleponInput.fill(data.telepon);
    if (data.alamat) await this.alamatInput.fill(data.alamat);
    if (data.jabatan) await this.jabatanInput.fill(data.jabatan);
    if (data.gaji_pokok) await this.gajiPokokInput.fill(data.gaji_pokok);
    if (data.tanggal_bergabung) await this.tanggalBergabungInput.fill(data.tanggal_bergabung);
  }

  async selectPtKlien(index: number = 1): Promise<void> {
    const options = this.ptKlienSelect.locator('option');
    const count = await options.count();
    if (count > 1) {
      const value = await options.nth(index).getAttribute('value');
      if (value) await this.ptKlienSelect.selectOption(value);
    }
  }

  async submitForm(): Promise<void> {
    await this.submitButton.click();
    await waitForPageLoad(this.page);
  }

  async expectOnIndex(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/karyawan$/);
  }

  async expectValidationError(): Promise<void> {
    await expect(this.page.locator('.text-red-500').first()).toBeVisible();
  }

  async getRowCount(): Promise<number> {
    return this.dataTable.locator('tbody tr').count();
  }
}
