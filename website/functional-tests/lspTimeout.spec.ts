import {test, expect} from '@playwright/test';

test('fast-forwarded timers do not time out PHPantom requests', async ({page}) => {
	const errors: string[] = [];
	page.on('pageerror', (error) => errors.push(error.message));

	await page.clock.install();
	await page.goto('/try');
	await expect(page.locator('.cm-content').first()).toBeVisible();

	await page.clock.fastForward(30 * 24 * 60 * 60 * 1000);
	await page.locator('.cm-content').first().click();
	await page.keyboard.type('x');
	await page.clock.fastForward(30 * 24 * 60 * 60 * 1000);

	expect(errors.filter((message) => message.includes('Request timed out'))).toEqual([]);
});
