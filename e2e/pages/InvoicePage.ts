import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Invoice (Admin & Owner)
 * @see Req 9.1-9.9
 */
export class InvoicePage {
  readonly page: Page;
  readonly buatInvoiceButton: Locator;
  readonly dataTable: Locator;
  readonly filterPtKlien: Locator;
  readonly filterPeriode: Locator;
  readonly filterStatus: Locator;

  // Modal buat invoice
  readonly modalPtKlienSelect: Locator;
  readonly modalPeriodeSelect: Locator;
  readonly modalSubmit: Locator;
  readonly modalCancel: Locator;

  // Owner approval
  readonly approveButton: Locator;
  readonly rejectButton: Locator;
  readonly rejectReasonInput: Locator;
  readonly rejectSubmit: Locator;

  constructor(page: Page) {
    this.page = page;
    this.buatInvoiceButton = page.locator('button[type="button"]:has-text("Buat Invoice")');
    this.dataTable = page.locator('table');
    this.filterPtKlien = page.locator('select[name="pt_klien_id"]');
    this.filterPeriode = page.locator('select[name="periode_id"]');
    this.filterStatus = page.locator('select[name="status"]');

    this.modalPtKlienSelect = page.locator('#inv_pt_klien_id');
    this.modalPeriodeSelect = page.locator('#inv_periode_id');
    this.modalSubmit = page.locator('button:has-text("Buat Invoice")').last();
    this.modalCancel = page.locator('button:has-text("Batal")');

    this.approveButton = page.locator('button:has-text("Setujui")');
    this.rejectButton = page.locator('button:has-text("Tolak")');
    this.rejectReasonInput = page.locator('#alasan_penolakan');
    this.rejectSubmit = page.locator('button:has-text("Tolak Invoice")');
  }

  async gotoAdminIndex(): Promise<void> {
    await this.page.goto('/admin/invoice');
    await waitForPageLoad(this.page);
  }

  async gotoOwnerIndex(): Promise<void> {
    await this.page.goto('/owner/invoice');
    await waitForPageLoad(this.page);
  }

  async openCreateModal(): Promise<void> {
    await this.buatInvoiceButton.click();
    await expect(this.modalPtKlienSelect).toBeVisible();
  }

  async approveInvoice(): Promise<void> {
    await this.approveButton.click();
    await waitForPageLoad(this.page);
  }

  async rejectInvoice(reason: string): Promise<void> {
    await this.rejectButton.click();
    await expect(this.rejectReasonInput).toBeVisible();
    await this.rejectReasonInput.fill(reason);
    await this.rejectSubmit.click();
    await waitForPageLoad(this.page);
  }
}
