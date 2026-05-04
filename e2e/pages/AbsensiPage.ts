import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Absensi (Admin)
 * @see Req 5.1, 5.2, 5.6, 6.1
 */
export class AbsensiPage {
  readonly page: Page;
  readonly inputManualButton: Locator;
  readonly importExcelButton: Locator;
  readonly rekapButton: Locator;
  readonly filterButton: Locator;
  readonly dataTable: Locator;

  // Manual form
  readonly formKaryawanSelect: Locator;
  readonly formTanggal: Locator;
  readonly formStatus: Locator;
  readonly formJamMasuk: Locator;
  readonly formJamKeluar: Locator;
  readonly formKeterangan: Locator;
  readonly formSubmit: Locator;

  constructor(page: Page) {
    this.page = page;
    this.inputManualButton = page.locator('button:has-text("Input Manual")');
    this.importExcelButton = page.locator('text=Import Excel');
    this.rekapButton = page.locator('a:has-text("Rekap")');
    this.filterButton = page.locator('button:has-text("Filter")');
    this.dataTable = page.locator('table');

    this.formKaryawanSelect = page.locator('#form_karyawan_id');
    this.formTanggal = page.locator('#form_tanggal');
    this.formStatus = page.locator('#form_status');
    this.formJamMasuk = page.locator('#jam_masuk');
    this.formJamKeluar = page.locator('#jam_keluar');
    this.formKeterangan = page.locator('#keterangan');
    this.formSubmit = page.locator('form button[type="submit"]:has-text("Simpan")');
  }

  async goto(): Promise<void> {
    await this.page.goto('/admin/absensi');
    await waitForPageLoad(this.page);
  }

  async openManualForm(): Promise<void> {
    await this.inputManualButton.click();
    await expect(this.formKaryawanSelect).toBeVisible();
  }

  async fillManualAbsensi(data: {
    tanggal: string;
    status_kehadiran: string;
    jam_masuk?: string;
    jam_keluar?: string;
    keterangan?: string;
  }): Promise<void> {
    // Select first karyawan
    const options = this.formKaryawanSelect.locator('option');
    const count = await options.count();
    if (count > 1) {
      const value = await options.nth(1).getAttribute('value');
      if (value) await this.formKaryawanSelect.selectOption(value);
    }
    await this.formTanggal.fill(data.tanggal);
    await this.formStatus.selectOption(data.status_kehadiran);
    if (data.jam_masuk) await this.formJamMasuk.fill(data.jam_masuk);
    if (data.jam_keluar) await this.formJamKeluar.fill(data.jam_keluar);
    if (data.keterangan) await this.formKeterangan.fill(data.keterangan);
  }

  async submitManualForm(): Promise<void> {
    await this.formSubmit.click();
    await waitForPageLoad(this.page);
  }
}
