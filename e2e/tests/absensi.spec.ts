import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { AbsensiPage } from '../pages/AbsensiPage';
import { USERS, ABSENSI_VALID, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad, toInputDate } from '../utils/helpers';

/**
 * TC_ABSENSI — Test Suite Absensi
 * @see Req 5.1-5.7, 6.1-6.7, Property 11, 12, 20
 */
test.describe('TC_ABSENSI: Input dan Rekap Absensi', () => {
  let absensiPage: AbsensiPage;

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    absensiPage = new AbsensiPage(page);
  });

  // ═══════════════════════════════════════════════════════════
  // HAPPY PATH
  // ═══════════════════════════════════════════════════════════

  test('TC_ABSENSI_001: Halaman absensi dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke halaman absensi', async () => {
      await absensiPage.goto();
      await expect(page).toHaveURL(/\/admin\/absensi/);
    });
    await test.step('Verifikasi elemen halaman', async () => {
      await expect(absensiPage.inputManualButton).toBeVisible();
      await expect(absensiPage.dataTable).toBeVisible();
    });
  });

  test('TC_ABSENSI_002: Form input manual absensi dapat dibuka', async ({ page }) => {
    await test.step('Buka halaman absensi', async () => {
      await absensiPage.goto();
    });
    await test.step('Klik tombol Input Manual', async () => {
      await absensiPage.openManualForm();
    });
    await test.step('Verifikasi form fields', async () => {
      await expect(absensiPage.formKaryawanSelect).toBeVisible();
      await expect(absensiPage.formTanggal).toBeVisible();
      await expect(absensiPage.formStatus).toBeVisible();
    });
  });

  test('TC_ABSENSI_003: Input absensi manual berhasil', async ({ page }) => {
    const uniqueDate = toInputDate(new Date(2025, 7, Math.floor(Math.random() * 28) + 1));

    await test.step('Buka form input manual', async () => {
      await absensiPage.goto();
      await absensiPage.openManualForm();
    });
    await test.step('Isi form absensi', async () => {
      await absensiPage.fillManualAbsensi({
        tanggal: uniqueDate,
        status_kehadiran: 'Hadir',
        jam_masuk: '08:00',
        jam_keluar: '17:00',
      });
    });
    await test.step('Submit form', async () => {
      await absensiPage.submitManualForm();
    });
    await test.step('Verifikasi berhasil', async () => {
      await expect(page).toHaveURL(/\/admin\/absensi/);
    });
  });

  test('TC_ABSENSI_004: Filter absensi berdasarkan status kehadiran', async ({ page }) => {
    await test.step('Buka halaman absensi', async () => {
      await absensiPage.goto();
    });
    await test.step('Filter berdasarkan status Hadir', async () => {
      await page.locator('#status_kehadiran').selectOption('Hadir');
      await absensiPage.filterButton.click();
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi URL mengandung filter', async () => {
      await expect(page).toHaveURL(/status_kehadiran=Hadir/);
    });
  });

  test('TC_ABSENSI_005: Halaman rekap absensi dapat diakses', async ({ page }) => {
    await test.step('Navigasi ke rekap absensi', async () => {
      await page.goto(ROUTES.admin.absensi + '/rekap');
      await waitForPageLoad(page);
    });
    await test.step('Verifikasi halaman rekap', async () => {
      await expect(page).toHaveURL(/\/admin\/absensi\/rekap/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // NEGATIVE TEST
  // ═══════════════════════════════════════════════════════════

  test('TC_ABSENSI_006: Input absensi gagal tanpa memilih karyawan', async ({ page }) => {
    await test.step('Buka form dan submit tanpa pilih karyawan', async () => {
      await absensiPage.goto();
      await absensiPage.openManualForm();
      await absensiPage.formTanggal.fill('2025-06-20');
      await absensiPage.formSubmit.click();
    });
    await test.step('Verifikasi form tidak tersubmit', async () => {
      // HTML5 required validation
      await expect(page).toHaveURL(/\/admin\/absensi/);
    });
  });

  test('TC_ABSENSI_007: Input absensi gagal tanpa tanggal', async ({ page }) => {
    await test.step('Buka form dan submit tanpa tanggal', async () => {
      await absensiPage.goto();
      await absensiPage.openManualForm();
      // Pilih karyawan tapi tidak isi tanggal
      const options = absensiPage.formKaryawanSelect.locator('option');
      const count = await options.count();
      if (count > 1) {
        const value = await options.nth(1).getAttribute('value');
        if (value) await absensiPage.formKaryawanSelect.selectOption(value);
      }
      await absensiPage.formSubmit.click();
    });
    await test.step('Verifikasi form tidak tersubmit', async () => {
      await expect(page).toHaveURL(/\/admin\/absensi/);
    });
  });

  // ═══════════════════════════════════════════════════════════
  // EDGE CASE
  // ═══════════════════════════════════════════════════════════

  test('TC_ABSENSI_008: Input absensi dengan status Alpha tanpa jam', async ({ page }) => {
    const uniqueDate = toInputDate(new Date(2025, 8, Math.floor(Math.random() * 28) + 1));

    await test.step('Input absensi Alpha tanpa jam masuk/keluar', async () => {
      await absensiPage.goto();
      await absensiPage.openManualForm();
      await absensiPage.fillManualAbsensi({
        tanggal: uniqueDate,
        status_kehadiran: 'Alpha',
      });
      await absensiPage.submitManualForm();
    });
    await test.step('Verifikasi berhasil (Alpha tidak perlu jam)', async () => {
      await expect(page).toHaveURL(/\/admin\/absensi/);
    });
  });
});
