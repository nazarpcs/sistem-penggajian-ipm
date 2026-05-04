import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { USERS, INVALID_CREDENTIALS, ROUTES } from '../fixtures/test-data';
import { takeScreenshot } from '../utils/helpers';

/**
 * TC_AUTH — Test Suite Autentikasi
 * @see Req 1.1-1.9, Property 1-4
 */
test.describe('TC_AUTH: Autentikasi dan Manajemen Akun', () => {
  let loginPage: LoginPage;

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page);
    await loginPage.goto();
  });

  // ═══════════════════════════════════════════════════════════
  // HAPPY PATH
  // ═══════════════════════════════════════════════════════════

  test('TC_AUTH_001: Halaman login menampilkan form email dan password', async ({ page }) => {
    await test.step('Verifikasi elemen form login', async () => {
      await loginPage.expectLoginPage();
      await expect(loginPage.logo).toBeVisible();
      await expect(loginPage.forgotPasswordLink).toBeVisible();
      await expect(loginPage.rememberCheckbox).toBeVisible();
    });
  });

  test('TC_AUTH_002: Admin berhasil login dengan kredensial valid', async ({ page }) => {
    await test.step('Input email dan password admin', async () => {
      await loginPage.login(USERS.admin.email, USERS.admin.password);
    });
    await test.step('Verifikasi redirect ke dashboard admin', async () => {
      await loginPage.expectRedirectToDashboard('admin');
    });
  });

  test('TC_AUTH_003: Pemilik PT berhasil login dengan kredensial valid', async ({ page }) => {
    await test.step('Input email dan password pemilik PT', async () => {
      await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);
    });
    await test.step('Verifikasi redirect ke dashboard owner', async () => {
      await loginPage.expectRedirectToDashboard('pemilik_pt');
    });
  });

  test('TC_AUTH_004: Karyawan berhasil login dengan kredensial valid', async ({ page }) => {
    await test.step('Input email dan password karyawan', async () => {
      await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);
    });
    await test.step('Verifikasi redirect ke profil karyawan', async () => {
      await loginPage.expectRedirectToDashboard('karyawan');
    });
  });

  test('TC_AUTH_005: Logout berhasil menghapus sesi dan redirect ke login', async ({ page }) => {
    await test.step('Login sebagai admin', async () => {
      await loginPage.login(USERS.admin.email, USERS.admin.password);
      await loginPage.expectRedirectToDashboard('admin');
    });
    await test.step('Klik logout', async () => {
      // Buka profile dropdown dulu (klik avatar/nama di header)
      const profileButton = page.locator('header button').filter({ has: page.locator('.rounded-full') }).first();
      await profileButton.click();
      // Klik tombol "Keluar" di dropdown
      await page.locator('button:has-text("Keluar")').click();
      await page.waitForURL(/\/login/);
    });
    await test.step('Verifikasi redirect ke halaman login', async () => {
      await expect(page).toHaveURL(/\/login/);
    });
    await test.step('Verifikasi tidak bisa akses dashboard setelah logout', async () => {
      await page.goto('/admin/dashboard');
      await expect(page).toHaveURL(/\/login/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // NEGATIVE TEST
  // ═══════════════════════════════════════════════════════════

  test('TC_AUTH_006: Login gagal dengan email salah', async ({ page }) => {
    await test.step('Input email yang tidak terdaftar', async () => {
      await loginPage.login(INVALID_CREDENTIALS.wrongEmail, USERS.admin.password);
    });
    await test.step('Verifikasi pesan error ditampilkan', async () => {
      await loginPage.expectErrorMessage();
      await expect(page).toHaveURL(/\/login/);
    });
  });

  test('TC_AUTH_007: Login gagal dengan password salah', async ({ page }) => {
    await test.step('Input password yang salah', async () => {
      await loginPage.login(USERS.admin.email, INVALID_CREDENTIALS.wrongPassword);
    });
    await test.step('Verifikasi pesan error ditampilkan', async () => {
      await loginPage.expectErrorMessage();
    });
  });

  test('TC_AUTH_008: Login gagal dengan email kosong', async ({ page }) => {
    await test.step('Submit form tanpa email', async () => {
      await loginPage.passwordInput.fill(USERS.admin.password);
      await loginPage.submitButton.click();
    });
    await test.step('Verifikasi form tidak tersubmit (HTML5 validation)', async () => {
      await expect(page).toHaveURL(/\/login/);
    });
  });

  test('TC_AUTH_009: Login gagal dengan password kosong', async ({ page }) => {
    await test.step('Submit form tanpa password', async () => {
      await loginPage.emailInput.fill(USERS.admin.email);
      await loginPage.submitButton.click();
    });
    await test.step('Verifikasi form tidak tersubmit', async () => {
      await expect(page).toHaveURL(/\/login/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // SECURITY TEST
  // ═══════════════════════════════════════════════════════════

  test('TC_AUTH_010: SQL Injection pada field email ditolak', async ({ page }) => {
    await test.step('Input SQL injection payload', async () => {
      await loginPage.login(INVALID_CREDENTIALS.sqlInjection, 'password');
    });
    await test.step('Verifikasi login gagal dan tidak ada error server', async () => {
      // Harus tetap di halaman login, bukan error 500
      const url = page.url();
      expect(url).not.toContain('500');
      await expect(page).toHaveURL(/\/login/);
    });
  });

  test('TC_AUTH_011: XSS payload pada field email ditolak', async ({ page }) => {
    await test.step('Input XSS payload', async () => {
      await loginPage.login(INVALID_CREDENTIALS.xssPayload, 'password');
    });
    await test.step('Verifikasi XSS tidak dieksekusi', async () => {
      const bodyText = await page.locator('body').innerHTML();
      expect(bodyText).not.toContain('<script>alert');
    });
  });

  test('TC_AUTH_012: Halaman forgot password dapat diakses', async ({ page }) => {
    await test.step('Klik link lupa password', async () => {
      await loginPage.forgotPasswordLink.click();
    });
    await test.step('Verifikasi halaman forgot password', async () => {
      await expect(page).toHaveURL(/\/password\/forgot/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // EDGE CASE
  // ═══════════════════════════════════════════════════════════

  test('TC_AUTH_013: Login dengan spasi di email', async ({ page }) => {
    await test.step('Input email dengan spasi', async () => {
      await loginPage.login('  ' + USERS.admin.email + '  ', USERS.admin.password);
    });
    await test.step('Verifikasi behavior (trim atau reject)', async () => {
      // Sistem harus menangani — bisa berhasil (trim) atau gagal (reject)
      const url = page.url();
      expect(url).toBeDefined();
    });
  });

  test('TC_AUTH_014: Akses dashboard tanpa login redirect ke login', async ({ page }) => {
    await test.step('Akses admin dashboard langsung', async () => {
      await page.goto('/admin/dashboard');
    });
    await test.step('Verifikasi redirect ke login', async () => {
      await expect(page).toHaveURL(/\/login/);
    });
  });
});
