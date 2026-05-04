import { Page, Locator, expect } from '@playwright/test';
import { waitForPageLoad } from '../utils/helpers';

/**
 * Page Object Model — Halaman Login
 * @see Req 1.1, 1.2, 1.3, 1.4
 */
export class LoginPage {
  readonly page: Page;
  readonly emailInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  readonly rememberCheckbox: Locator;
  readonly forgotPasswordLink: Locator;
  readonly errorAlert: Locator;
  readonly statusAlert: Locator;
  readonly logo: Locator;

  constructor(page: Page) {
    this.page = page;
    this.emailInput = page.locator('#email');
    this.passwordInput = page.locator('#password');
    this.submitButton = page.locator('button[type="submit"]');
    this.rememberCheckbox = page.locator('input[name="remember"]');
    this.forgotPasswordLink = page.locator('a[href*="password/forgot"]');
    this.errorAlert = page.locator('[role="alert"].bg-red-50');
    this.statusAlert = page.locator('[role="alert"].bg-emerald-50');
    this.logo = page.getByText('IPM', { exact: true }).first();
  }

  async goto(): Promise<void> {
    await this.page.goto('/login');
    await waitForPageLoad(this.page);
  }

  async login(email: string, password: string): Promise<void> {
    await this.emailInput.fill(email);
    await this.passwordInput.fill(password);
    await Promise.all([
      this.page.waitForLoadState('domcontentloaded'),
      this.submitButton.click(),
    ]);
  }

  async expectLoginPage(): Promise<void> {
    await expect(this.emailInput).toBeVisible();
    await expect(this.passwordInput).toBeVisible();
    await expect(this.submitButton).toBeVisible();
  }

  async expectErrorMessage(text?: string): Promise<void> {
    await expect(this.errorAlert).toBeVisible();
    if (text) {
      await expect(this.errorAlert).toContainText(text);
    }
  }

  async expectSuccessMessage(text?: string): Promise<void> {
    await expect(this.statusAlert).toBeVisible();
    if (text) {
      await expect(this.statusAlert).toContainText(text);
    }
  }

  async expectRedirectToDashboard(role: string): Promise<void> {
    const urlMap: Record<string, string> = {
      admin: '/admin/dashboard',
      pemilik_pt: '/owner/dashboard',
      karyawan: '/karyawan/(dashboard|profil)',
    };
    await expect(this.page).toHaveURL(new RegExp(urlMap[role] || '/dashboard'));
  }
}
