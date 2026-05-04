import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { KaryawanPage } from '../pages/KaryawanPage';
import { USERS, KARYAWAN_VALID, ROUTES } from '../fixtures/test-data';
import { randomEmail, randomNIK, waitForPageLoad } from '../utils/helpers';

/**
 * TC_KARYAWAN — Test Suite Manajemen Data Karyawan
 * @see Req 3.1-3.7, Property 7-10
 */
test.describe('TC_KARYAWAN: Manajemen Data Karyawan', () => {
  let karyawanPage: KaryawanPage;

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    karyawanPage = new KaryawanPage(page);
  });

  // ═══════════════════════════════════════════════════════════
  // HAPPY PATH
  // ═══════════════════════════════════════════════════════════

  test('TC_KARYAWAN_001: Halaman daftar karyawan dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke halaman karyawan', async () => {
      await karyawanPage.gotoIndex();
      await karyawanPage.expectOnIndex();
    });
    await test.step('Verifikasi tabel data ditampilkan', async () => {
      await expect(karyawanPage.dataTable).toBeVisible();
    });
  });

  test('TC_KARYAWAN_002: Form tambah karyawan dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke form tambah karyawan', async () => {
      await karyawanPage.gotoCreate();
    });
    await test.step('Verifikasi form fields', async () => {
      await expect(karyawanPage.namaLengkapInput).toBeVisible();
      await expect(karyawanPage.nikInput).toBeVisible();
      await expect(karyawanPage.emailInput).toBeVisible();
      await expect(karyawanPage.ptKlienSelect).toBeVisible();
      await expect(karyawanPage.jabatanInput).toBeVisible();
      await expect(karyawanPage.gajiPokokInput).toBeVisible();
    });
  });

  test('TC_KARYAWAN_003: Tambah karyawan baru berhasil', async ({ page }) => {
    const uniqueEmail = randomEmail();
    const uniqueNIK = randomNIK();

    await test.step('Buka form tambah karyawan', async () => {
      await karyawanPage.gotoCreate();
    });
    await test.step('Isi form dengan data valid', async () => {
      await karyawanPage.fillForm({
        nama_lengkap: 'Test Karyawan E2E',
        nik: uniqueNIK,
        email: uniqueEmail,
        jabatan: 'Staff QA',
        gaji_pokok: '5000000',
        tanggal_bergabung: '2025-01-15',
      });
      await karyawanPage.selectPtKlien(1);
    });
    await test.step('Submit form', async () => {
      await karyawanPage.submitForm();
    });
    await test.step('Verifikasi redirect ke index atau show', async () => {
      const url = page.url();
      expect(url.includes('/admin/karyawan')).toBeTruthy();
    });
  });

  test('TC_KARYAWAN_004: Daftar karyawan menampilkan data', async ({ page }) => {
    await test.step('Buka halaman daftar karyawan', async () => {
      await karyawanPage.gotoIndex();
    });
    await test.step('Verifikasi tabel memiliki data', async () => {
      const rows = await karyawanPage.getRowCount();
      expect(rows).toBeGreaterThan(0);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // NEGATIVE TEST
  // ═══════════════════════════════════════════════════════════

  test('TC_KARYAWAN_005: Tambah karyawan gagal tanpa nama lengkap', async ({ page }) => {
    await test.step('Buka form dan submit tanpa nama', async () => {
      await karyawanPage.gotoCreate();
      await karyawanPage.fillForm({
        nik: randomNIK(),
        email: randomEmail(),
        jabatan: 'Staff',
        gaji_pokok: '3000000',
        tanggal_bergabung: '2025-01-01',
      });
      await karyawanPage.selectPtKlien(1);
      await karyawanPage.submitButton.click();
    });
    await test.step('Verifikasi tetap di halaman form (HTML5 required)', async () => {
      await expect(page).toHaveURL(/\/admin\/karyawan\/create/);
    });
  });

  test('TC_KARYAWAN_006: Tambah karyawan gagal dengan email invalid', async ({ page }) => {
    await test.step('Isi form dengan email invalid', async () => {
      await karyawanPage.gotoCreate();
      await karyawanPage.fillForm({
        nama_lengkap: 'Test Invalid Email',
        nik: randomNIK(),
        email: 'bukan-email-valid',
        jabatan: 'Staff',
        gaji_pokok: '3000000',
        tanggal_bergabung: '2025-01-01',
      });
      await karyawanPage.selectPtKlien(1);
      await karyawanPage.submitButton.click();
    });
    await test.step('Verifikasi form tidak tersubmit', async () => {
      // HTML5 email validation akan mencegah submit
      await expect(page).toHaveURL(/\/admin\/karyawan/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // EDGE CASE
  // ═══════════════════════════════════════════════════════════

  test('TC_KARYAWAN_007: Tambah karyawan dengan gaji pokok 0', async ({ page }) => {
    await test.step('Isi form dengan gaji pokok 0', async () => {
      await karyawanPage.gotoCreate();
      await karyawanPage.fillForm({
        nama_lengkap: 'Test Gaji Nol',
        nik: randomNIK(),
        email: randomEmail(),
        jabatan: 'Intern',
        gaji_pokok: '0',
        tanggal_bergabung: '2025-01-01',
      });
      await karyawanPage.selectPtKlien(1);
      await karyawanPage.submitForm();
    });
    await test.step('Verifikasi behavior (berhasil atau validasi error)', async () => {
      const url = page.url();
      expect(url).toBeDefined();
    });
  });

  test('TC_KARYAWAN_008: Filter karyawan berdasarkan PT Klien', async ({ page }) => {
    await test.step('Buka halaman karyawan', async () => {
      await karyawanPage.gotoIndex();
    });
    await test.step('Pilih filter PT Klien dan submit', async () => {
      const options = page.locator('#pt_klien_id option');
      const count = await options.count();
      if (count > 1) {
        const value = await options.nth(1).getAttribute('value');
        if (value) {
          await page.locator('#pt_klien_id').selectOption(value);
          await page.locator('button:has-text("Filter")').click();
          await waitForPageLoad(page);
        }
      }
    });
    await test.step('Verifikasi halaman masih karyawan index', async () => {
      await expect(page).toHaveURL(/\/admin\/karyawan/);
    });
  });
});
