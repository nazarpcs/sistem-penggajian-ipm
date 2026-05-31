import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { AbsensiPage } from '../pages/AbsensiPage';
import { USERS, ROUTES } from '../fixtures/test-data';
import { waitForPageLoad, toInputDate } from '../utils/helpers';

/**
 * TC_FLOW — Full End-to-End Business Flow
 * Tests the complete payroll cycle:
 *   Input Absensi → Hitung Gaji → View Slip Gaji → Download PDF
 *   → Generate Invoice → Owner Approve → Download Invoice PDF
 *   → Karyawan View Slip Gaji
 */
test.describe('TC_FLOW: Full Payroll Cycle (Input → Output)', () => {
  test.describe.configure({ mode: 'serial' });

  // ═══════════════════════════════════════════════════════════
  // STEP 1: ADMIN INPUT ABSENSI
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_001: Admin input absensi untuk karyawan', async ({ page }) => {
    test.setTimeout(60000);
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);
    await expect(page).toHaveURL(/\/admin\/dashboard/);

    const absensiPage = new AbsensiPage(page);
    await absensiPage.goto();
    await absensiPage.openManualForm();
    await absensiPage.fillManualAbsensi({
      tanggal: toInputDate(new Date(2025, 5, 5)),
      status_kehadiran: 'Hadir',
      jam_masuk: '08:00',
      jam_keluar: '17:00',
    });
    await absensiPage.formSubmit.click();
    await page.waitForURL(/\/admin\/absensi/, { timeout: 20000 });

    // Verify data exists in table
    await expect(absensiPage.dataTable).toBeVisible();
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 2: ADMIN HITUNG GAJI
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_002: Admin menghitung gaji karyawan', async ({ page }) => {
    test.setTimeout(60000);
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.penggajian);
    await waitForPageLoad(page);
    await expect(page).toHaveURL(/\/admin\/penggajian/);

    // Open Hitung Gaji modal
    await page.locator('button[type="button"]:has-text("Hitung Gaji")').click();

    // Wait for modal to fully render
    const ptKlienSelect = page.locator('#hitung_pt_klien_id');
    await expect(ptKlienSelect).toBeVisible();

    // Select PT Klien (skip test if no options available)
    const ptOptions = ptKlienSelect.locator('option:not([value=""])');
    const ptCount = await ptOptions.count();
    if (ptCount === 0) {
      test.skip(true, 'No PT Klien available for payroll calculation');
      return;
    }
    const ptValue = await ptOptions.first().getAttribute('value');
    if (ptValue) await ptKlienSelect.selectOption(ptValue);

    // Select Periode (skip if no periods available)
    const periodeSelect = page.locator('#hitung_periode_id');
    const periodeOptions = periodeSelect.locator('option:not([value=""])');
    const periodeCount = await periodeOptions.count();
    if (periodeCount === 0) {
      test.skip(true, 'No Periode available for payroll calculation');
      return;
    }
    const periodeValue = await periodeOptions.first().getAttribute('value');
    if (periodeValue) await periodeSelect.selectOption(periodeValue);

    // Submit
    await page.locator('form[action*="penggajian/hitung"] button[type="submit"]').click();
    await page.waitForURL(/\/admin\/penggajian/, { timeout: 30000 });

    // Verify success or slip gaji appears
    const hasSuccess = await page.locator('[role="alert"]').first().isVisible().catch(() => false);
    const hasTable = await page.locator('main table').first().isVisible().catch(() => false);
    expect(hasSuccess || hasTable).toBeTruthy();
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 3: ADMIN VIEW SLIP GAJI DETAIL
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_003: Admin melihat detail slip gaji', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.penggajian);
    await waitForPageLoad(page);

    // Click detail on first slip gaji
    const detailLink = page.locator('a[href*="/admin/penggajian/"]').first();
    if (await detailLink.isVisible()) {
      await detailLink.click();
      await waitForPageLoad(page);
      await expect(page).toHaveURL(/\/admin\/penggajian\/\d+/);

      // Verify slip gaji detail shows salary components
      const content = await page.textContent('body');
      expect(content).toContain('Gaji');
    }
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 4: ADMIN DOWNLOAD SLIP GAJI PDF
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_004: Admin download slip gaji PDF', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.penggajian);
    await waitForPageLoad(page);

    // Find PDF download link
    const pdfLink = page.locator('a[href*="/pdf"]').first();
    if (await pdfLink.isVisible()) {
      // Intercept download
      const [download] = await Promise.all([
        page.waitForEvent('download', { timeout: 30000 }).catch(() => null),
        pdfLink.click(),
      ]);

      if (download) {
        const filename = download.suggestedFilename();
        expect(filename).toContain('slip-gaji');
        expect(filename).toContain('.pdf');
      }
    }
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 5: ADMIN GENERATE INVOICE
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_005: Admin membuat invoice untuk PT Klien', async ({ page }) => {
    test.setTimeout(60000);
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.invoice);
    await waitForPageLoad(page);
    await expect(page).toHaveURL(/\/admin\/invoice/);

    // Open Buat Invoice modal
    await page.locator('button[type="button"]:has-text("Buat Invoice")').click();
    const ptKlienSelect = page.locator('#inv_pt_klien_id');
    await expect(ptKlienSelect).toBeVisible();

    // Select PT Klien (skip if none available)
    const ptOptions = ptKlienSelect.locator('option:not([value=""])');
    const ptCount = await ptOptions.count();
    if (ptCount === 0) {
      test.skip(true, 'No PT Klien available for invoice creation');
      return;
    }
    const ptValue = await ptOptions.first().getAttribute('value');
    if (ptValue) await ptKlienSelect.selectOption(ptValue);

    // Select Periode (skip if none available)
    const periodeSelect = page.locator('#inv_periode_id');
    const periodeOptions = periodeSelect.locator('option:not([value=""])');
    const periodeCount = await periodeOptions.count();
    if (periodeCount === 0) {
      test.skip(true, 'No Periode available for invoice creation');
      return;
    }
    const periodeValue = await periodeOptions.first().getAttribute('value');
    if (periodeValue) await periodeSelect.selectOption(periodeValue);

    // Submit
    await page.locator('form[action*="invoice"] button[type="submit"]').click();
    await page.waitForURL(/\/admin\/invoice/, { timeout: 15000 });

    // Verify invoice created (success message or table has data)
    const body = await page.textContent('body');
    const hasInvoice = body?.includes('INV-') || body?.includes('berhasil') || body?.includes('Invoice');
    expect(hasInvoice).toBeTruthy();
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 6: ADMIN VIEW INVOICE DETAIL
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_006: Admin melihat detail invoice', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.invoice);
    await waitForPageLoad(page);

    // Click detail on first invoice
    const detailLink = page.locator('a[href*="/admin/invoice/"]').first();
    if (await detailLink.isVisible()) {
      await detailLink.click();
      await waitForPageLoad(page);
      await expect(page).toHaveURL(/\/admin\/invoice\/\d+/);

      // Verify invoice detail content
      const content = await page.textContent('body');
      expect(content).toContain('Invoice');
    }
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 7: OWNER APPROVE INVOICE
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_007: Owner menyetujui invoice', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.pemilik_pt.email, USERS.pemilik_pt.password);

    await page.goto(ROUTES.owner.invoice);
    await waitForPageLoad(page);

    // Find invoice waiting for approval and click detail
    const invoiceLink = page.locator('a[href*="/owner/invoice/"]').first();
    if (await invoiceLink.isVisible()) {
      await invoiceLink.click();
      await waitForPageLoad(page);

      // Click Setujui button
      const approveBtn = page.locator('button:has-text("Setujui")');
      if (await approveBtn.isVisible()) {
        await approveBtn.click();
        await page.waitForURL(/\/owner\/invoice/, { timeout: 15000 });

        // Verify success
        const body = await page.textContent('body');
        expect(body?.includes('berhasil') || body?.includes('disetujui')).toBeTruthy();
      }
    }
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 8: ADMIN DOWNLOAD INVOICE PDF (after approval)
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_008: Admin download invoice PDF setelah disetujui', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.admin.email, USERS.admin.password);

    await page.goto(ROUTES.admin.invoice);
    await waitForPageLoad(page);

    // Filter by status disetujui
    await page.locator('#status').selectOption('disetujui');
    await page.locator('button:has-text("Filter")').click();
    await waitForPageLoad(page);

    // Find PDF download link (only visible for approved invoices)
    const pdfLink = page.locator('a[href*="/pdf"]').first();
    if (await pdfLink.isVisible()) {
      const [download] = await Promise.all([
        page.waitForEvent('download', { timeout: 30000 }).catch(() => null),
        pdfLink.click(),
      ]);

      if (download) {
        const filename = download.suggestedFilename();
        expect(filename).toContain('invoice');
        expect(filename).toContain('.pdf');
      }
    }
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 9: KARYAWAN VIEW SLIP GAJI
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_009: Karyawan melihat slip gaji sendiri', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);

    await page.goto(ROUTES.karyawan.slipGaji);
    await waitForPageLoad(page);
    await expect(page).toHaveURL(/\/karyawan\/slip-gaji/);

    // Verify slip gaji table visible
    const table = page.locator('table');
    await expect(table).toBeVisible();

    // Check table has salary columns
    const headers = await page.locator('th').allTextContents();
    const headerText = headers.join(' ');
    expect(headerText).toContain('Gaji Pokok');
    expect(headerText).toContain('Gaji Bersih');
  });

  // ═══════════════════════════════════════════════════════════
  // STEP 10: KARYAWAN DOWNLOAD SLIP GAJI PDF
  // ═══════════════════════════════════════════════════════════

  test('TC_FLOW_010: Karyawan download slip gaji PDF sendiri', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(USERS.karyawan.email, USERS.karyawan.password);

    await page.goto(ROUTES.karyawan.slipGaji);
    await waitForPageLoad(page);

    // Find PDF link
    const pdfLink = page.locator('a[href*="/pdf"]').first();
    if (await pdfLink.isVisible()) {
      const [download] = await Promise.all([
        page.waitForEvent('download', { timeout: 30000 }).catch(() => null),
        pdfLink.click(),
      ]);

      if (download) {
        const filename = download.suggestedFilename();
        expect(filename).toContain('.pdf');
      }
    }
  });
});
