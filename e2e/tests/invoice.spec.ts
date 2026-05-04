import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { InvoicePage } from '../pages/InvoicePage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_INVOICE — Test Suite Invoice
 * @see Req 9.1-9.10, Property 15, 16, 19
 */
test.describe('TC_INVOICE: Pembuatan dan Pengelolaan Invoice', () => {

  test.describe('Admin — Invoice Management', () => {
    let invoicePage: InvoicePage;

    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      await expect(page).toHaveURL(/\/admin\/dashboard/);
      invoicePage = new InvoicePage(page);
    });

    test('TC_INVOICE_001: Halaman invoice admin dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke halaman invoice', async () => {
        await invoicePage.gotoAdminIndex();
      });
      await test.step('Verifikasi halaman invoice', async () => {
        await expect(page).toHaveURL(/\/admin\/invoice/);
        await expect(invoicePage.dataTable).toBeVisible();
      });
    });

    test('TC_INVOICE_002: Modal buat invoice dapat dibuka', async ({ page }) => {
      await test.step('Buka halaman invoice', async () => {
        await invoicePage.gotoAdminIndex();
      });
      await test.step('Klik tombol Buat Invoice', async () => {
        const btn = page.locator('button[type="button"]').filter({ hasText: 'Buat Invoice' });
        await btn.click();
      });
      await test.step('Verifikasi modal form', async () => {
        await expect(page.locator('#inv_pt_klien_id')).toBeVisible({ timeout: 5000 });
        await expect(page.locator('#inv_periode_id')).toBeVisible({ timeout: 5000 });
      });
    });

    test('TC_INVOICE_003: Filter invoice berdasarkan status', async ({ page }) => {
      await test.step('Buka halaman invoice', async () => {
        await invoicePage.gotoAdminIndex();
      });
      await test.step('Filter berdasarkan status menunggu_approval', async () => {
        await invoicePage.filterStatus.selectOption('menunggu_approval');
        await page.locator('button:has-text("Filter")').click();
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi URL filter', async () => {
        await expect(page).toHaveURL(/status=menunggu_approval/);
      });
    });

    test('TC_INVOICE_004: Laporan invoice dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke laporan invoice', async () => {
        await page.goto(ROUTES.admin.laporan.invoice);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi halaman laporan', async () => {
        await expect(page).toHaveURL(/\/admin\/laporan\/invoice/);
      });
    });
  });

  test.describe('Pemilik PT — Invoice Approval', () => {
    let invoicePage: InvoicePage;

    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);
      await expect(page).toHaveURL(/\/owner\/dashboard/);
      invoicePage = new InvoicePage(page);
    });

    test('TC_INVOICE_005: Pemilik PT dapat melihat daftar invoice', async ({ page }) => {
      await test.step('Navigasi ke halaman invoice owner', async () => {
        await invoicePage.gotoOwnerIndex();
      });
      await test.step('Verifikasi halaman invoice owner', async () => {
        await expect(page).toHaveURL(/\/owner\/invoice/);
      });
    });

    test('TC_INVOICE_006: Pemilik PT dapat melihat detail invoice', async ({ page }) => {
      await test.step('Buka halaman invoice owner', async () => {
        await invoicePage.gotoOwnerIndex();
      });
      await test.step('Klik invoice pertama jika ada', async () => {
        const firstRow = page.locator('table tbody tr').first();
        const link = firstRow.locator('a').first();
        if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
          await link.click();
          await waitForPageLoad(page);
          await expect(page).toHaveURL(/\/owner\/invoice\/\d+/);
        }
      });
    });

    // ═══════════════════════════════════════════════════════════
    // NEGATIVE TEST — Reject tanpa alasan
    // ═══════════════════════════════════════════════════════════

    test('TC_INVOICE_007: Reject invoice tanpa alasan gagal', async ({ page }) => {
      await test.step('Buka detail invoice menunggu approval', async () => {
        await invoicePage.gotoOwnerIndex();
        const firstRow = page.locator('table tbody tr').first();
        const link = firstRow.locator('a').first();
        if (await link.isVisible({ timeout: 3000 }).catch(() => false)) {
          await link.click();
          await waitForPageLoad(page);

          // Cek apakah ada tombol Tolak
          if (await invoicePage.rejectButton.isVisible({ timeout: 2000 }).catch(() => false)) {
            await test.step('Klik Tolak tanpa isi alasan', async () => {
              await invoicePage.rejectButton.click();
              await expect(invoicePage.rejectReasonInput).toBeVisible();
              // Submit tanpa isi alasan — HTML5 required akan mencegah
              await page.locator('button:has-text("Tolak Invoice")').click();
              // Harus tetap di halaman yang sama
              await expect(invoicePage.rejectReasonInput).toBeVisible();
            });
          }
        }
      });
    });
  });
});
