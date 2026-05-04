import { Page, expect } from '@playwright/test';

/**
 * Utility helpers untuk E2E test Sistem Penggajian PT IPM.
 */

/** Tunggu halaman selesai loading. */
export async function waitForPageLoad(page: Page): Promise<void> {
  await page.waitForLoadState('domcontentloaded');
}

/** Ambil teks dari flash message / alert. */
export async function getFlashMessage(page: Page): Promise<string | null> {
  const alert = page.locator('[role="alert"]').first();
  if (await alert.isVisible({ timeout: 3000 }).catch(() => false)) {
    return alert.textContent();
  }
  return null;
}

/** Ambil CSRF token dari meta tag atau hidden input. */
export async function getCsrfToken(page: Page): Promise<string> {
  const token = await page.locator('input[name="_token"]').first().getAttribute('value');
  return token ?? '';
}

/** Screenshot dengan nama deskriptif. */
export async function takeScreenshot(page: Page, name: string): Promise<void> {
  await page.screenshot({
    path: `e2e/reports/screenshots/${name}-${Date.now()}.png`,
    fullPage: true,
  });
}

/** Format tanggal ke dd/mm/yyyy. */
export function formatDate(date: Date): string {
  const d = String(date.getDate()).padStart(2, '0');
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const y = date.getFullYear();
  return `${d}/${m}/${y}`;
}

/** Format tanggal ke yyyy-mm-dd (input date). */
export function toInputDate(date: Date): string {
  return date.toISOString().split('T')[0];
}

/** Generate string acak. */
export function randomString(length = 8): string {
  return Math.random().toString(36).substring(2, 2 + length);
}

/** Generate NIK acak (16 digit). */
export function randomNIK(): string {
  return Array.from({ length: 16 }, () => Math.floor(Math.random() * 10)).join('');
}

/** Generate email acak. */
export function randomEmail(): string {
  return `test.${randomString(6)}@example.com`;
}
