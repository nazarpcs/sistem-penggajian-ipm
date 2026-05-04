import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_PAYROLL — Test Suite Penggajian
 * @see Req 7.1-7.8, 8.1-8.6, Property 13, 14, 18
 */
test.describe('TC_PAYROLL: Perhitungan Gaji dan Slip Gaji', () => {

  test.describe('Admin — Penggajian', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('TC_PAYROLL_001: Halaman penggajian dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke halaman penggajian', async () => {
        await page.goto(ROUTES.admin.penggajian);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi halaman penggajian', async () => {
        await expect(page).toHaveURL(/\/admin\/penggajian/);
      });
    });

    test('TC_PAYROLL_002: Halaman slip gaji admin menampilkan tabel', async ({ page }) => {
      await test.step('Buka halaman penggajian', async () => {
        await page.goto(ROUTES.admin.penggajian);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi tabel slip gaji', async () => {
        await expect(page.locator('table')).toBeVisible();
      });
    });

    test('TC_PAYROLL_003: Halaman laporan penggajian dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke laporan penggajian', async () => {
        await page.goto(ROUTES.admin.laporan.penggajian);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi halaman laporan', async () => {
        await expect(page).toHaveURL(/\/admin\/laporan\/penggajian/);
      });
    });
  });

  test.describe('Karyawan — Slip Gaji Self-Service', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });

    test('TC_PAYROLL_004: Karyawan dapat mengakses halaman slip gaji', async ({ page }) => {
      await test.step('Navigasi ke slip gaji', async () => {
        await page.goto(ROUTES.karyawan.slipGaji);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi halaman slip gaji', async () => {
        await expect(page).toHaveURL(/\/karyawan\/slip-gaji/);
        await expect(page.locator('text=Daftar Slip Gaji')).toBeVisible();
      });
    });

    test('TC_PAYROLL_005: Karyawan melihat tabel slip gaji dengan kolom lengkap', async ({ page }) => {
      await test.step('Buka halaman slip gaji', async () => {
        await page.goto(ROUTES.karyawan.slipGaji);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi kolom tabel', async () => {
        const headers = page.locator('table thead th');
        await expect(headers.filter({ hasText: 'Periode' })).toBeVisible();
        await expect(headers.filter({ hasText: 'Gaji Pokok' })).toBeVisible();
        await expect(headers.filter({ hasText: 'Gaji Bersih' })).toBeVisible();
      });
    });
  });
});
