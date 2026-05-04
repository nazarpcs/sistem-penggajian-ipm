import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_RBAC — Test Suite Role-Based Access Control
 * @see Req 2.1-2.6, Property 5, Property 6
 */
test.describe('TC_RBAC: Role-Based Access Control', () => {

  // ═══════════════════════════════════════════════════════════
  // ADMIN ACCESS
  // ═══════════════════════════════════════════════════════════

  test.describe('Admin Role', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('TC_RBAC_001: Admin dapat mengakses dashboard admin', async ({ page }) => {
      await test.step('Verifikasi dashboard admin', async () => {
        await expect(page.locator('text=Total Karyawan Aktif')).toBeVisible();
      });
    });

    test('TC_RBAC_002: Admin dapat mengakses halaman karyawan', async ({ page }) => {
      await test.step('Navigasi ke halaman karyawan', async () => {
        await page.goto(ROUTES.admin.karyawan);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/karyawan/);
      });
    });

    test('TC_RBAC_003: Admin dapat mengakses halaman PT Klien', async ({ page }) => {
      await test.step('Navigasi ke halaman PT Klien', async () => {
        await page.goto(ROUTES.admin.ptKlien);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/pt-klien/);
      });
    });

    test('TC_RBAC_004: Admin dapat mengakses halaman absensi', async ({ page }) => {
      await test.step('Navigasi ke halaman absensi', async () => {
        await page.goto(ROUTES.admin.absensi);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/absensi/);
      });
    });

    test('TC_RBAC_005: Admin dapat mengakses halaman invoice', async ({ page }) => {
      await test.step('Navigasi ke halaman invoice', async () => {
        await page.goto(ROUTES.admin.invoice);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/invoice/);
      });
    });

    test('TC_RBAC_006: Admin dapat mengakses audit log', async ({ page }) => {
      await test.step('Navigasi ke halaman audit log', async () => {
        await page.goto(ROUTES.admin.auditLog);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/admin\/audit-log/);
      });
    });
  });

  // ═══════════════════════════════════════════════════════════
  // PEMILIK PT ACCESS
  // ═══════════════════════════════════════════════════════════

  test.describe('Pemilik PT Role', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);
      await expect(page).toHaveURL(/\/owner\/dashboard/);
    });

    test('TC_RBAC_007: Pemilik PT dapat mengakses dashboard owner', async ({ page }) => {
      await test.step('Verifikasi dashboard owner', async () => {
        await expect(page).toHaveURL(/\/owner\/dashboard/);
      });
    });

    test('TC_RBAC_008: Pemilik PT dapat mengakses halaman invoice', async ({ page }) => {
      await test.step('Navigasi ke halaman invoice owner', async () => {
        await page.goto(ROUTES.owner.invoice);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/owner\/invoice/);
      });
    });

    test('TC_RBAC_009: Pemilik PT TIDAK dapat mengakses halaman admin karyawan', async ({ page }) => {
      await test.step('Coba akses halaman admin karyawan', async () => {
        const response = await page.goto(ROUTES.admin.karyawan);
        const status = response?.status() ?? 0;
        // Harus 403 atau redirect
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('owner')).toBeTruthy();
      });
    });

    test('TC_RBAC_010: Pemilik PT TIDAK dapat mengakses audit log', async ({ page }) => {
      await test.step('Coba akses halaman audit log', async () => {
        const response = await page.goto(ROUTES.admin.auditLog);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('owner')).toBeTruthy();
      });
    });

    test('TC_RBAC_011: Pemilik PT TIDAK dapat mengakses halaman absensi admin', async ({ page }) => {
      await test.step('Coba akses halaman absensi admin', async () => {
        const response = await page.goto(ROUTES.admin.absensi);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('owner')).toBeTruthy();
      });
    });
  });

  // ═══════════════════════════════════════════════════════════
  // KARYAWAN ACCESS
  // ═══════════════════════════════════════════════════════════

  test.describe('Karyawan Role', () => {
    test.beforeEach(async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });

    test('TC_RBAC_012: Karyawan dapat mengakses profil', async ({ page }) => {
      await test.step('Verifikasi halaman profil', async () => {
        await page.goto(ROUTES.karyawan.profil);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/karyawan\/profil/);
      });
    });

    test('TC_RBAC_013: Karyawan dapat mengakses riwayat absensi', async ({ page }) => {
      await test.step('Navigasi ke riwayat absensi', async () => {
        await page.goto(ROUTES.karyawan.absensi);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/karyawan\/absensi/);
      });
    });

    test('TC_RBAC_014: Karyawan dapat mengakses slip gaji', async ({ page }) => {
      await test.step('Navigasi ke slip gaji', async () => {
        await page.goto(ROUTES.karyawan.slipGaji);
        await waitForPageLoad(page);
        await expect(page).toHaveURL(/\/karyawan\/slip-gaji/);
      });
    });

    test('TC_RBAC_015: Karyawan TIDAK dapat mengakses halaman admin', async ({ page }) => {
      await test.step('Coba akses dashboard admin', async () => {
        const response = await page.goto(ROUTES.admin.dashboard);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('karyawan')).toBeTruthy();
      });
    });

    test('TC_RBAC_016: Karyawan TIDAK dapat mengakses halaman owner', async ({ page }) => {
      await test.step('Coba akses dashboard owner', async () => {
        const response = await page.goto(ROUTES.owner.dashboard);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('karyawan')).toBeTruthy();
      });
    });

    test('TC_RBAC_017: Karyawan TIDAK dapat mengakses CRUD karyawan', async ({ page }) => {
      await test.step('Coba akses halaman karyawan admin', async () => {
        const response = await page.goto(ROUTES.admin.karyawan);
        const status = response?.status() ?? 0;
        expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('karyawan')).toBeTruthy();
      });
    });
  });

  // ═══════════════════════════════════════════════════════════
  // UNAUTHENTICATED ACCESS
  // ═══════════════════════════════════════════════════════════

  test.describe('Unauthenticated Access', () => {
    test('TC_RBAC_018: Akses admin tanpa login redirect ke login', async ({ page }) => {
      await test.step('Akses admin dashboard tanpa login', async () => {
        await page.goto(ROUTES.admin.dashboard);
        await expect(page).toHaveURL(/\/login/);
      });
    });

    test('TC_RBAC_019: Akses owner tanpa login redirect ke login', async ({ page }) => {
      await test.step('Akses owner dashboard tanpa login', async () => {
        await page.goto(ROUTES.owner.dashboard);
        await expect(page).toHaveURL(/\/login/);
      });
    });

    test('TC_RBAC_020: Akses karyawan tanpa login redirect ke login', async ({ page }) => {
      await test.step('Akses karyawan profil tanpa login', async () => {
        await page.goto(ROUTES.karyawan.profil);
        await expect(page).toHaveURL(/\/login/);
      });
    });
  });
});
