import {defineConfig, devices} from '@playwright/test';

// Functional (non-screenshot) tests for the playground editor — currently the
// PHPantom-in-wasm autocomplete. Runs against the built site served from
// ../dist (so the wasm worker is exercised exactly as shipped).
//
// For a quick local run against the dev server instead, set
// PW_BASE_URL=http://localhost:5173 (this skips the built-site web server).
const baseURL = process.env.PW_BASE_URL || 'http://localhost:3001';

export default defineConfig({
	testDir: '.',
	outputDir: './test-results',
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	reporter: process.env.CI ? 'github' : 'list',

	use: {
		baseURL,
		trace: 'on-first-retry',
	},

	projects: [
		{
			name: 'chromium',
			use: {...devices['Desktop Chrome'], viewport: {width: 1280, height: 900}},
		},
	],

	webServer: process.env.PW_BASE_URL
		? undefined
		: {
				command: 'npx serve ../dist -l 3001',
				url: baseURL,
				reuseExistingServer: !process.env.CI,
				timeout: 120 * 1000,
		  },
});
