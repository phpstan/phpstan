import {test, expect} from '@playwright/test';

// Some browsers expose window.localStorage as null when site storage is
// blocked. The page must still load and the theme switcher must still work.
test('theme switcher works when localStorage is null', async ({page}) => {
	await page.addInitScript(() => {
		Object.defineProperty(window, 'localStorage', {get: () => null});
	});
	const errors: string[] = [];
	page.on('pageerror', (error) => errors.push(error.message));

	await page.goto('/blog');
	const dark = page.locator('[title="Theme will always use dark colors"]');
	await expect(dark).toBeVisible();
	await dark.click();
	await expect(page.locator('html')).toHaveClass(/\bdark\b/);

	expect(errors).toEqual([]);
});
