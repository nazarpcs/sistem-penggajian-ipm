import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Dashboard Admin
 * @see Req 10.1
 */
export class AdminDashboardPage {
  readonly page: Page;
  readonly totalKaryawanCard: Locator;
  readonly totalPtKlienCard: Locator;
  readonly penggajianCard: Locator;
  readonly invoicePendingCard: Locator;
  readonly kontrakWarning: Locator;
  readonly invoiceTable: Locator;
  readonly sidebarNav: Locator;

  constructor(page: Page) {
    this.page = page;
    this.totalKaryawanCard = page.locator('text=Total Karyawan Aktif').locator('..');
    this.totalPtKlienCard = page.locator('text=Total PT Klien Aktif').locator('..');
    this.penggajianCard = page.locator('text=Penggajian Bulan Ini').locator('..');
    this.invoicePendingCard = page.locator('text=Invoice Pending').locator('..');
    this.kontrakWarning = page.locator('text=Kontrak Akan Berakhir');
    this.invoiceTable = page.locator('text=Invoice Menunggu Approval').locator('..').locator('table');
    this.sidebarNav = page.locator('nav');
  }

  async goto(): Promise<void> {
    await this.page.goto('/admin/dashboard');
    await waitForPageLoad(this.page);
  }

  async expectDashboardVisible(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/dashboard/);
    await expect(this.totalKaryawanCard).toBeVisible();
    await expect(this.totalPtKlienCard).toBeVisible();
  }

  async navigateTo(menuText: string): Promise<void> {
    await this.page.locator(`a:has-text("${menuText}")`).first().click();
    await waitForPageLoad(this.page);
  }
}
