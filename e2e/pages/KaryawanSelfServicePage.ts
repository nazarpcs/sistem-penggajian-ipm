import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Self-Service Karyawan (Profil, Absensi, Slip Gaji)
 * @see Req 8.1, 8.3, 8.4, 8.5
 */
export class KaryawanSelfServicePage {
  readonly page: Page;
  readonly profilSection: Locator;
  readonly absensiTable: Locator;
  readonly slipGajiTable: Locator;
  readonly downloadPdfLink: Locator;

  constructor(page: Page) {
    this.page = page;
    this.profilSection = page.locator('text=Profil');
    this.absensiTable = page.locator('table');
    this.slipGajiTable = page.locator('table');
    this.downloadPdfLink = page.locator('a:has-text("PDF")');
  }

  async gotoProfil(): Promise<void> {
    await this.page.goto('/karyawan/profil');
    await waitForPageLoad(this.page);
  }

  async gotoAbsensi(): Promise<void> {
    await this.page.goto('/karyawan/absensi');
    await waitForPageLoad(this.page);
  }

  async gotoSlipGaji(): Promise<void> {
    await this.page.goto('/karyawan/slip-gaji');
    await waitForPageLoad(this.page);
  }

  async expectProfilVisible(): Promise<void> {
    await expect(this.page).toHaveURL(/\/karyawan\/profil/);
  }

  async expectAbsensiVisible(): Promise<void> {
    await expect(this.page).toHaveURL(/\/karyawan\/absensi/);
  }

  async expectSlipGajiVisible(): Promise<void> {
    await expect(this.page).toHaveURL(/\/karyawan\/slip-gaji/);
  }
}
