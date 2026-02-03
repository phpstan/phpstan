import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
	testDir: '.',
	snapshotDir: './__screenshots__',
	snapshotPathTemplate: '{snapshotDir}/{testName}/{projectName}/{arg}{ext}',
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: process.env.CI ? 'github' : 'html',

	use: {
		baseURL: 'http://localhost:3000',
		trace: 'on-first-retry',
	},

	projects: [
		{
			name: 'desktop-light',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 1280, height: 800 },
			},
		},
		{
			name: 'desktop-dark',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 1280, height: 800 },
			},
		},
		{
			name: 'mobile-light',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 375, height: 667 },
				isMobile: true,
			},
		},
		{
			name: 'mobile-dark',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 375, height: 667 },
				isMobile: true,
			},
		},
	],

	webServer: {
		command: 'npx serve ../dist -l 3000',
		url: 'http://localhost:3000',
		reuseExistingServer: !process.env.CI,
		timeout: 120 * 1000,
	},
});
