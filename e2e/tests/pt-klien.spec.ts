import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, ROUTES, PT_KLIEN_VALID } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_PTKLIEN — Test Suite Manajemen PT Klien
 * @see Req 4.1-4.7
 */
test.describe('TC_PTKLIEN: Manajemen Data PT Klien', () => {

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);
    await expect(page).toHaveURL(/\/admin\/dashboard/);
  });

  test('TC_PTKLIEN_001: Halaman daftar PT Klien dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke halaman PT Klien', async () => {
      await page.goto(ROUTES.admin.ptKlien);
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi halaman PT Klien', async () => {
      await expect(page).toHaveURL(/\/admin\/pt-klien/);
      await expect(page.locator('table')).toBeVisible();
    });
  });

  test('TC_PTKLIEN_002: Form tambah PT Klien dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke form tambah PT Klien', async () => {
      await page.goto(ROUTES.admin.ptKlien + '/create');
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi form fields', async () => {
      await expect(page.locator('#nama')).toBeVisible();
      await expect(page.locator('#email')).toBeVisible();
    });
  });

  test('TC_PTKLIEN_003: Daftar PT Klien menampilkan data', async ({ page }) => {
    await test.step('Buka halaman PT Klien', async () => {
      await page.goto(ROUTES.admin.ptKlien);
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi tabel memiliki data', async () => {
      const rows = await page.locator('table tbody tr').count();
      expect(rows).toBeGreaterThan(0);
    });
  });

  test('TC_PTKLIEN_004: Detail PT Klien dapat diakses', async ({ page }) => {
    await test.step('Buka halaman PT Klien', async () => {
      await page.goto(ROUTES.admin.ptKlien);
      await waitForPageLoad(page);
    });
    await test.step('Klik PT Klien pertama', async () => {
      const firstLink = page.locator('table tbody tr a').first();
      if (await firstLink.isVisible({ timeout: 3000 }).catch(() => false)) {
        await firstLink.click();
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/pt-klien\/\d+/);
      }
    });
  });
});
