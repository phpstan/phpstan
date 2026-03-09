import { test, expect, Page } from '@playwright/test';

const pages = [
	{ name: 'homepage', path: '/' },
	{ name: 'playground', path: '/try' },
	{ name: 'blog', path: '/blog' },
	{ name: 'documentation', path: '/documentation' },
	{ name: 'getting-started', path: '/user-guide/getting-started' },
	{ name: 'sponsor', path: '/sponsor' },
];

async function setDarkMode(page: Page, enabled: boolean): Promise<void> {
	await page.evaluate((dark) => {
		if (dark) {
			document.documentElement.classList.add('dark');
		} else {
			document.documentElement.classList.remove('dark');
		}
	}, enabled);
	// Wait for any CSS transitions
	await page.waitForTimeout(100);
}

async function waitForPageReady(page: Page): Promise<void> {
	// Wait for network idle
	await page.waitForLoadState('networkidle');
	// Wait for fonts to load
	await page.evaluate(() => document.fonts.ready);
	// Hide dynamic content that might cause flakiness
	await page.evaluate(() => {
		const style = document.createElement('style');
		style.innerHTML = `
			/* Hide analytics and dynamic content for stable screenshots */
			[data-site="UDESFNRN"] { display: none !important; }
			/* Disable animations */
			*, *::before, *::after {
				animation-duration: 0s !important;
				animation-delay: 0s !important;
				transition-duration: 0s !important;
				transition-delay: 0s !important;
			}
		`;
		document.head.appendChild(style);
	});
}

for (const pageInfo of pages) {
	test.describe(pageInfo.name, () => {
		test('screenshot', async ({ page }, testInfo) => {
			await page.goto(pageInfo.path);
			await waitForPageReady(page);

			const isDark = testInfo.project.name.includes('dark');
			await setDarkMode(page, isDark);

			await expect(page).toHaveScreenshot(`${pageInfo.name}.png`, {
				fullPage: true,
				maxDiffPixelRatio: 0.01,
			});
		});
	});
}
