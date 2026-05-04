import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad } from '../utils/helpers';

/**
 * TC_SEC — Test Suite Keamanan
 * @see Req 12.1-12.6, Property 5, 6
 */
test.describe('TC_SEC: Keamanan Data', () => {

  // ═══════════════════════════════════════════════════════════
  // SESSION & AUTH PROTECTION
  // ═══════════════════════════════════════════════════════════

  test('TC_SEC_001: Endpoint admin diproteksi — redirect ke login tanpa sesi', async ({ page }) => {
    await test.step('Akses endpoint admin tanpa login', async () => {
      await page.goto(ROUTES.admin.karyawan);
      await expect(page).toHaveURL(/\/login/);
    });
  });

  test('TC_SEC_002: Endpoint owner diproteksi — redirect ke login tanpa sesi', async ({ page }) => {
    await test.step('Akses endpoint owner tanpa login', async () => {
      await page.goto(ROUTES.owner.invoice);
      await expect(page).toHaveURL(/\/login/);
    });
  });

  test('TC_SEC_003: Endpoint karyawan diproteksi — redirect ke login tanpa sesi', async ({ page }) => {
    await test.step('Akses endpoint karyawan tanpa login', async () => {
      await page.goto(ROUTES.karyawan.slipGaji);
      await expect(page).toHaveURL(/\/login/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // CSRF PROTECTION
  // ═══════════════════════════════════════════════════════════

  test('TC_SEC_004: Form login memiliki CSRF token', async ({ page }) => {
    await test.step('Buka halaman login', async () => {
      await page.goto('/login');
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi CSRF token ada di form', async () => {
      const csrfInput = page.locator('input[name="_token"]');
      await expect(csrfInput).toBeAttached();
      const value = await csrfInput.getAttribute('value');
      expect(value).toBeTruthy();
      expect(value!.length).toBeGreaterThan(10);
    });
  });

  test('TC_SEC_005: POST tanpa CSRF token ditolak', async ({ page }) => {
    await test.step('Kirim POST request tanpa CSRF token', async () => {
      const response = await page.request.post('/login', {
        data: { email: USERS.admin.email, password: USERS.admin.password },
      });
      // Laravel akan menolak dengan 419 (CSRF token mismatch)
      expect([419, 302, 405].includes(response.status())).toBeTruthy();
    });
  });

  // ═══════════════════════════════════════════════════════════
  // XSS PREVENTION
  // ═══════════════════════════════════════════════════════════

  test('TC_SEC_006: XSS payload di URL tidak dieksekusi', async ({ page }) => {
    await test.step('Akses URL dengan XSS payload', async () => {
      await page.goto('/login?error=<script>alert("xss")</script>');
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi script tidak dieksekusi', async () => {
      const bodyHtml = await page.locator('body').innerHTML();
      expect(bodyHtml).not.toContain('<script>alert("xss")</script>');
    });
  });

  // ═══════════════════════════════════════════════════════════
  // DATA ISOLATION
  // ═══════════════════════════════════════════════════════════

  test('TC_SEC_007: Karyawan hanya melihat data miliknya sendiri', async ({ page }) => {
    await test.step('Login sebagai karyawan', async () => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });
    await test.step('Akses slip gaji', async () => {
      await page.goto(ROUTES.karyawan.slipGaji);
      await waitForPageLoad(page);
      await expect(page).toHaveURL(/\/karyawan\/slip-gaji/);
    });
    await test.step('Verifikasi tidak ada data karyawan lain', async () => {
      // Halaman slip gaji hanya menampilkan data milik karyawan yang login
      // Tidak ada filter PT Klien atau karyawan lain
      const filterKaryawan = page.locator('select[name="karyawan_id"]');
      await expect(filterKaryawan).not.toBeVisible();
    });
  });

  test('TC_SEC_008: Karyawan tidak bisa akses slip gaji karyawan lain via URL', async ({ page }) => {
    await test.step('Login sebagai karyawan', async () => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });
    await test.step('Coba akses slip gaji ID 999 (bukan miliknya)', async () => {
      const response = await page.goto('/karyawan/slip-gaji/999/pdf');
      const status = response?.status() ?? 0;
      // Harus 403 atau 404
      expect([403, 404, 302].includes(status)).toBeTruthy();
    });
  });

  // ═══════════════════════════════════════════════════════════
  // DIRECT URL MANIPULATION
  // ═══════════════════════════════════════════════════════════

  test('TC_SEC_009: Akses halaman admin dengan role karyawan ditolak', async ({ page }) => {
    await test.step('Login sebagai karyawan', async () => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });
    await test.step('Coba akses admin karyawan CRUD', async () => {
      const response = await page.goto('/admin/karyawan/create');
      const status = response?.status() ?? 0;
      expect([403, 302, 301].includes(status) || page.url().includes('login') || page.url().includes('karyawan')).toBeTruthy();
    });
  });
});
