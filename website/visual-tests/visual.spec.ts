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

async function waitForPageReady(page: Page, networkIdle = true): Promise<void> {
	// Wait for network idle
	if (networkIdle) {
		await page.waitForLoadState('networkidle');
	}
	// Wait for fonts to load
	await page.evaluate(() => document.fonts.ready);
	// Hide dynamic content that might cause flakiness
	await page.evaluate(() => {
		const style = document.createElement('style');
		style.innerHTML = `
			/* Hide analytics and dynamic content for stable screenshots */
			[data-site="UDESFNRN"] { display: none !important; }
			/* Hide randomly-picked examples to avoid flaky diffs */
			[data-testid="random-examples"] { display: none !important; }
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

// A shared playground (/r/…) shows only a spinner, without the footer, until
// its code arrives from the API. The widths are the Tailwind breakpoints.
test.describe('playground-loading', () => {
	for (const width of [375, 640, 768, 1024, 1280]) {
		test(`${width}`, async ({ page }, testInfo) => {
			test.skip(testInfo.project.name.startsWith('mobile'), 'The widths are set here, the desktop projects cover light and dark mode.');
			await page.setViewportSize({ width, height: 800 });

			// The static server knows only /try, and the API never answers.
			await page.route('**/r/*', async (route) => route.fulfill({ response: await route.fetch({ url: new URL('/try', route.request().url()).href }) }));
			await page.route('https://api.phpstan.org/**', () => {});
			const sampleRequest = page.waitForRequest('https://api.phpstan.org/sample?**');
			await page.goto('/r/12345678-1234-4123-8123-123456789012');
			await sampleRequest;
			await waitForPageReady(page, false);

			await setDarkMode(page, testInfo.project.name.includes('dark'));

			await expect(page.getByText('PHPStan s.r.o.')).toBeHidden();
			// The page is mostly empty, so even the footer is less than 1 % of it.
			await expect(page).toHaveScreenshot('playground-loading.png', {
				fullPage: true,
				maxDiffPixels: 100,
			});
		});
	}
});
