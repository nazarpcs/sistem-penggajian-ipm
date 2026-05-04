import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { AdminDashboardPage } from '../pages/AdminDashboardPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_DASHBOARD — Test Suite Dashboard dan Laporan
 * @see Req 10.1-10.7
 */
test.describe('TC_DASHBOARD: Dashboard dan Laporan', () => {

  test.describe('Admin Dashboard', () => {
    let dashboardPage: AdminDashboardPage;

    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      dashboardPage = new AdminDashboardPage(page);
    });

    test('TC_DASHBOARD_001: Dashboard admin menampilkan stat cards', async ({ page }) => {
      await test.step('Verifikasi stat cards', async () => {
        await dashboardPage.expectDashboardVisible();
        await expect(page.locator('text=Total Karyawan Aktif')).toBeVisible();
        await expect(page.locator('text=Total PT Klien Aktif')).toBeVisible();
        await expect(page.locator('text=Penggajian Bulan Ini')).toBeVisible();
        await expect(page.locator('text=Invoice Pending')).toBeVisible();
      });
    });

    test('TC_DASHBOARD_002: Dashboard admin menampilkan tabel invoice pending', async ({ page }) => {
      await test.step('Verifikasi tabel invoice pending', async () => {
        await expect(page.locator('h2:has-text("Invoice Menunggu Approval")')).toBeVisible();
      });
    });

    test('TC_DASHBOARD_003: Laporan absensi admin dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke laporan absensi', async () => {
        await page.goto(ROUTES.admin.laporan.absensi);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/laporan\/absensi/);
      });
    });

    test('TC_DASHBOARD_004: Laporan penggajian admin dapat diakses', async ({ page }) => {
      await test.step('Navigasi ke laporan penggajian', async () => {
        await page.goto(ROUTES.admin.laporan.penggajian);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/laporan\/penggajian/);
      });
    });
  });

  test.describe('Owner Dashboard', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);
    });

    test('TC_DASHBOARD_005: Dashboard owner dapat diakses', async ({ page }) => {
      await test.step('Verifikasi dashboard owner', async () => {
        await expect(page).toHaveURL(/\/owner\/dashboard/);
      });
    });

    test('TC_DASHBOARD_006: Owner dapat mengakses laporan', async ({ page }) => {
      await test.step('Navigasi ke laporan absensi owner', async () => {
        await page.goto('/owner/laporan/absensi');
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/owner\/laporan\/absensi/);
      });
    });
  });
});
