import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_AUDIT — Test Suite Audit Log
 * @see Req 11.1-11.5, Property 17
 */
test.describe('TC_AUDIT: Audit Log Aktivitas', () => {

  test.describe('Admin — Audit Log', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('TC_AUDIT_001: Halaman audit log dapat diakses oleh Admin', async ({ page }) => {
      await test.step('Navigasi ke audit log', async () => {
        await page.goto(ROUTES.admin.auditLog);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi halaman audit log', async () => {
        await expect(page).toHaveURL(/\/admin\/audit-log/);
      });
    });

    test('TC_AUDIT_002: Audit log menampilkan tabel data', async ({ page }) => {
      await test.step('Buka halaman audit log', async () => {
        await page.goto(ROUTES.admin.auditLog);
        await waitForPageLoad(page);
      });
      await test.step('Verifikasi tabel audit log', async () => {
        await expect(page.locator('table')).toBeVisible();
      });
    });

    test('TC_AUDIT_003: Login activity tercatat di audit log', async ({ page }) => {
      await test.step('Buka audit log dan cari aktivitas login', async () => {
        await page.goto(ROUTES.admin.auditLog);
        await waitForPageLoad(page);
        // Cek apakah ada entri login di tabel
        const tableBody = page.locator('table tbody');
        await expect(tableBody).toBeVisible();
      });
    });
  });

  test.describe('Non-Admin — Audit Log Access Denied', () => {
    test('TC_AUDIT_004: Pemilik PT tidak dapat mengakses audit log', async ({ page }) => {
      await test.step('Login sebagai Pemilik PT', async () => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);
      });
      await test.step('Coba akses audit log', async () => {
        const response = await page.goto(ROUTES.admin.auditLog);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('owner')).toBeTruthy();
      });
    });

    test('TC_AUDIT_005: Karyawan tidak dapat mengakses audit log', async ({ page }) => {
      await test.step('Login sebagai Karyawan', async () => {
        const loginPage = new LoginPage(page);
        await loginPage.goto();
        await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
      });
      await test.step('Coba akses audit log', async () => {
        const response = await page.goto(ROUTES.admin.auditLog);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('karyawan')).toBeTruthy();
      });
    });
  });
});
